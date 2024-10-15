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

// Handle pagination
$limit = 10; // Items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$total_clubs = fetch_data("SELECT COUNT(*) AS count FROM clubs")[0]['count'];
$total_pages = ceil($total_clubs / $limit);

// Fetch clubs with pagination
include 'user-info.php';
$clubs = fetch_data("SELECT club_id, club_name, description, created_by, created_at, club_image FROM clubs LIMIT $start, $limit");

// Handle deletion of clubs
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_clubs'])) {
    if (!empty($_POST['club_ids'])) {
        $club_ids = implode(',', array_map('intval', $_POST['club_ids']));
        $delete_query = "DELETE FROM clubs WHERE club_id IN ($club_ids)";

        // Execute delete query and check for errors
        if ($conn->query($delete_query) === TRUE) {
            header("Location: club.php?page=$page&success=1");
            exit(); 
        } else {
            echo "Error deleting records: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clubs</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="admin/plugins/fontawesome-free/css/all.min.css">

    <!-- Theme style -->
    <link rel="stylesheet" href="admin/dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <?php include 'sidebar.php' ?>

        <div class="content-wrapper">
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Clubs</h1>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12 col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h3 class="card-title">List of Clubs</h3>
                                    <div class="ml-auto">
                                        <button class="btn btn-success" data-toggle="modal" data-target="#addClubModal">Add Club</button>
                                        <button type="submit" name="delete_clubs" class="btn btn-danger">Delete Selected</button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <input type="checkbox" id="select-all">
                                                </th>
                                                <th>ID</th>
                                                <th>Club Image</th>
                                                <th>Club Name</th>
                                                <th>Description</th>
                                                <th>Created By</th>
                                                <th>Created At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($clubs as $club): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="club_ids[]" value="<?php echo htmlspecialchars($club['club_id']); ?>">
                                                    </td>
                                                    <td><?php echo htmlspecialchars($club['club_id']); ?></td>
                                                    <td>
                                                        <?php if (!empty($club['club_image'])): ?>
                                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($club['club_image']); ?>" alt="Club Image" width="50" height="50">
                                                        <?php else: ?>
                                                            <img src="default-club.png" alt="Default Club Image" width="50" height="50">
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($club['club_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($club['description']); ?></td>
                                                    <td><?php echo htmlspecialchars($club['created_by']); ?></td>
                                                    <td><?php echo htmlspecialchars($club['created_at']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <!-- Pagination -->
                                    <nav aria-label="Page navigation example">
                                        <ul class="pagination justify-content-center mt-4">
                                            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                                                <a class="page-link" href="club.php?page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
                                            </li>
                                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                    <a class="page-link" href="club.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                                                <a class="page-link" href="club.php?page=<?php echo $page + 1; ?>">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for Adding Club -->
            <div class="modal fade" id="addClubModal" tabindex="-1" role="dialog" aria-labelledby="addClubModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addClubModalLabel">Add Club</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="add-clubs.php" enctype="multipart/form-data">
                            <div class="modal-body">
                                <label style="display: flex; justify-content: center; align-items: center; flex-direction: column;">Club Image</label><br>
                                <div style="display: flex; justify-content: center; align-items: center; flex-direction: column;">
                                    <img id="imagePreview" src="admin/dist/img/default-featured-image.jpg" alt="Image Preview"
                                        style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; cursor: pointer;"
                                        onclick="document.getElementById('club_image').click();">
                                    <input type="file" id="club_image" name="club_image" accept="image/*" style="display: none;" onchange="previewImage(event)">
                                </div>
                                <div class="form-group">
                                    <label for="club_name">Club Name</label>
                                    <input type="text" class="form-control" id="club_name" name="club_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Add Club</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <aside class="control-sidebar control-sidebar-dark"></aside>
        </div>

        <!-- jQuery -->
        <script src="admin/plugins/jquery/jquery.min.js"></script>
        <!-- Bootstrap 4 -->
        <script src="admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
        <!-- AdminLTE App -->
        <script src="admin/dist/js/adminlte.min.js"></script>

        <!-- Select all checkboxes script -->
        <script>
            function previewImage(event) {
                const imagePreview = document.getElementById('imagePreview');
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            }
        </script>
</body>

</html>