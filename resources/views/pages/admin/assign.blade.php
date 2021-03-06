@section('page-title')
    Assign Students
@endsection



<div id="assign">
    <h5>Student Management</h5>
    <hr>
    <h4>Assign Students</h4>
    <p class="text-muted">Students are assigned to your club when they enter your club's
        access code, <code>{{ $clubCode }}</code>. You can also manually add them here.</p>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title">Add by student ID or full name</h5>
                    <hr>
                    <p class="card-text text-muted">Use the <a href="#">Enrolled
                            Students</a> page
                        to search for an ID or name.</p>
                    <form id="manual-assign-form" method="post"
                          action="{{ route('manual-assign') }}">
                        @csrf
                        <div class="form-group">
                            <input type="text" class="form-control"
                                   id="assign-input"
                                   placeholder="ex. 115602 or Blake Nahin">
                        </div>
                        <div class="form-group">
                            <button class="btn btn-success btn-block" id="manual-assign"><i
                                    class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xs-3">
            <div class="card bg-light border-danger" style="max-width: 18rem;">
                <div class="card-body">
                    <h5 class="card-title">Purge all students</h5>
                    <hr>
                    <p class="card-text">This will detach all students from your club. This will not block them.
                        <strong>Be
                            careful.</strong>
                    </p>
                    <button class="btn btn-outline-danger btn-block" id="purge-students"><i
                            class="fas fa-times-circle"></i> Purge All
                        Students
                    </button>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <table class="table table-hover" id="assigned-table">
        <thead class="thead-dark">
        <tr>
            <th>Student ID</th>
            <th>Last Name</th>
            <th>First Name</th>
            <th>Grade Level</th>
            <th>Email</th> <!--(ND) if next day)-->
            <th class="print-hide">Actions</th>
        </tr>
        </thead>
        <tbody>
        <!--Students that are a part of the admin's (current user) club -->
        @if($data)
            @foreach($data as $student)
                <tr>
                    <!-- Success background if currently clocked out -->
                    <!-- Info background if a timepunch is marked -->
                    <td>{{ $student->student->student_id }}</td>
                    <td>{{ $student->last_name }}</td>
                    <td>{{ $student->first_name }}</td>
                    <td>{{ $student->student->grade }}</td>
                    <td>{{ $student->email }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('my-hours', ['user' => $student->id]) }}"
                               target="_blank">
                                <button class="btn btn-success" rel="tooltip" title="View Hours"><i
                                        class="fas fa-clock"></i></button>
                            </a>
                            <a href="mailto:{{ $student->email }}" target="_blank">
                                <button class="btn btn-info" rel="tooltip" title="Send Email"><i
                                        class="fas fa-envelope"></i></button>
                            </a>
                            <button class="btn btn-danger drop-student" rel="tooltip" title="Drop Student"
                                    data-id="{{ $student->id }}"><i
                                    class="fas fa-minus-circle"></i></button>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif
        </tbody>
    </table>
</div>