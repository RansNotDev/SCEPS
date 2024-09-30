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
$total_members = fetch_data("SELECT COUNT(*) AS count FROM club_members")[0]['count'];
$total_pages = ceil($total_members / $limit);

// Fetch club members with pagination
include 'user-info.php';
// Fetch clubs for the dropdown
function fetch_clubs()
{
    global $conn;
    $query = "SELECT club_id, club_name FROM clubs";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch club members with pagination, including club names
$club_members = fetch_data("SELECT cm.member_id, cm.username, cm.email, cm.full_name, cm.role, cm.joined_at, cm.image, cl.club_name 
                            FROM club_members cm 
                            JOIN clubs cl ON cm.club_id = cl.club_id 
                            LIMIT $start, $limit");

// Handle deletion of members
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_members'])) {
    if (!empty($_POST['member_ids'])) {
        $member_ids = implode(',', array_map('intval', $_POST['member_ids']));
        $delete_query = "DELETE FROM club_members WHERE member_id IN ($member_ids)";

        // Execute delete query and check for errors
        if ($conn->query($delete_query) === TRUE) {
            // Deletion was successful, redirect to the same page
            header("Location: club-members.php?page=$page&success=1");
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
    <title>Club Members</title>

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
                            <h1 class="m-0">Club Members</h1>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="club-members.php">

                <!-- Main Content -->
                <div class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12 col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h3 class="card-title">List of Club Members</h3>
                                        <div class="ml-auto">
                                            <a href="#" class="btn btn-success" data-toggle="modal" data-target="#addMemberModal">Add Member</a>
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
                                                    <th>Profile Image</th>
                                                    <th>Username</th>
                                                    <th>Email</th>
                                                    <th>Full Name</th>
                                                    <th>Role</th>
                                                    <th>Club Name</th>
                                                    <th>Joined At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($club_members as $member): ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" name="member_ids[]" value="<?php echo htmlspecialchars($member['member_id']); ?>">
                                                        </td>
                                                        <td><?php echo htmlspecialchars($member['member_id']); ?></td>
                                                        <td class="text-center">
                                                            <div class="d-flex justify-content-center">
                                                                <?php if (!empty($member['image'])): ?>
                                                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($member['image']); ?>" alt="Profile Image" width="75" height="75" class="img-circle">
                                                                <?php else: ?>
                                                                    <img src="admin/dist/img/default.jpg" alt="Default Image" width="75" height="75" class="img-circle">
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($member['username']); ?></td>
                                                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                                                        <td><?php echo htmlspecialchars($member['full_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($member['role']); ?></td>
                                                        <td><?php echo htmlspecialchars($member['club_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($member['joined_at']); ?></td>
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
                                                        Are you sure you want to delete the selected members?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger" name="delete_members">Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pagination -->
                                        <nav aria-label="Page navigation example">
                                            <ul class="pagination justify-content-center mt-4">
                                                <!-- Previous button -->
                                                <li class="page-item <?php if ($page <= 1) {
                                                                            echo 'disabled';
                                                                        } ?>">
                                                    <a class="page-link" href="club-members.php?page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
                                                </li>

                                                <!-- Page numbers -->
                                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                        <a class="page-link" href="club-members.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                    </li>
                                                <?php endfor; ?>

                                                <!-- Next button -->
                                                <li class="page-item <?php if ($page >= $total_pages) {
                                                                            echo 'disabled';
                                                                        } ?>">
                                                    <a class="page-link" href="club-members.php?page=<?php echo $page + 1; ?>">Next</a>
                                                </li>
                                            </ul>
                                        </nav>

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
                                                        <?php echo $_SESSION['success']; ?>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
            </form>
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
                    <?php echo $_SESSION['error']; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Add Member Modal -->
    <div class="modal fade" id="addMemberModal" tabindex="-1" role="dialog" aria-labelledby="addMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMemberModalLabel">Add New Member</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="add-members.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group text-center">
                            <label>Profile Image</label><br>
                            <div style="display: flex; justify-content: center; align-items: center; flex-direction: column;">
                                <img id="imagePreview" src="admin/dist/img/default.jpg" alt="Image Preview"
                                    style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; cursor: pointer;"
                                    onclick="document.getElementById('image').click();">
                                <input type="file" id="image" name="image" accept="image/*" style="display: none;" onchange="previewImage(event)">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select class="form-control" id="role" name="role">
                                <option value="Member">Member</option>
                                <option value="Club Leader">Club Leader</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="club">Club</label>
                            <select class="form-control" id="club" name="club_id" required>
                                <option value="">Select Club</option>
                                <?php
                                $clubs = fetch_clubs();
                                foreach ($clubs as $club):
                                ?>
                                    <option value="<?php echo htmlspecialchars($club['club_id']); ?>">
                                        <?php echo htmlspecialchars($club['club_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <aside class="control-sidebar control-sidebar-dark">
    </aside>
    </div>

    <!-- jQuery -->
    <script src="admin/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="admin/dist/js/adminlte.min.js"></script>

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
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                $('#errorModal').modal('show');
            <?php endif; ?>
        });
    </script>
    <script>
        // Show success modal with delay if the success message is set
        <?php if (isset($_SESSION['success'])): ?>
            setTimeout(function() {
                $('#successModal').modal('show');
                <?php unset($_SESSION['success']); ?> // Clear the success message after showing the modal
            }, 1000); // 1000 milliseconds = 1 second delay
        <?php endif; ?>

        // Show error modal with delay if the error message is set
        <?php if (isset($_SESSION['error'])): ?>
            setTimeout(function() {
                $('#errorModal').modal('show');
                <?php unset($_SESSION['error']); ?> // Clear the error message after showing the modal
            }, 1000); // 1000 milliseconds = 1 second delay
        <?php endif; ?>
    </script>

</body>

</html>