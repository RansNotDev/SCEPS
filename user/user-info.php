<?php

$user_info = fetch_data("SELECT image, full_name, role, club_id FROM club_members WHERE username = '$username'");

// Initialize variables for display
$image_src = 'devnull_access/dist/img/default.jpg'; // Default user image path
$full_name = 'Guest'; // Default name if no user info
$club_name = ''; // Initialize club name
$club_image_src = 'devnull_access/dist/img/default_club.jpg'; // Default club image path

if (!empty($user_info)) {
    // Fetch the first user's information
    $full_name = htmlspecialchars($user_info[0]['full_name']); // Sanitize full name
    $image_data = $user_info[0]['image'];
    $club_id = $user_info[0]['club_id']; // Get the club_id

    // Encode the user image data to base64 if it exists
    if (!empty($image_data)) {
        $base64_image = base64_encode($image_data);
        $image_src = 'data:image/jpeg;base64,' . $base64_image; // Adjust the MIME type as needed
    }

    // Fetch the club name and image using the club_id
    $club_info = fetch_data("SELECT club_name, club_image FROM clubs WHERE club_id = '$club_id'");

    if (!empty($club_info)) {
        $club_name = htmlspecialchars($club_info[0]['club_name']); // Sanitize club name
        
        // Encode the club image data to base64 if it exists
        $club_image_data = $club_info[0]['club_image'];
        if (!empty($club_image_data)) {
            $base64_club_image = base64_encode($club_image_data); // Ensure this is binary data
            $club_image_src = 'data:image/jpeg;base64,' . $base64_club_image; // Adjust MIME type as needed
        }
    }
}

?>