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

// Handle form submission for adding clubs
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $club_name = trim($_POST['club_name']);
    $description = trim($_POST['description']);
    $created_by = $_SESSION['username']; // Get the logged-in user's username
    $updated_at = date('Y-m-d H:i:s');

    // Image upload
    $club_image = null;
    if (isset($_FILES['club_image']) && $_FILES['club_image']['error'] == 0) {
        $club_image = file_get_contents($_FILES['club_image']['tmp_name']);
        // Optional: Check if the file type is an image
        $image_type = mime_content_type($_FILES['club_image']['tmp_name']);
        if (strpos($image_type, 'image') === false) {
            $_SESSION['error'] = "Uploaded file is not a valid image.";
            header("Location: clubs.php");
            exit();
        }
    }

    // Validate fields to ensure none are blank and an image is uploaded
    if (empty($club_name) || empty($description) || is_null($club_image)) {
        $_SESSION['error'] = "All fields are required, and a club image must be uploaded.";
    } else {
        // Check for duplicate club name
        $stmt = $conn->prepare("SELECT COUNT(*) FROM clubs WHERE club_name = ?");
        $stmt->bind_param("s", $club_name);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $_SESSION['error'] = "Club name already exists.";
        } else {
            // Prepare SQL to insert data into the clubs table
            $stmt = $conn->prepare("INSERT INTO clubs (club_image, club_name, description, created_by, updated_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $club_image, $club_name, $description, $created_by, $updated_at);

            // Execute query and handle success or failure
            if ($stmt->execute()) {
                // Set success message in session
                $_SESSION['success'] = "Club added successfully!";
            } else {
                // Set error message in session
                $_SESSION['error'] = "Error adding club. Please try again.";
            }

            // Close the statement
            $stmt->close();
        }
    }

    // Redirect back to clubs page
    header("Location: club.php");
    exit();
}
