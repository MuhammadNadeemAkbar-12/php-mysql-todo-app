<?php 
// include 'db_connect.php';
// session_start();

// $user_id = $_SESSION['user_id'];
// echo $user_id;

// $query = "SELECT * FROM tasks";
// $result = mysqli_query($conn, $query);
// // print_r($result);
// // $posts = mysqli_fetch_assoc($result);
// print_r($posts);

//     // foreach($posts as $post){
//         // print_r($post);
//     // }

if (isset($_POST['update_task'])) {
    $edit_id = intval($_POST['edit_id']);
    $edit_task_name = trim($_POST['edit_task_name']);
    $edit_description = trim($_POST['edit_description']);
    $image_path = null;

    // Step 1: Check agar user ne nayi image upload ki hai
    if (!empty($_FILES["edit_task_image"]["name"])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Step 2: Nayi image ka naam aur path
        $image_name = time() . '_' . basename($_FILES["edit_task_image"]["name"]);
        $target_file = $target_dir . $image_name;

        // Step 3: File ko move karte hain uploads folder mein
        if (move_uploaded_file($_FILES["edit_task_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    // Step 4: Agar image upload hui hai to path bhi update karo
    if (!empty($image_path)) {
        $stmt = $conn->prepare("UPDATE tasks SET task_name = ?, description = ?, image = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssii", $edit_task_name, $edit_description, $image_path, $edit_id, $user_id);
    } else {
        // Agar image upload nahi hui, sirf name & desc update karo
        $stmt = $conn->prepare("UPDATE tasks SET task_name = ?, description = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $edit_task_name, $edit_description, $edit_id, $user_id);
    }

    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit;
}
?>


