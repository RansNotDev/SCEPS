<?php
// Start the session and include necessary files
session_start();
include '../connections/db.php';

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

// Fetch user information and get the user's club ID
include 'user-info.php'; // This should set $club_id

// Get the current date
$current_date = date('Y-m-d');

// Modify the SQL queries to include the user's club ID
$recent_events_query = "
    SELECT 
        events.event_name, 
        events.event_description, 
        events.event_date, 
        events.location, 
        clubs.club_name, 
        events.created_by 
    FROM 
        events 
    LEFT JOIN 
        clubs ON events.club_id = clubs.club_id 
    WHERE 
        events.event_date < '$current_date' 
        AND events.club_id = '$club_id' 
    ORDER BY 
        events.event_date DESC 
    LIMIT 5
";

$upcoming_events_query = "
    SELECT 
        events.event_name, 
        events.event_description, 
        events.event_date, 
        events.location, 
        clubs.club_name, 
        events.created_by 
    FROM 
        events 
    LEFT JOIN 
        clubs ON events.club_id = clubs.club_id 
    WHERE 
        events.event_date >= '$current_date' 
        AND events.club_id = '$club_id' 
    ORDER BY 
        events.event_date ASC 
    LIMIT 5
";

// Fetch events based on the user's club designation
$upcoming_events = fetch_data($upcoming_events_query);
$recent_events = fetch_data($recent_events_query);

// Fetch statistics
$total_members = fetch_data("SELECT COUNT(*) as count FROM club_members WHERE club_id = '$club_id'")[0]['count'];
$total_events = fetch_data("SELECT COUNT(*) as count FROM events WHERE club_id = '$club_id'")[0]['count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>School Club Event Planner - Dashboard</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="../devnull_access/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../devnull_access/dist/css/adminlte.min.css">
    <script src="../devnull_access/plugins/chart.js/Chart.min.js"></script>

    <style>
        .event-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .event-item {
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            transition: box-shadow 0.3s ease;
        }

        .event-item:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .event-item h5 {
            color: #007bff;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini">
    <?php include 'member_sidebar.php'; ?>

    <div class="content-wrapper" style="max-height: 600px; overflow-y: auto;">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Dashboard</h1>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-2 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($total_members); ?></h3>
                                <p>Total Members</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($total_events); ?></h3>
                                <p>Total Events</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-flag"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-6 col-12 d-flex">
                                <div class="card flex-fill">
                                    <div class="card-header">
                                        <h3 class="card-title">Recent Events</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="event-list">
                                            <ul class="list-unstyled">
                                                <?php if (empty($recent_events)): ?>
                                                    <li>No recent events found.</li>
                                                <?php else: ?>
                                                    <?php foreach ($recent_events as $event): ?>
                                                        <li class="event-item mb-3 p-3 border rounded">
                                                            <h5><?php echo htmlspecialchars($event['event_name']); ?></h5>
                                                            <p><strong>Description:</strong> <?php echo htmlspecialchars($event['event_description']); ?></p>
                                                            <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                                                            <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                                                            <p><strong>Club:</strong> <?php echo htmlspecialchars($event['club_name']); ?></p>
                                                            <p><strong>Created By:</strong> <?php echo htmlspecialchars($event['created_by']); ?></p>
                                                        </li>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-12 d-flex">
                                <div class="card flex-fill">
                                    <div class="card-header">
                                        <h3 class="card-title">Upcoming Events</h3>
                                    </div>
                                    <div class="card-body">
                                        <ul class="event-list list-unstyled">
                                            <?php if (empty($upcoming_events)): ?>
                                                <li>No upcoming events found.</li>
                                            <?php else: ?>
                                                <?php foreach ($upcoming_events as $event): ?>
                                                    <li class="event-item mb-3 p-3 border rounded">
                                                        <h5><?php echo htmlspecialchars($event['event_name']); ?></h5>
                                                        <p><strong>Description:</strong> <?php echo htmlspecialchars($event['event_description']); ?></p>
                                                        <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                                                        <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                                                        <p><strong>Club:</strong> <?php echo htmlspecialchars($event['club_name']); ?></p>
                                                        <p><strong>Created By:</strong> <?php echo htmlspecialchars($event['created_by']); ?></p>
                                                    </li>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12 col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Statistics Overview</h3>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="statsChart" style="height: 400px; max-height: 400px; max-width: 100%;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="control-sidebar control-sidebar-dark">
                </aside>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="../devnull_access/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../devnull_access/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../devnull_access/dist/js/adminlte.min.js"></script>
    <!-- Chart.js Script for Bar Graph -->
    <script>
        $(function() {
            var ctx = document.getElementById('statsChart').getContext('2d');
            var statsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Members', 'Events'],
                    datasets: [{
                        label: 'Total Count',
                        data: [<?php echo htmlspecialchars($total_members); ?>, <?php echo htmlspecialchars($total_events); ?>],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 99, 132, 0.6)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Total Count'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Categories'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>