<?php
session_start();
require_once 'database/db_connection.php';

if (isset($_SESSION['user_id'])) {
    // Log the logout event
    $log_stmt = $conn->prepare("INSERT INTO session_logs (user_id, event_type, ip_address, user_agent) VALUES (?, 'logout', ?, ?)");
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    $log_stmt->bind_param("iss", $_SESSION['user_id'], $ip_address, $user_agent);
    $log_stmt->execute();
    $log_stmt->close();
}

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>