<?php

namespace App\Http\Controllers;

use App\Event;
use App\Hour;
use App\Http\Requests\StoreHoursRequest;
use App\StudentInfo;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class HoursController extends Controller
{
    public function index(User $user = null)
    {
        if ($user) {
            if (!isAdmin()) {
                return redirect(route('home'))->with('forbidden', true);
            }
            $uid = $user->id;
            $fullName = $user->full_name;
            $studentId = $user->student->student_id;
            $grade = $user->student->grade;
        } else {
            $uid = Auth::id();
            $fullName = Auth::user()->full_name;
            $studentId = Auth::user()->student->student_id;
            $grade = Auth::user()->student->grade;
        }

        $hours = Hour::where('user_id', $uid)->orderByDesc('start_time')->get();

        $events = Event::active()->get();

        $total = Hour::select(\DB::raw('TIME_TO_SEC(TIMEDIFF(end_time, start_time)) AS total'))->where('user_id',
            $uid)->get();
        $totalHours = round($total->sum('total') / 3600, 1);
        $averageHours = round($total->avg('total') / 3600, 1);

        $numEvents = Hour::where('user_id', $uid)->count();

        return view('pages.hours',
            compact('hours', 'totalHours', 'averageHours',
                'numEvents', 'fullName', 'uid',
                'studentId', 'grade', 'events'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'uid'        => 'required|exists:users,id',
            'event'      => 'required|exists:events,id',
            'date'       => 'required|date|date_format:m/d/Y',
            'start_time' => 'required|before:end_time',
            'end_time'   => 'required|after:start_time'
        ]);

        $uid = $request->uid;
        $user = User::find($uid);
        if (!$user->clubs()->where('clubs.id', getClubId())->exists()) {
            return response()->json(['status' => 'error', 'message' => "The student does not belong to the club."]);
        }

        $hour = new Hour;
        $event = $request->event;

        $startDate = new Carbon($request->date);
        $startTime = $startDate->setTimeFromTimeString($request->start_time);

        $endDate = new Carbon($request->date);
        $endTime = $endDate->setTimeFromTimeString($request->end_time);

        $hour->needs_review = false;
        $hour->event_id = $event;
        $hour->start_time = $startTime;
        $hour->end_time = $endTime;
        $hour->student_id = $user->student->student_id;
        $hour->club_id = getClubId();
        try {
            $user->hours()->save($hour);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Store new hour submission
     * "Add New Activity"
     * "Clock In"
     *
     * @param \App\Http\Requests\StoreHoursRequest $request
     *
     * @return array
     * @throws \Throwable
     */
    public function store(StoreHoursRequest $request)
    {
        $req = $request->validated();

        $stuid = $req['id'];
        $event = $req['event'];
        $comments = $request->comments ?? null; //Comments might be blank

        $hour = new Hour;
        $hour->student_id = $stuid;
        $hour->event_id = $event;
        $hour->start_time = Carbon::now();
        $hour->comments = $comments;
        $hour->club_id = getClubId();
        #$hour->saveOrFail();

        $student = StudentInfo::where('student_id', $stuid);
        if ($student->exists()) {
            //Associate user
            $hour->user_id = $student->first()->user->id;
            $hour->saveOrFail();
        }

        $name = $student->first()->full_name;
        $event = $hour->getEventName();
        if (Auth::guard('admin')->check()) {
            log_action("Clocked in $name for $event");
        } else {
            log_action("Clocked in for $event");
        }

        return ['success' => true, 'messsage' => $name . " has been clocked in."];


    }

    public function delete(Hour $hour, Request $request)
    {
        $stuid = $hour->student_id;
        if (Hour::isClockedIn($stuid) || isAdmin()) {
            //Delete hour
            try {
                $hour->delete();
                if (isAdmin()) {
                    log_action("Deleted time punch for " . $hour->getFullName() . " from " . $hour->start_time->toFormattedDateString());
                } else {
                    log_action("Deleted own time punch");
                }
            } catch (\Exception $e) {
                abort(500, $e->getMessage());
            }

            return response()->json(['success' => true]);
        } else {
            return abort(422, 'You are not clocked in.');
        }
    }

    public function clockout(Hour $hour, Request $request, $mark = false)
    {
        if ($hour->end_time) {
            //Already clocked out, possibly by admin? Whatever!
            return response()->json(['success' => true]);
        }
        //Clock out!
        $hour->end_time = Carbon::now();
        $hour->comments = $request->comments ?? null;
        $hour->needs_review = ($mark) ? true : false;
        $hour->saveOrFail();

        $name = $hour->getEventName();
        log_action("Clocked out from $name");

        return response()->json(['success' => true]);
    }

    public function charts(User $user)
    {
        //TODO: add club_ids
        $uid = $user->id;
        //Line Chart: Average Duration per Month
        $lineRes = Hour::select(\DB::raw(
            "MONTH(start_time) as `month`, ROUND(AVG(ROUND(TIME_TO_SEC(TIMEDIFF(end_time, start_time)) / 3600, 1)), 1) AS hours"))
            ->where('user_id', $uid)
            ->where('club_id', getClubId())
            ->groupBy("month")->get();

        //Pie Chart: Events by Name
        $pieRes = \DB::table('hours')
            ->join('events', function ($join) {
                $join->on('hours.event_id', '=', 'events.id')
                    ->where('events.club_id', getClubId());
            })
            ->select(\DB::raw('hours.event_id AS event_id, events.event_name AS event_name, COUNT(*) AS count'))
            ->where('hours.user_id', $uid)
            ->where('hours.club_id', getClubId())
            ->groupBy('event_id', 'event_name')->get();

        //Mixed Chart: Total Hours, Total Hours per Month by Event
        $mixedRes = [];
        $labels = [];
        for ($i = 0; $i < 8; $i++) {
            //Past 8 months
            $now = Carbon::now()->subMonths($i);

            $monthName = $now->format('F');
            $month = Carbon::now()->subMonths($i)->month;

            $labels[$month] = $monthName;

            /** Total Hours */
            $db = Hour::select(
                \DB::raw("ROUND(SUM(TIME_TO_SEC(TIMEDIFF(end_time, start_time)) / 3600), 1) AS hours"))
                ->where('user_id', $uid)->where('club_id', getClubId());
            $totalHours = $db->whereRaw("MONTH(start_time) = ?", [$month]);
            $mixedRes[$month]['total'] = $totalHours->first()->hours ?: 0;

            /** Hours per Event */
            $events = Event::where('club_id', getClubId())->get(); //Inlcuding inactive events

            foreach ($events as $event) {
                $db = Hour::select(
                    \DB::raw("ROUND(SUM(TIME_TO_SEC(TIMEDIFF(end_time, start_time)) / 3600), 1) AS hours"))
                    ->where('user_id', $uid)
                    ->where('club_id', getClubId());
                $totalHours = $db->whereRaw("MONTH(start_time) = ?", [$month])
                    ->where('event_id', $event->id)
                    ->first()->hours;

                $mixedRes[$month]['events'][$event->event_name] =
                    $totalHours ?: 0;
            }

        }
        $response = $this->parseChartsForJs(
            ['line' => $lineRes, 'pie' => $pieRes, 'mixed' => $mixedRes, 'labels' => $labels]
        );

        return response()->json($response);
    }

    private function parseChartsForJs(array $data)
    {
        $return = [];
        $labelData = $data['labels'];
        $pieData = $data['pie'];
        $mixedData = $data['mixed'];

        /** Line Chart */
        $lineData = $data['line'];
        foreach ($lineData as $line) {
            $month = $line->month;
            $total = $line->hours;

            if (!isset($labelData[$month])) {
                //Past 8 months ago, ignore
                continue;
            }
            $monthName = $labelData[$month];

            $return['line']['labels'][] = $monthName;
            $return['line']['data'][] = $total;
        }

        /** Pie Chart */
        foreach ($pieData as $pie) {
            $id = $pie->event_id;
            $name = $pie->event_name;
            $total = $pie->count;

            $return['pie']['labels'][] = $name;
            $return['pie']['data'][] = $total;
        }

        /** Mixed Chart - "The Big One!" */
        $dataset = [];
        foreach ($mixedData as $month => $mixed) {
            if (!$mixed['total']) {
                //No data to show
                continue;
            }
            $monthName = $labelData[$month];
            $return['mixed']['labels'][] = $monthName;
            $return['mixed']['totals'][] = $mixed['total'];

            //Each Event
            $c = 0;
            foreach ($mixed['events'] as $name => $total) {
                $dataset[$name][] = $total;
            }

        }
        $return['mixed']['datasets'] = $dataset;

        return $return;
    }

    public function mark(Hour $hour)
    {
        $hour->needs_review = true;
        try {
            $hour->saveOrFail();
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function undoMark(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:hours'
        ]);

        $hour = Hour::find($request->id);
        $this->authorize('update', $hour);

        $hour->needs_review = false;
        $hour->saveOrFail();

        return response()->json(['status' => 'success']);
    }
}
