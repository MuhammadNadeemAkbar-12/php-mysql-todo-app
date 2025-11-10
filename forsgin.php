<?php

include 'db_connect.php';


// $tablequery = "CREATE TABLE posts(
// id INT(6) PRIMARY KEY,
// email VARCHAR(255) UNIQUE 
// )";

// $tablequery = "ALTER TABLE posts ADD COLUMN name VARCHAR(100) AFTER id";
// $tablequery = "ALTER TABLE posts
// MODIFY COLUMN id INT AUTO_INCREMENT";


// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['email'])) {
//     $username = $_POST["name"];
//     $useremail = $_POST['email'];
//     $tablequery = $conn->prepare('INSERT INTO posts (name, email) VALUES (?, ?)');
//     $tablequery->bind_param("ss", $username, $useremail);
//     $tablequery->execute();
//     $tablequery->close();
// }



// $slectData = "SELECT * FROM posts";
// $result = mysqli_query($conn, $slectData);

// if (mysqli_num_rows($result) > 0) {
//     while ($row = mysqli_fetch_assoc($result)) {
//         echo "ID: " . $row['id'] . " - Name: " . $row['name'] . " - Email: " . $row['email'] . "<br>";
//     }
// }



// $result = mysqli_query($conn, $tablequery);


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users & Posts System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f5f7fa;
      padding-top: 30px;
      font-family: 'Poppins', sans-serif;
    }
    .card {
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .section-title {
      font-weight: 600;
      color: #333;
    }
  </style>
</head>
<body>

<div class="container">

  <!-- ADD USER FORM -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">Add New User</h5>
    </div>
    <div class="card-body">
      <form action="#" method="POST">
        <div class="mb-3">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" placeholder="Enter user name" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="Enter user email" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Add User</button>
      </form>
    </div>
  </div>

  <!-- ADD POST FORM -->
  <div class="card mb-4">
    <div class="card-header bg-success text-white">
      <h5 class="mb-0">Add New Post</h5>
    </div>
    <div class="card-body">
      <form action="#" method="POST">
        <div class="mb-3">
          <label class="form-label">Select User</label>

          <select name="user_id" class="form-select" required>
            <option value="">-- Select User --</option>
            <option value="1">Ali</option>
            <option value="2">Sara</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Post Title</label>
          <input type="text" name="title" class="form-control" placeholder="Enter post title" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Post Content</label>
          <textarea name="content" class="form-control" rows="3" placeholder="Write your post..." required></textarea>
        </div>
        <button type="submit" class="btn btn-success w-100">Add Post</button>
      </form>
    </div>
  </div>

  <!-- POSTS LIST -->
  <div class="card">
    <div class="card-header bg-dark text-white">
      <h5 class="mb-0">All Posts</h5>
    </div>
    <div class="card-body">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Post Title</th>
            <th>Author</th>
            <th>Content</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>My First Post</td>
            <td>Ali</td>
            <td>This is a demo post content...</td>
          </tr>
          <tr>
            <td>2</td>
            <td>Another Post</td>
            <td>Sara</td>
            <td>Example text showing how your posts will appear...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>

</body>
</html>
