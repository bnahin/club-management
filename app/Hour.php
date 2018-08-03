<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Hour
 *
 * @property int                 $id
 * @property int                 $user_id
 * @property int                 $event_id
 * @property string|null         $start_time
 * @property string|null         $end_time
 * @property string              $comments
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Event     $event
 * @property-read \App\User      $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Hour whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Hour whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Hour whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Hour whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Hour whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Hour whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Hour whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Hour whereUserId($value)
 * @mixin \Eloquent
 * @property int $student_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Hour whereStudentId($value)
 */
class Hour extends Model
{
    protected $guarded = [];

    protected $dates = ['start_time', 'end_time'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the student is clocked out.
     *
     * @param int Student ID $stuid
     *
     * @return bool
     */
    public static function isClockedOut($stuid)
    {
        return Hour::where('student_id', $stuid)
            ->whereNotNull('start_time')
            ->whereNull('end_time')
            ->exists();
    }

    public static function getClockData($stuid)
    {
        return static::isClockedOut($stuid) ?
            Hour::where('student_id', $stuid)
                ->whereNotNull('start_time')
                ->whereNull('end_time')
                ->firstOrFail() : false;
    }
    public function getEventName() {
        return ($this->event_id) ? $this->event->event_name : 'Out of Classroom';
    }
}
