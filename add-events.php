<?php
session_start();
include './connections/db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

// Get the username from the session
$created_by = $_SESSION['username'] ?? null;

$action = $_POST['action'] ?? '';

if ($action == 'fetch-calendar') {
    $result = $conn->query("SELECT event_id, event_name, event_description, event_date, start_time, end_time, location, club_id FROM events");

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    echo json_encode($events);
} elseif ($action == 'update') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'] ?? '';
    $start_time = $_POST['start_time'] ?? null;
    $end_time = $_POST['end_time'] ?? null;
    $location = $_POST['location'] ?? '';
    $club_id = $_POST['club_id'] ?? null; // Get the club_id from the POST request

    if ($id) {
        // Update existing event
        $stmt = $conn->prepare("UPDATE events SET event_name = ?, event_description = ?, event_date = ?, start_time = ?, end_time = ?, location = ?, club_id = ?, created_by = ? WHERE event_id = ?");
        $stmt->bind_param("ssssssssi", $name, $description, $date, $start_time, $end_time, $location, $club_id, $created_by, $id);
    } else {
        // Insert new event
        $stmt = $conn->prepare("INSERT INTO events (event_name, event_description, event_date, start_time, end_time, location, club_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $name, $description, $date, $start_time, $end_time, $location, $club_id, $created_by);
    }

    if ($stmt->execute()) {
        echo 'Event saved successfully';
    } else {
        echo 'Error saving event: ' . $stmt->error;
    }

    $stmt->close(); // Close the statement
} elseif ($action == 'delete') {
    $id = $_POST['id'] ?? null;
    if ($id) {
        $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo 'Event deleted successfully';
    } else {
        echo 'No event ID provided';
    }
} else {
    echo 'Invalid action';
}
