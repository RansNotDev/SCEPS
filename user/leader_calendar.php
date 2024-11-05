<?php
session_start();
include '../connections/db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

// Get username from sessions
$username = $_SESSION['username'];

// Function to execute queries and return results
function fetch_data($query)
{
    global $conn;
    $stmt = $conn->prepare($query);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Fetch user information
include 'user-info.php';
$recent_activities = fetch_data("SELECT * FROM activities WHERE username = '$username' ORDER BY date DESC LIMIT 5");
$total_users = fetch_data("SELECT COUNT(*) as count FROM club_members")[0]['count'];
$total_posts = fetch_data("SELECT COUNT(*) as count FROM posts")[0]['count'];

// Fetch clubs from the database
$clubs = fetch_data("SELECT club_id, club_name FROM clubs");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../admin/plugins/fontawesome-free/css/all.min.css">

    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="../admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css">

    <!-- Theme style -->
    <link rel="stylesheet" href="../admin/dist/css/adminlte.min.css">

    <!-- fullCalendar -->
    <link rel="stylesheet" href="../admin/plugins/fullcalendar/main.css">

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">

    <style>
        .fc-daygrid-event {
            border: none;
            border-radius: 4px;
            padding: 5px;
            color: white;
            transition: transform 0.2s;
            cursor: pointer;
        }

        .fc-daygrid-event:hover {
            transform: scale(1.05);
        }

        /* Style for non-draggable past events */
        .fc-not-draggable {
            opacity: 0.5;
            /* Make past events look faded */
            pointer-events: none;
            /* Disable mouse events */
        }
    </style>

</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include 'leader_sidebar.php'; ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Calendar</h1>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-body p-0">
                                <div id="calendar"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Event Modal -->
        <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="eventModalLabel">Event Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="eventForm">
                            <input type="hidden" id="eventId">
                            <div class="mb-3">
                                <label for="eventTitle" class="form-label">Event Title</label>
                                <input type="text" class="form-control" id="eventTitle" required>
                            </div>
                            <div class="mb-3">
                                <label for="eventDescription" class="form-label">Event Description</label>
                                <textarea class="form-control" id="eventDescription" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="eventDate" class="form-label">Event Date</label>
                                <input type="date" class="form-control" id="eventDate" required>
                            </div>
                            <div class="mb-3">
                                <label for="startTime" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="startTime" required>
                            </div>
                            <div class="mb-3">
                                <label for="endTime" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="endTime" required>
                            </div>
                            <div class="mb-3">
                                <label for="eventLocation" class="form-label">Location</label>
                                <input type="text" class="form-control" id="eventLocation">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="deleteEventBtn">Delete Event</button>
                        <button type="button" class="btn btn-primary" id="saveEventBtn">Save changes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Modal -->
        <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="errorModalLabel">Error</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="errorMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- jQuery -->
    <script src="../admin/plugins/jquery/jquery.min.js"></script>
    <!-- jQuery UI -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- FullCalendar -->
    <script src="../admin/plugins/moment/moment.min.js"></script>
    <script src="../admin/plugins/fullcalendar/main.js"></script>
    <!-- AdminLTE App -->
    <script src="../admin/dist/js/adminlte.min.js"></script>

    <script>
        const colors = [
            '#378CE7',
            '#67C6E3',
            '#B6FFFA',
            '#98ABEE',
            '#40A2E3',
            '#7FC7D9',
            '#B4D4FF',
            '#86B6F6',
            '#96EFFF',
            '#00A9FF'
        ];

        $(function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                themeSystem: 'bootstrap',
                editable: true,
                droppable: true,
                events: function(fetchInfo, successCallback, failureCallback) {
                    $.ajax({
                        url: 'add-events.php',
                        type: 'POST',
                        data: {
                            action: 'fetch-calendar'
                        },
                        dataType: 'json',
                        success: function(data) {
                            var events = data.map(function(event) {
                                return {
                                    id: event.event_id,
                                    title: event.event_name,
                                    start: event.event_date + 'T' + (event.start_time || '00:00'),
                                    end: event.event_date + 'T' + (event.end_time || '23:59'),
                                    description: event.event_description,
                                    location: event.location,
                                    club_id: event.club_id
                                };
                            });
                            successCallback(events);
                        },
                        error: function() {
                            failureCallback('Failed to fetch events');
                        }
                    });
                },
                eventDidMount: function(info) {
                    // Select a random color from the cozy color palette
                    const randomColor = colors[Math.floor(Math.random() * colors.length)];
                    info.el.style.backgroundColor = randomColor;

                    var eventDate = new Date(info.event.start);
                    var today = new Date();
                    today.setHours(0, 0, 0, 0);

                    // If the event date is in the past, make it non-draggable
                    if (eventDate < today) {
                        info.el.classList.add('fc-not-draggable'); // Add a class to style
                        info.event.setProp('draggable', false); // Disable dragging
                    }
                },
                eventClick: function(info) {
                    $('#eventId').val(info.event.id);
                    $('#eventTitle').val(info.event.title);
                    $('#eventDescription').val(info.event.extendedProps.description);
                    $('#eventDate').val(info.event.startStr.split('T')[0]);
                    $('#startTime').val(moment(info.event.start).format('HH:mm'));
                    $('#endTime').val(moment(info.event.end).format('HH:mm'));
                    $('#eventLocation').val(info.event.extendedProps.location);
                    $('#clubSelect').val(info.event.extendedProps.club_id); // Set selected club
                    $('#eventModal').modal('show');
                },
                dateClick: function(info) {
                    var selectedDate = new Date(info.dateStr);
                    var today = new Date();
                    today.setHours(0, 0, 0, 0); // Set time to midnight for comparison

                    // Check if the selected date is today or in the past
                    if (selectedDate <= today) {
                        alert('You cannot add an event on a past date.');
                        return;
                    }

                    // If valid date, open modal
                    $('#eventId').val('');
                    $('#eventTitle').val('');
                    $('#eventDescription').val('');
                    $('#eventDate').val(info.dateStr);
                    $('#startTime').val('00:00');
                    $('#endTime').val('23:59');
                    $('#eventLocation').val('');
                    $('#clubSelect').val(''); // Reset club selection
                    $('#eventModal').modal('show');
                },
                eventDrop: function(info) {
                    var droppedDate = info.event.start; // The new start date of the event
                    var today = new Date();
                    today.setHours(0, 0, 0, 0);

                    if (info.event.start < today) {
                        alert('You cannot drop an event on a past date.');
                        info.revert();
                        return;
                    }

                    // Proceed with the AJAX call to update the event
                    $.ajax({
                        url: 'add-events.php',
                        type: 'POST',
                        data: {
                            action: 'update',
                            id: info.event.id,
                            name: info.event.title,
                            description: info.event.extendedProps.description,
                            date: info.event.startStr.split('T')[0],
                            start_time: info.event.startStr.split('T')[1] || null,
                            end_time: info.event.endStr.split('T')[1] || null,
                            location: info.event.extendedProps.location,
                            club_id: info.event.extendedProps.club_id
                        },
                        success: function(response) {
                            if (response.startsWith('Error:')) {
                                $('#errorMessage').text(response);
                                $('#errorModal').modal('show');
                            } else {
                                console.log('Event updated');
                            }
                        },
                        error: function() {
                            $('#errorMessage').text('Failed to connect to the server');
                            $('#errorModal').modal('show');
                        }
                    });
                }
            });

            calendar.render();

            // Save Event Button
            $('#saveEventBtn').click(function() {
                var id = $('#eventId').val();
                var title = $('#eventTitle').val().trim();
                var description = $('#eventDescription').val().trim();
                var date = $('#eventDate').val();
                var startTime = $('#startTime').val().trim();
                var endTime = $('#endTime').val().trim();
                var location = $('#eventLocation').val().trim();

                // Client-side validation
                if (!title) {
                    alert('Event title is required.');
                    return;
                }
                if (!date) {
                    alert('Event date is required.');
                    return;
                }
                if (!startTime) {
                    alert('Start time is required.');
                    return;
                }
                if (!endTime) {
                    alert('End time is required.');
                    return;
                }

                $.ajax({
                    url: 'add-events.php',
                    type: 'POST',
                    data: {
                        action: 'update',
                        id: id,
                        name: title,
                        description: description,
                        date: date,
                        start_time: startTime,
                        end_time: endTime,
                        location: location,
                        club_id: $('#clubSelect').val()
                    },
                    success: function(response) {
                        if (response.startsWith('Error:')) {
                            $('#errorMessage').text(response);
                            $('#errorModal').modal('show');
                        } else {
                            $('#eventModal').modal('hide');
                            calendar.refetchEvents();
                        }
                    },
                    error: function() {
                        $('#errorMessage').text('Failed to save event');
                        $('#errorModal').modal('show');
                    }
                });
            });

            // Delete Event Button
            $('#deleteEventBtn').click(function() {
                var id = $('#eventId').val();
                if (id) {
                    if (confirm('Are you sure you want to delete this event?')) {
                        $.ajax({
                            url: 'add-events.php',
                            type: 'POST',
                            data: {
                                action: 'delete',
                                id: id
                            },
                            success: function(response) {
                                if (response.trim() === 'Event deleted successfully') {
                                    $('#eventModal').modal('hide');
                                    calendar.refetchEvents();
                                } else {
                                    $('#errorMessage').text(response);
                                    $('#errorModal').modal('show');
                                }
                            },
                            error: function() {
                                $('#errorMessage').text('Failed to connect to the server');
                                $('#errorModal').modal('show');
                            }
                        });
                    }
                } else {
                    alert('No event selected for deletion.');
                }
            });
        });

        $(document).ready(function() {
            <?php if (isset($error_message)): ?>
                $('#errorMessage').text('<?php echo htmlspecialchars($error_message); ?>');
                $('#errorModal').modal('show');
            <?php endif; ?>
        });
    </script>
</body>
</html>