<?php
// Start the session and include necessary files
session_start();
include './connections/db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

// Get username from session
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
    <link rel="stylesheet" href="admin/plugins/fontawesome-free/css/all.min.css">

    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css">

    <!-- Theme style -->
    <link rel="stylesheet" href="admin/dist/css/adminlte.min.css">

    <!-- fullCalendar -->
    <link rel="stylesheet" href="admin/plugins/fullcalendar/main.css">

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .external-event {
            margin: 10px 0;
            padding: 5px;
            font-size: 14px;
            color: #fff;
            cursor: pointer;
        }

        .fc-daygrid-event {
            border: none;
        }

        #trash-bin {
            cursor: pointer;
            z-index: 10;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Calendar</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Calendar</li>
                            </ol>
                        </div>
                    </div>
                </div><!-- /.container-fluid -->
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="sticky-top mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Draggable Events</h4>
                                    </div>
                                    <div class="card-body">
                                        <div id="external-events">
                                            <!-- Existing draggable events will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Create Event</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="btn-group" style="width: 100%; margin-bottom: 10px;">
                                            <ul class="fc-color-picker" id="color-chooser">
                                                <li><a class="text-primary" href="#"><i class="fas fa-square"></i></a></li>
                                                <li><a class="text-warning" href="#"><i class="fas fa-square"></i></a></li>
                                                <li><a class="text-success" href="#"><i class="fas fa-square"></i></a></li>
                                                <li><a class="text-danger" href="#"><i class="fas fa-square"></i></a></li>
                                                <li><a class="text-muted" href="#"><i class="fas fa-square"></i></a></li>
                                            </ul>
                                        </div>
                                        <div class="input-group">
                                            <input id="new-event" type="text" class="form-control" placeholder="Event Title">
                                            <div class="input-group-append">
                                                <button id="add-new-event" type="button" class="btn btn-primary">Add</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Trash Bin -->
                                <div id="trash-bin" class="card text-center" style="background: #ffdddd; border: 2px dashed red;">
                                    <br>
                                    <i class="fas fa-trash" style="font-size: 24px;"></i>
                                    <p>Trash Bin</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-9">
                            <div class="card card-primary">
                                <div class="card-body p-0">
                                    <div id="calendar"></div>
                                </div>
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="saveEventBtn">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="admin/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="admin/dist/js/adminlte.min.js"></script>
    <!-- FullCalendar -->
    <script src="admin/plugins/moment/moment.min.js"></script>
    <script src="admin/plugins/fullcalendar/main.js"></script>

    <script>
        $(function() {
            var currColor = '#3c8dbc'; // Default color

            // Initialize the calendar
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                themeSystem: 'bootstrap',
                editable: true,
                droppable: true, // Make sure this is enabled to allow dragging from external events
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
                                    location: event.location
                                };
                            });
                            successCallback(events);
                        }
                    });
                },
                eventClick: function(info) {
                    $('#eventId').val(info.event.id);
                    $('#eventTitle').val(info.event.title);
                    $('#eventDescription').val(info.event.extendedProps.description);
                    $('#eventDate').val(info.event.startStr.split('T')[0]);
                    $('#startTime').val(info.event.startStr.split('T')[1] || '00:00');
                    $('#endTime').val(info.event.endStr.split('T')[1] || '23:59');
                    $('#eventLocation').val(info.event.extendedProps.location);
                    $('#eventModal').modal('show');
                },
                dateClick: function(info) {
                    $('#eventId').val('');
                    $('#eventTitle').val('');
                    $('#eventDescription').val('');
                    $('#eventDate').val(info.dateStr);
                    $('#startTime').val('09:00');
                    $('#endTime').val('17:00');
                    $('#eventLocation').val('');
                    $('#eventModal').modal('show');
                },
                eventDrop: function(info) {
                    var trashBin = document.getElementById('trash-bin');

                    // Ensure trashBin is not null
                    if (!trashBin) {
                        console.error('Trash bin element not found');
                        return;
                    }

                    // Get the bounding rect of the trash bin
                    var trashBinRect = trashBin.getBoundingClientRect();
                    if (!trashBinRect) {
                        console.error('Trash bin rect is undefined');
                        return;
                    }
                    console.log('Trash Bin Rect:', trashBinRect);

                    // Get the bounding rect of the dragged event
                    var eventRect = info.el.getBoundingClientRect();
                    if (!eventRect) {
                        console.error('Event rect is undefined');
                        return;
                    }
                    console.log('Event Rect:', eventRect);

                    // Check if the event is dropped inside the trash bin
                    if (
                        eventRect.left < trashBinRect.right &&
                        eventRect.right > trashBinRect.left &&
                        eventRect.top < trashBinRect.bottom &&
                        eventRect.bottom > trashBinRect.top
                    ) {
                        console.log('Item dropped on the trash bin!');
                        if (confirm('Are you sure you want to delete this event?')) {
                            $.ajax({
                                url: 'add-events.php',
                                type: 'POST',
                                data: {
                                    action: 'delete',
                                    id: info.event.id
                                },
                                success: function(response) {
                                    console.log('Delete Response:', response); // Log the response for debugging
                                    if (response.trim() === 'Event deleted successfully') {
                                        info.event.remove();
                                    } else {
                                        alert('Failed to delete event');
                                        info.revert(); // Revert event to its original position if not deleted
                                    }
                                },
                                error: function() {
                                    alert('Failed to connect to the server');
                                    info.revert(); // Revert event to its original position if there is an error
                                }
                            });
                        } else {
                            info.revert(); // Revert event to its original position if not confirmed
                        }
                    } else {
                        console.log('The draggable element is not over the trash bin.');
                        // Update event details if not in trash bin
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
                                location: info.event.extendedProps.location
                            },
                            success: function() {
                                console.log('Event updated');
                            }
                        });
                    }
                }
            });

            calendar.render();

            // Save Event Button
            $('#saveEventBtn').click(function() {
                var id = $('#eventId').val();
                var title = $('#eventTitle').val();
                var description = $('#eventDescription').val();
                var date = $('#eventDate').val();
                var startTime = $('#startTime').val() || null;
                var endTime = $('#endTime').val() || null;
                var location = $('#eventLocation').val();

                var eventData = {
                    action: 'update',
                    id: id,
                    name: title,
                    description: description,
                    date: date,
                    start_time: startTime,
                    end_time: endTime,
                    location: location
                };

                $.ajax({
                    url: 'add-events.php',
                    type: 'POST',
                    data: eventData,
                    success: function(response) {
                        $('#eventModal').modal('hide');
                        calendar.refetchEvents();
                    },
                    error: function() {
                        alert('Failed to save event');
                    }
                });
            });

            // Initialize the color picker
            $('#color-chooser > li > a').click(function(e) {
                e.preventDefault();
                currColor = $(this).css('color');
                $('#add-new-event').css({
                    'background-color': currColor,
                    'border-color': currColor
                });
            });

            // Add new event button click handler
            $('#add-new-event').click(function(e) {
                e.preventDefault();
                var eventName = $('#new-event').val();
                if (eventName.length === 0) {
                    alert('Event title is required.');
                    return;
                }

                // Send AJAX request to add the new event
                $.ajax({
                    url: 'add-events.php',
                    type: 'POST',
                    data: {
                        action: 'add',
                        name: eventName
                    },
                    success: function(response) {
                        console.log(response);
                        if (response.includes('successfully')) {
                            var newEvent = $('<div />')
                                .css({
                                    'background-color': currColor,
                                    'border-color': currColor,
                                    'color': '#fff'
                                })
                                .addClass('external-event')
                                .text(eventName)
                                .data('eventObject', {
                                    title: eventName
                                });

                            // Prepend the new event and initialize it
                            $('#external-events').prepend(newEvent);
                            ini_events(newEvent);
                            $('#new-event').val('');
                        } else {
                            alert(response);
                        }
                    },
                    error: function() {
                        alert('Failed to connect to the server');
                    }
                });
            });

            // Fetch external events and display them
            $.ajax({
                url: 'add-events.php',
                type: 'POST',
                data: {
                    action: 'fetch-external'
                },
                dataType: 'json',
                success: function(events) {
                    events.forEach(function(event) {
                        var newEvent = $('<div />')
                            .css({
                                'background-color': currColor,
                                'border-color': currColor,
                                'color': '#fff'
                            })
                            .addClass('external-event')
                            .text(event.event_name)
                            .data('eventObject', {
                                title: event.event_name
                            });

                        // Prepend the event and initialize it
                        $('#external-events').prepend(newEvent);
                        ini_events(newEvent);
                    });
                },
                error: function() {
                    alert('Failed to fetch events');
                }
            });

            // Initialize external events
            function ini_events(ele) {
                ele.each(function() {
                    var eventObject = $(this).data('eventObject');
                    $(this).draggable({
                        zIndex: 1070,
                        revert: true, // Will cause the event to go back to its original position after the drag
                        revertDuration: 0
                    });
                });
            }

            ini_events($('#external-events .external-event'));
        });
    </script>
    <!-- jQuery UI -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">

</body>

</html>