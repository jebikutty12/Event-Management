<?php
require_once 'config/database.php';

$conn = getDBConnection();

// Admin credentials
$username = 'admin';
$email = 'admin@eventmanagement.com';
$password = 'admin123';

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Delete existing admin if any
$conn->query("DELETE FROM admins WHERE username = '$username'");

// Insert new admin
$stmt = $conn->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashed_password);

if ($stmt->execute()) {
    echo "<h2 style='color: green;'>✓ Admin account created successfully!</h2>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<br>";
    echo "<a href='admin/login.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go to Admin Login</a>";
    echo "<br><br>";
    echo "<p style='color: red;'><strong>IMPORTANT:</strong> Delete this file (create-admin.php) after creating the admin account for security reasons!</p>";
} else {
    echo "<h2 style='color: red;'>✗ Error creating admin account</h2>";
    echo "<p>" . $conn->error . "</p>";
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Admin Account</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
    </style>
</head>
<body>
</body>
</html>