<?php
session_start();
include './connections/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($action === 'fetch-external') {
        // Fetch events with NULL values in 'start_time' and 'end_time'
        $query = "SELECT * FROM events WHERE start_time IS NULL AND end_time IS NULL";
        $result = $conn->query($query);
        if ($result) {
            $events = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($events);
        } else {
            echo json_encode(['error' => $conn->error]);
        }
    }

    if ($action === 'fetch-calendar') {
        // Fetch all events regardless of 'start_time' and 'end_time' values
        $query = "SELECT * FROM events";
        $result = $conn->query($query);
        if ($result) {
            $events = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($events);
        } else {
            echo json_encode(['error' => $conn->error]);
        }
    }

    if ($action === 'add') {
        $event_name = $_POST['name'] ?? '';

        if (!empty($event_name)) {
            $created_at = date('Y-m-d H:i:s'); // Get current datetime
            $stmt = $conn->prepare("INSERT INTO events (event_name, created_at) VALUES (?, ?)");
            $stmt->bind_param('ss', $event_name, $created_at);
            if ($stmt->execute()) {
                echo 'Event added successfully';
            } else {
                echo 'Error adding event';
            }
            $stmt->close();
        } else {
            echo 'Event name is required';
        }
    }

    if ($action === 'delete') {
        $eventId = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
        $stmt->bind_param('i', $eventId);
        if ($stmt->execute()) {
            echo 'Event deleted successfully';
        } else {
            echo 'Failed to delete event: ' . $stmt->error;
        }
        $stmt->close();
    }

    if ($action === 'update') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $date = $_POST['date'];
        $start_time = $_POST['start_time'] ?? NULL;
        $end_time = $_POST['end_time'] ?? NULL;
        $location = $_POST['location'];

        $stmt = $conn->prepare("UPDATE events SET event_name = ?, event_description = ?, event_date = ?, start_time = ?, end_time = ?, location = ? WHERE event_id = ?");
        $stmt->bind_param('ssssssi', $name, $description, $date, $start_time, $end_time, $location, $id);
        if ($stmt->execute()) {
            echo 'Event updated successfully';
        } else {
            echo 'Failed to update event: ' . $stmt->error;
        }
        $stmt->close();
    }

    $conn->close();
}
?>
