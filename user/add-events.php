<?php
session_start();
include './connections/db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$action = $_POST['action'] ?? '';

if ($action == 'fetch-calendar') {
    $result = $conn->query("SELECT * FROM events");
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

    if ($id) {
        $stmt = $conn->prepare("UPDATE events SET event_name = ?, event_description = ?, event_date = ?, start_time = ?, end_time = ?, location = ? WHERE event_id = ?");
        $stmt->bind_param("ssssssi", $name, $description, $date, $start_time, $end_time, $location, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO events (event_name, event_description, event_date, start_time, end_time, location) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $description, $date, $start_time, $end_time, $location);
    }
    $stmt->execute();

    echo 'Event saved successfully';
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