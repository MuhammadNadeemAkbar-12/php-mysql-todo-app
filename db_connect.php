<?php
$servername = "localhost";  
$username   = "root";        
$password   = "";          
$database   = "task_manager_db";

$conn = mysqli_connect($servername, $username, $password, $database);

if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}else {
    // echo "Database connection is succesfull";
}

?>