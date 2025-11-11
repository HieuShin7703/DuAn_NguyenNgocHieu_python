<?php
require_once "../db.php";

$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$email = "admin@example.com";
$role = "Admin";

$stmt = $conn->prepare("SELECT * FROM Users WHERE username=?");
$stmt->execute([$username]);
if ($stmt->rowCount() > 0) {
    $conn->prepare("UPDATE Users SET password_hash=?, role=?, email=? WHERE username=?")
         ->execute([$password, $role, $email, $username]);
    echo "✅ Admin password reset successful.";
} else {
    $stmt = $conn->prepare("INSERT INTO Users(username, password_hash, email, role) VALUES(?, ?, ?, ?)");
    $stmt->execute([$username, $password, $email, $role]);
    echo "✅ Admin account created successfully.";
}
?>
