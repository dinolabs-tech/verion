<?php
session_start();
require_once 'database/db_connection.php';

if (isset($_SESSION['user_id']) && isset($_POST['query_id']) && isset($_POST['status'])) {
    $query_id = $_POST['query_id'];
    $status = "opened";
    $user_id = $_SESSION['user_id'];

    // Ensure the user is authorized to update the query
    $stmt = $conn->prepare("SELECT raised_to_user_id FROM queries WHERE query_id = ?");
    $stmt->bind_param("i", $query_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $query = $result->fetch_assoc();
        if ($query['raised_to_user_id'] == $user_id) {
            $update_stmt = $conn->prepare("UPDATE queries SET status = ? WHERE query_id = ?");
            $update_stmt->bind_param("si", $status, $query_id);
            if ($update_stmt->execute()) {
                echo "Status updated successfully.";
            } else {
                echo "Error updating status.";
            }
            $update_stmt->close();
        } else {
            echo "You are not authorized to update this query.";
        }
    } else {
        echo "Query not found.";
    }
    $stmt->close();
} else {
    echo "Invalid request.";
}
?>
