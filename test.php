<?php 
include 'db_connect.php';
session_start();

$user_id = $_SESSION['user_id'];
echo $user_id;

$query = "SELECT * FROM tasks";
$result = mysqli_query($conn, $query);
// print_r($result);
// $posts = mysqli_fetch_assoc($result);
print_r($posts);

    // foreach($posts as $post){
        // print_r($post);
    // }
