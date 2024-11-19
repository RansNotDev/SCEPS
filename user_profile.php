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
$user_info = fetch_data("SELECT username FROM admin WHERE username = '$username'");
$recent_activities = fetch_data("SELECT * FROM activities WHERE username = '$username' ORDER BY date DESC LIMIT 5");
$total_users = fetch_data("SELECT COUNT(*) as count FROM admin")[0]['count'];
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
    <link rel="stylesheet" href="devnull_access/plugins/fontawesome-free/css/all.min.css">

    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="devnull_access/plugins/icheck-bootstrap/icheck-bootstrap.min.css">

    <!-- Theme style -->
    <link rel="stylesheet" href="devnull_access/dist/css/adminlte.min.css">

    <!-- fullCalendar -->
    <link rel="stylesheet" href="devnull_access/plugins/fullcalendar/main.css">

</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                </li>
            </ul>
            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <?php include 'sidebar.php' ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Dashboard</h1>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Main Content -->
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <!-- User Information Card -->
                        <div class="col-lg-4 col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">User Information</h3>
                                </div>
                                <div class="card-body">
                                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user_info[0]['username']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user_info[0]['email']); ?></p>
                                </div>
                            </div>
                        </div>
                        <!-- Statistics Cards -->
                        <div class="col-lg-4 col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Statistics</h3>
                                </div>
                                <div class="card-body">
                                    <p><strong>Total Users:</strong> <?php echo htmlspecialchars($total_users); ?></p>
                                    <p><strong>Total Posts:</strong> <?php echo htmlspecialchars($total_posts); ?></p>
                                </div>
                            </div>
                        </div>
                        <!-- Recent Activities Card -->
                        <div class="col-lg-4 col-12">
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
                    </div>
                </div>
            </div>

            <!-- Control Sidebar -->
            <aside class="control-sidebar control-sidebar-dark">
                <!-- Control sidebar content goes here -->
            </aside>
            <!-- /.control-sidebar -->

        </div>
        <!-- ./wrapper -->

        <!-- jQuery -->
        <script src="devnull_access/plugins/jquery/jquery.min.js"></script>

        <!-- Bootstrap 4 -->
        <script src="devnull_access/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

        <!-- AdminLTE App -->
        <script src="devnull_access/dist/js/adminlte.min.js"></script>

        <!-- fullCalendar 2.2.5 -->
        <script src="devnull_access/plugins/moment/moment.min.js"></script>
        <script src="devnull_access/plugins/fullcalendar/main.js"></script>

        <!-- jQuery UI -->
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</body>

</html>