@section('page-title')
    Marked Hours
@endsection

@push('scripts')
    <script src="{{ asset('datetimepicker/moment.min.js') }}"></script>
    <script src="{{ asset('datetimepicker/datetimepicker.js') }}"></script>
    <script src="{{ asset('datetimepicker/timepicker.min.js') }}"></script>
@endpush
@push('styles')
    <link rel="stylesheet" href="{{ asset('datetimepicker/datetimepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('datetimepicker/timepicker.min.css') }}">
@endpush
<div class="modal fade" id="marked-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editing <strong>Blake Nahin</strong>'s Timepunch</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Student's Comments</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of
                            the card's content.</p>
                    </div>
                </div>
                <form id="edit-hour-form">
                    <div class="form-group row">
                        <label for="event" class="col-sm-2 col-form-label">Event</label>
                        <div class="col-sm-6">
                            <select class="form-control" id="event">
                                @if(count($data['events']))
                                    @foreach($data['events'] as $event)
                                        <option value="{{ $event->id }}">{{ $event->event_name }}</option>
                                    @endforeach
                                @endif</select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="start-time" class="col-sm-2 col-form-label">Date</label>
                        <div class="col-sm-4">
                            <input id="start-time" type="text" class="form-control">
                        </div>
                    </div>
                    <fieldset class="form-inline">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Start & End Time</label>
                            <div class="col-sm-10" id="start-end-time-col">
                                <div class="input-group clockpicker" data-placement="right" data-align="top"
                                     data-autoclose="true">
                                    <input type="text" class="form-control" value="09:32">
                                </div>
                                <span class="fas fa-minus"></span>
                                <div class="input-group clockpicker" data-placement="right" data-align="top"
                                     data-autoclose="true">
                                    <input type="text" class="form-control" value="09:32">
                                </div>

                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

                <button type="button" class="btn btn-danger"><i class="fas fa-times"></i> Remove Timepunch</button>
                <button type="button" class="btn btn-success"><i class="fas fa-check"></i> Save changes</button>
            </div>
        </div>
    </div>
</div>

<div id="blocks">
    <h5>Marked Hours</h5>
    <hr>
    <!--TODO: Blocked Students w/ action to unblock-->
    <div class="card" id="blocked-info">
        <div class="card-body">
            These time punches have been <strong>marked for review</strong>. Possible situations include a missed punch,
            accidental clock in, or date/time adjustments.
        </div>
    </div>
    <table class="table" id="marked-table">
        <thead>
        <tr class="thead-dark">
            <th>Last Name</th>
            <th>First Name</th>
            <th>Date & Time</th>
            <!--Date OUT 0:0 - 0:0 IN -->
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @if(count($data['hours']))
            @foreach ($data['hours'] as $marked)
                <tr>
                    <td>{{ $marked->user->last_name }}</td>
                    <td>{{ $marked->user->first_name }}</td>
                    <td>{{ $marked->start_time->format('m/d/Y H:i') }} - {{ $marked->end_time->format('H:i') }}</td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-info marked-edit" data-id="{{ $marked->id }}"><i
                                    class="fas fa-pencil-alt"></i></button>
                            <button class="btn btn-outline-danger undo-mark" data-id="{{ $marked->id }}"><i
                                    class="fas fa-undo"></i></button>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif
        </tbody>
    </table>

</div>