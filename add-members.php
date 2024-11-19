<?php
// Start sessions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include DB connection
include './connections/db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

// Handle form submission for adding members
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $hashed_password = password_hash($password, PASSWORD_BCRYPT); // Hash the password
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $club_id = (int)$_POST['club_id'];
    $joined_at = date('Y-m-d H:i:s');

    // Image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
        $image_type = mime_content_type($_FILES['image']['tmp_name']);
        if (strpos($image_type, 'image') === false) {
            $_SESSION['error'] = "Uploaded file is not a valid image.";
            header("Location: club-members.php");
            exit();
        }
    }

    // Validation for username and password
    if (empty($username) || empty($email) || empty($full_name) || empty($role) || empty($club_id)) {
        $_SESSION['error'] = "All fields are required.";
    } elseif (is_null($image)) {
        $_SESSION['error'] = "Profile image is required.";
    } elseif (preg_match('/\s/', $username)) {
        $_SESSION['error'] = "Username must not contain whitespaces.";
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[^a-zA-Z\d]/', $password)) {
        $_SESSION['error'] = "Password must include uppercase, lowercase, and special characters.";
    } else {
        // Check for duplicate username or email
        $stmt = $conn->prepare("SELECT COUNT(*) FROM club_members WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $_SESSION['error'] = "Username or email already exists.";
        } else {
            // Insert data into the club_members table
            $stmt = $conn->prepare("INSERT INTO club_members (image, username, password, email, full_name, role, joined_at, club_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssi", $image, $username, $hashed_password, $email, $full_name, $role, $joined_at, $club_id);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Member added successfully!";
            } else {
                $_SESSION['error'] = "Error adding member. Please try again.";
            }
            $stmt->close();
        }
    }

    // Redirect back to club members page
    header("Location: club-members.php");
    exit();
}
?>
