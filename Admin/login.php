<?php
include '../db_connect.php';

$hashedPassword = password_hash("admin123", PASSWORD_DEFAULT);
$query = "INSERT INTO users (name, email, password, role) Values('ADMIN', 'admin@gmail.com', '$hashedPassword', 'admin')";
$result = mysqli_query($conn, $query);
echo "Sucssfully LoGIN: $result";

?>