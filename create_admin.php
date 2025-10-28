<?php
/**
 * File: create_admin.php
 * Purpose: This script creates a default admin user in the database.
 * It's intended for initial setup and should be run only once.
 */

require_once 'database/db_connection.php';


// Hash the default password for security.
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if admin user already exists
/**
 * Prepare a SQL statement to check if a user with the default username or email already exists.
 */
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();

// If the admin user already exists, display a message.
if ($stmt->num_rows > 0) {
    echo "Admin user '$username' or '$email' already exists.<br>";
} else {
    // If the admin user does not exist, create the user.
    $stmt->close();
    // Prepare a SQL statement to insert the new admin user into the database.
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

    // Execute the statement and display a success or error message.
    if ($stmt->execute()) {
        echo "Admin user '$username' created successfully with password '$password'. Please change this password after first login.<br>";
    } else {
        echo "Error creating admin user: " . $conn->error . "<br>";
    }
}
$stmt->close();
$conn->close();
?>