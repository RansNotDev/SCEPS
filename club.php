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
$clubs = fetch_data("SELECT club_id, club_image, club_name, description, created_by, created_at 
                     FROM clubs 
                     LIMIT $start, $limit");

// Handle deletion of clubs
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_clubs'])) {
    if (!empty($_POST['club_ids'])) {
        $club_ids = implode(',', array_map('intval', $_POST['club_ids']));
        $delete_query = "DELETE FROM clubs WHERE club_id IN ($club_ids)";

        // Execute delete query and check for errors
        if ($conn->query($delete_query) === TRUE) {
            // Deletion was successful, redirect to the same page
            header("Location: clubs.php?page=$page&success=1");
            exit();
        } else {
            // Handle deletion error
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="admin/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="admin/dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <?php include 'sidebar.php' ?>

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Clubs</h1>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="clubs.php">
                <div class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12 col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h3 class="card-title">List of Clubs</h3>
                                        <div class="ml-auto">
                                            <a href="#" class="btn btn-success" data-toggle="modal" data-target="#addClubModal">Add Club</a>
                                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#confirmDeleteModal">Delete Selected</button>
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
                                                        <td class="text-center">
                                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($club['club_image']); ?>" alt="Club Image" width="75" height="75" class="img-circle">
                                                        </td>
                                                        <td><?php echo htmlspecialchars($club['club_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($club['description']); ?></td>
                                                        <td><?php echo htmlspecialchars($club['created_by']); ?></td>
                                                        <td><?php echo htmlspecialchars($club['created_at']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>

                                        <!-- Confirmation Modal -->
                                        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete the selected clubs?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger" name="delete_clubs">Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pagination -->
                                        <nav aria-label="Page navigation example">
                                            <ul class="pagination justify-content-center mt-4">
                                                <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                                                    <a class="page-link" href="clubs.php?page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
                                                </li>
                                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                        <a class="page-link" href="clubs.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                    </li>
                                                <?php endfor; ?>
                                                <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                                                    <a class="page-link" href="clubs.php?page=<?php echo $page + 1; ?>">Next</a>
                                                </li>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
            </form>
        </div>
    </div>

    <!-- Add Club Modal -->
    <div class="modal fade" id="addClubModal" tabindex="-1" role="dialog" aria-labelledby="addClubModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClubModalLabel">Add New Club</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="add-club.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group text-center">
                            <label>Club Image</label><br>
                            <img id="clubImagePreview" src="admin/dist/img/default.jpg" alt="Image Preview"
                                style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; cursor: pointer;"
                                onclick="document.getElementById('clubImage').click();">
                            <input type="file" id="clubImage" name="club_image" accept="image/*" style="display: none;" onchange="previewClubImage(event)">
                        </div>
                        <div class="form-group">
                            <label for="club_name">Club Name</label>
                            <input type="text" class="form-control" id="club_name" name="club_name" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="created_by">Created By</label>
                            <input type="text" class="form-control" id="created_by" name="created_by" required>
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

    <script src="admin/plugins/jquery/jquery.min.js"></script>
    <script src="admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="admin/dist/js/adminlte.min.js"></script>

    <script>
        document.getElementById('select-all').addEventListener('click', function(event) {
            let checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = event.target.checked;
            });
        });

        function previewClubImage(event) {
            const imagePreview = document.getElementById('clubImagePreview');
            imagePreview.src = URL.createObjectURL(event.target.files[0]);
        }
    </script>
</body>

</html>