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

// Fetch user information
include '../user-info.php';
$recent_activities = fetch_data("SELECT * FROM activities WHERE username = '$username' ORDER BY date DESC LIMIT 5");
$total_users = fetch_data("SELECT COUNT(*) as count FROM club_members")[0]['count'];
$total_posts = fetch_data("SELECT COUNT(*) as count FROM posts")[0]['count'];
$total_events = fetch_data("SELECT COUNT(*) as count FROM events")[0]['count'];
$total_clubs = fetch_data("SELECT COUNT(*) as count FROM clubs")[0]['count'];
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>School Club Event Planner - Member Dashboard</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../admin/plugins/fontawesome-free/css/all.min.css">

    <!-- Theme style -->
    <link rel="stylesheet" href="../admin/dist/css/adminlte.min.css">

    <!-- Chart.js -->
    <script src="../admin/plugins/chart.js/Chart.min.js"></script>
</head>

<body class="hold-transition sidebar-mini">
    <?php include '../user/member_sidebar.php' ?>

    <div class="content-wrapper" style="max-height: 600px; overflow-y: auto;">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Member Dashboard</h1>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <!-- Recent Activities Card -->
                            <div class="col-lg-6 col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Recent Activities</h3>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            <?php foreach ($recent_activities as $activity): ?>
                                                <li><?php echo htmlspecialchars($activity['activity']); ?> - <small><?php echo htmlspecialchars($activity['date']); ?></small></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Upcoming Events</h3>
                                    </div>
                                    <div class="card-body">
                                        <div id="calendar"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Bar Graph -->
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
    <script src="../admin/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../admin/dist/js/adminlte.min.js"></script>
    <!-- Chart.js Script for Bar Graph -->
    <script>
        $(function() {
            var ctx = document.getElementById('statsChart').getContext('2d');
            var statsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Users', 'Posts', 'Events', 'Clubs'],
                    datasets: [{
                        label: 'Total Count',
                        data: [<?php echo htmlspecialchars($total_users); ?>, <?php echo htmlspecialchars($total_posts); ?>, <?php echo htmlspecialchars($total_events); ?>, <?php echo htmlspecialchars($total_clubs); ?>],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(153, 102, 255, 0.6)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Adjust this to avoid stretched labels
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
                            },
                            ticks: {
                                autoSkip: true, // Automatically skip labels if necessary
                                maxTicksLimit: 5 // Limit the number of ticks to avoid overcrowding
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return tooltipItem.dataset.label + ': ' + tooltipItem.raw; // Custom tooltip label
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

    </div>
</body>

</html>