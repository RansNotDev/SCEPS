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
    if ($stmt) {
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

// Fetch user information and get the user's club ID
include 'user-info.php'; // This should set $club_id, $full_name, $image_src, and $club_name

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $full_name = htmlspecialchars(trim($_POST['full_name']));
    $new_username = htmlspecialchars(trim($_POST['username']));
    $new_password = trim($_POST['new_password']);

    // Get current hashed password from the database for reference
    // Assume you have a query fetching it into $current_hashed_password

    // Handle profile picture upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $imageData = file_get_contents($_FILES["image"]["tmp_name"]);
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            // Update database with new image data
            $stmt = $conn->prepare("UPDATE club_members SET image=? WHERE username=?");
            $stmt->bind_param("bs", $imageData, $username);
            $stmt->execute();
        } else {
            echo "<p style='color: red;'>File is not an image.</p>";
        }
    }

    // Hash password if provided
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE club_members SET full_name=?, username=?, password=?, image=? WHERE username=?");
        $stmt->bind_param("sssss", $full_name, $new_username, $hashed_password, $imageData, $username);
    } else {
        $stmt = $conn->prepare("UPDATE club_members SET full_name=?, username=?, image=? WHERE username=?");
        $stmt->bind_param("ssss", $full_name, $new_username, $imageData, $username);
    }

    $stmt->execute();
    header('Location: profile.php?success=1');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="../admin/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../admin/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="hold-transition sidebar-mini">

    <?php include 'member_sidebar.php' ?>
    <div class="container mt-5">
        <h1 class="text-center">Edit Profile</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" role="alert">
                Profile updated successfully!
            </div>
        <?php endif; ?>
        <div class="mt-4 text-center">
            <img src="<?php echo htmlspecialchars($image_src); ?>" alt="Profile Picture" class="rounded-circle" style="width:100px;height:100px;">
            
        </div>
        <p class="mt-4 text-left">Current Club: <?php echo htmlspecialchars($club_name); ?></p>
        <form action="" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" class="form-control" name="full_name" id="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
                <div class="invalid-feedback">Please enter your full name.</div>
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" required>
                <div class="invalid-feedback">Please enter a username.</div>
            </div>
            <div class="form-group">
                <label for="image">Profile Picture:</label>
                <input type="file" class="form-control-file" name="image" id="image" accept="image/*">
            </div>
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" class="form-control" name="new_password" id="new_password" placeholder="Leave blank to keep current password">
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>

    <!-- jQuery -->
    <script src="../admin/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../admin/dist/js/adminlte.min.js"></script>
    <script>
        // Disable form submissions if there are invalid fields
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
</body>

</html>