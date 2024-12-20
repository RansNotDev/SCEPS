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
    $club_id = $_POST['club_id'] ?? null;

    // Check for existing event if it's a new event
    if (!$id) { // Only check for new events
        // Check for existing events with the same location and time
        $stmt = $conn->prepare("SELECT COUNT(*) FROM events WHERE location = ? AND event_date = ? AND ((start_time <= ? AND end_time >= ?) OR (start_time <= ? AND end_time >= ?))");
        $stmt->bind_param("ssssss", $location, $date, $start_time, $start_time, $end_time, $end_time);
        $stmt->execute();
        $stmt->bind_result($locationCount);
        $stmt->fetch();
        $stmt->close();

        if ($locationCount > 0) {
            echo 'Error: An event at the same location overlaps with another event at this time.';
            exit();
        }

        // Optional: Check for existing events with the same name, club, time, location, and date
        $stmt = $conn->prepare("SELECT COUNT(*) FROM events WHERE event_name = ? AND club_id = ? AND event_date = ? AND start_time = ? AND location = ?");
        $stmt->bind_param("sssss", $name, $club_id, $date, $start_time, $location);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            echo 'Error: An event with the same name, club, time, location, and date already exists.';
            exit();
        }

        // Check if there are already 10 events scheduled for this location on this date
        $stmt = $conn->prepare("SELECT COUNT(*) FROM events WHERE location = ? AND event_date = ?");
        $stmt->bind_param("ss", $location, $date);
        $stmt->execute();
        $stmt->bind_result($eventCount);
        $stmt->fetch();
        $stmt->close();

        if ($eventCount >= 10) {
            echo 'Error: The maximum number of events (10) for this location on this date has been reached.';
            exit();
        }
    }

    // Minimum duration check for new and updated events
    $startTimeObj = new DateTime($start_time);
    $endTimeObj = new DateTime($end_time);
    $timeDifference = $startTimeObj->diff($endTimeObj);

    // Ensure the duration is at least 2 hours
    if ($timeDifference->h < 1 && $timeDifference->days == 0) {
        echo 'Error: Event duration must be at least 1 hours.';
        exit();
    }

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
