<?php

$user_info = fetch_data("SELECT image, full_name, role FROM club_members WHERE username = '$username'");

// Initialize variables for display
$image_src = 'devnull_access/dist/img/default.jpg'; // Default image path
$full_name = 'Guest'; // Default name if no user info

if (!empty($user_info)) {
    // Fetch the first user's information
    $full_name = htmlspecialchars($user_info[0]['full_name']); // Sanitize full name
    $image_data = $user_info[0]['image'];

    // Encode the image data to base64 if it exists
    if (!empty($image_data)) {
        $base64_image = base64_encode($image_data);
        $image_src = 'data:image/jpeg;base64,' . $base64_image; // Adjust the MIME type as needed
    }
}
