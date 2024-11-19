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

include 'user-info.php';
// Fetch clubs with pagination
$clubs = fetch_data("SELECT c.club_id, c.club_image, c.club_name, c.description, c.created_by, c.created_at 
                     FROM clubs c 
                     LIMIT $start, $limit");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_clubs'])) {
    if (!empty($_POST['club_ids'])) {
        $club_ids = implode(',', array_map('intval', $_POST['club_ids']));
        $delete_query = "DELETE FROM clubs WHERE club_id IN ($club_ids)";

        // Execute delete query and check for errors
        if ($conn->query($delete_query) === TRUE) {
            $_SESSION['success'] = "Clubs deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting records: " . $conn->error;
        }
    }
    header("Location: club.php?page=$page");
    exit();
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
    <link rel="stylesheet" href="devnull_access/plugins/fontawesome-free/css/all.min.css">

    <!-- Theme style -->
    <link rel="stylesheet" href="devnull_access/dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

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

            <form method="POST" action="club.php">

                <!-- Main Content -->
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
            </form>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">Success</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php echo isset($_SESSION['success']) ? $_SESSION['success'] : ''; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
                    <?php echo isset($_SESSION['error']) ? $_SESSION['error'] : ''; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
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
                <form method="POST" action="add-clubs.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group text-center">
                            <label>Club Image</label><br>
                            <div style="display: flex; justify-content: center; align-items: center; flex-direction: column;">
                                <img id="imagePreview" src="devnull_access/dist/img/default-featured-image.jpg" alt="Image Preview"
                                    style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; cursor: pointer;"
                                    onclick="document.getElementById('image').click();">
                                <input type="file" id="image" name="image" accept="image/*" style="display: none;" onchange="previewImage(event)">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="clubName">Club Name</label>
                            <input type="text" class="form-control" id="clubName" name="club_name" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Club</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <aside class="control-sidebar control-sidebar-dark">
    </aside>
    </div>

    <!-- jQuery -->
    <script src="devnull_access/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="devnull_access/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="devnull_access/dist/js/adminlte.min.js"></script>


    <!-- Select all checkboxes script -->
    <script>
        document.getElementById('select-all').addEventListener('click', function(event) {
            let checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = event.target.checked;
            });
        });
        document.getElementById('confirmDeleteButton').addEventListener('click', function() {
            // Submit the form containing the member IDs
            document.querySelector('form').submit();
        });

        function previewImage(event) {
            const imagePreview = document.getElementById('imagePreview');
            imagePreview.src = URL.createObjectURL(event.target.files[0]);
        }

        // Show modals on load if there are success or error messages
        $(document).ready(function() {
            <?php if (isset($_SESSION['success'])): ?>
                $('#successModal').modal('show');
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                $('#errorModal').modal('show');
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        });
    </script>
</body>

</html>