<?php
session_start();
include 'db_connect.php';

$postId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
// echo "<script>alert('$postId');</script>";
if (!$postId) {
       header('Location: homepage.php');
       exit;
}


$postId = mysqli_real_escape_string($conn, $postId);

// for creater 
$taskSql = "
    SELECT *, u.name AS creater_name, u.email AS creater_email
    FROM tasks t
    JOIN users u ON t.user_id = u.id
    WHERE t.id = '$postId'
   
";
$taskResult = mysqli_query($conn, $taskSql);
$task = mysqli_fetch_assoc($taskResult);

if (!$task) {
       echo "<h1 style='text-align:center;margin-top:4rem;font-family:sans-serif;'>Post not found.</h1>";
       exit;
}

// Get comments 
// $comments = [];

$commentSql = "
    SELECT c.id, c.comment_text, c.created_at, u.name, u.email
    FROM comments c
    JOIN users u ON c.user_id = u.id
    ORDER BY c.created_at DESC
";
$comments = mysqli_query($conn, $commentSql);


// Comment Insert Logic
if (isset($_POST['add_comment']) && isset($_SESSION['user_id'])) {
       $task_id = $_POST['task_id'];
       $user_id = $_SESSION['user_id'];
       $comment_text = mysqli_real_escape_string($conn, $_POST['comment_text']);

       $insertQuery = "INSERT INTO comments (task_id, user_id, comment_text) VALUES ('$task_id', '$user_id', '$comment_text')";
       mysqli_query($conn, $insertQuery);
        header("Location: post_detail.php?id=$postId");
           exit();
       // Page reload to show new comment
       //     header("Location: " . $_SERVER['PHP_SELF']);
       //     exit();
}


// Get total likes count
$likeCountSql = "SELECT COUNT(*) AS total_likes FROM likes WHERE task_id = '$postId'";
$likeResult = mysqli_query($conn, $likeCountSql);
$likeRow = mysqli_fetch_assoc($likeResult);
$totalLikes = $likeRow['total_likes'] ?? 0;




// Get all comments 
$CommentCountSql = "SELECT COUNT(*) AS total_comments FROM comments WHERE task_id = '$postId'";
$CommentResult = mysqli_query($conn, $CommentCountSql);
$CommentRow = mysqli_fetch_assoc($CommentResult);
$totalComments = $CommentRow['total_comments'];
// echo $totalComments;

?>
<!DOCTYPE html>
<html lang="en">

<head>
       <meta charset="UTF-8">
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title><?php echo htmlspecialchars($task['task_name']); ?> | TaskFlow</title>
       <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<style>

       * {
              margin: 0;
              padding: 0;
              box-sizing: border-box;
       }

       body {
              font-family: 'Poppins', sans-serif;
              background: #f8f9fa;
              color: #333;
       }

       .post-header {
              background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
              color: white;
              padding: 3rem 0;
              margin-bottom: 3rem;
              position: relative;
              overflow: hidden;
       }

       .post-header::before {
              content: '';
              position: absolute;
              top: 0;
              left: 0;
              right: 0;
              bottom: 0;
              background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="white" opacity="0.1"/></svg>');
              opacity: 0.3;
       }

       .post-header .container {
              position: relative;
              z-index: 2;
       }

       .btn-back {
              background: rgba(255, 255, 255, 0.2);
              border: 2px solid rgba(255, 255, 255, 0.5);
              color: white;
              padding: 0.6rem 1.5rem;
              border-radius: 50px;
              font-weight: 600;
              transition: all 0.3s ease;
              text-decoration: none;
              display: inline-flex;
              align-items: center;
              gap: 0.5rem;
       }

       .btn-back:hover {
              background: white;
              color: #667eea;
              transform: translateX(-5px);
              box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
       }

       .post-title {
              font-size: 2.5rem;
              font-weight: 700;
              margin-bottom: 0.5rem;
              line-height: 1.3;
       }

       .post-meta {
              display: flex;
              flex-wrap: wrap;
              gap: 1.5rem;
              align-items: center;
              color: rgba(255, 255, 255, 0.9);
              font-size: 0.95rem;
       }

       .post-meta-item {
              display: flex;
              align-items: center;
              gap: 0.5rem;
       }

       .post-meta-item i {
              font-size: 1.1rem;
       }

       .content-card {
              background: white;
              border: none;
              border-radius: 20px;
              padding: 2.5rem;
              margin-bottom: 2rem;
              box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
              transition: all 0.3s ease;
       }

       .content-card:hover {
              box-shadow: 0 10px 35px rgba(102, 126, 234, 0.15);
       }

       .status-badge {
              background: linear-gradient(135deg, #667eea, #764ba2);
              color: white;
              padding: 0.5rem 1.2rem;
              border-radius: 50px;
              font-weight: 600;
              font-size: 0.9rem;
              display: inline-block;
       }

       .like-badge {
              background: rgba(102, 126, 234, 0.12);
              color: #667eea;
              padding: 0.6rem 1.3rem;
              border-radius: 50px;
              font-weight: 600;
              display: inline-flex;
              align-items: center;
              gap: 0.6rem;
              font-size: 0.95rem;
       }

       .like-badge i {
              font-size: 1.1rem;
       }

       .post-image-wrapper {
              border-radius: 15px;
              overflow: hidden;
              margin: 2rem 0;
              box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
       }

       .post-image {
              width: 100%;
              max-height: 600px;
              object-fit: cover;
              display: block;
       }

       .description-section {
              margin-top: 2.5rem;
       }

       .description-section h4 {
              color: #333;
              font-weight: 700;
              margin-bottom: 1.5rem;
              font-size: 1.5rem;
              position: relative;
              padding-bottom: 0.5rem;
       }

       .description-section h4::after {
              content: '';
              position: absolute;
              bottom: 0;
              left: 0;
              width: 60px;
              height: 3px;
              background: linear-gradient(135deg, #667eea, #764ba2);
              border-radius: 3px;
       }

       .description-text {
              font-size: 1.1rem;
              line-height: 1.8;
              color: #555;
       }

       .comments-card {
              background: white;
              border: none;
              border-radius: 20px;
              padding: 2.5rem;
              box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
       }

       .comments-header {
              display: flex;
              align-items: center;
              gap: 0.8rem;
              margin-bottom: 2rem;
              color: #333;
              font-weight: 700;
              font-size: 1.4rem;
       }

       .comments-header i {
              color: #667eea;
              font-size: 1.5rem;
       }

       .comment-item {
              background: linear-gradient(135deg, #f9f9ff 0%, #f1f5ff 100%);
              border: none;
              border-radius: 15px;
              padding: 1.5rem;
              margin-bottom: 1.5rem;
              transition: all 0.3s ease;
       }

       .comment-item:hover {
              transform: translateY(-3px);
              box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
       }

       .comment-header {
              display: flex;
              justify-content: space-between;
              align-items: start;
              margin-bottom: 1rem;
              flex-wrap: wrap;
              gap: 0.5rem;
       }

       .comment-author {
              font-weight: 600;
              color: #667eea;
              font-size: 1rem;
       }

       .comment-email {
              color: #888;
              font-size: 0.85rem;
              margin-left: 0.5rem;
       }

       .comment-time {
              color: #999;
              font-size: 0.85rem;
              display: flex;
              align-items: center;
              gap: 0.4rem;
       }

       .comment-text {
              color: #444;
              line-height: 1.6;
              margin: 0;
              font-size: 0.95rem;
       }

       .add-comment {
              margin-top: 1.5rem;
       }

       .add-comment .input-group {
              box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
              border-radius: 50px;
              overflow: hidden;
       }

       .add-comment .form-control {
              border: 2px solid #e0e0e0;
              border-radius: 50px 0 0 50px;
              padding: 0.8rem 1.5rem;
              font-size: 0.95rem;
              border-right: none;
       }

       .add-comment .form-control:focus {
              border-color: #667eea;
              box-shadow: none;
       }

       .add-comment .btn {
              border-radius: 0 50px 50px 0;
              padding: 0.8rem 1.5rem;
              background: linear-gradient(135deg, #667eea, #764ba2);
              border: 2px solid #667eea;
              color: white;
              font-weight: 600;
              transition: all 0.3s ease;
       }

       .add-comment .btn:hover {
              background: linear-gradient(135deg, #764ba2, #667eea);
              transform: scale(1.05);
       }

       .no-comments {
              text-align: center;
              padding: 3rem 1rem;
              color: #999;
       }

       .no-comments i {
              font-size: 3rem;
              margin-bottom: 1rem;
              color: #ddd;
       }

       .comment-toggle-btn {
              background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
              border: 2px dashed rgba(102, 126, 234, 0.3);
              color: #667eea;
              padding: 0.8rem 2rem;
              border-radius: 50px;
              font-weight: 600;
              font-size: 0.95rem;
              width: 100%;
              transition: all 0.3s ease;
              cursor: pointer;
              display: flex;
              align-items: center;
              justify-content: center;
              gap: 0.6rem;
              margin-top: 1rem;
       }

       .comment-toggle-btn:hover {
              background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
              border-color: rgba(102, 126, 234, 0.6);
              transform: translateY(-2px);
              box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
       }

       .comment-toggle-btn i {
              font-size: 1rem;
              transition: transform 0.3s ease;
       }

       .comment-toggle-btn.expanded i {
              transform: rotate(180deg);
       }

       .hidden-comments {
              display: none;
       }

       .hidden-comments.show {
              display: block;
       }

       .sidebar-widget {
              background: white;
              border-radius: 20px;
              padding: 2rem;
              box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
              margin-bottom: 2rem;
       }

       .widget-title {
              font-size: 1.2rem;
              font-weight: 700;
              color: #333;
              margin-bottom: 1.5rem;
              display: flex;
              align-items: center;
              gap: 0.6rem;
       }

       .widget-title i {
              color: #667eea;
       }

       .author-info {
              display: flex;
              flex-direction: column;
              gap: 0.8rem;
       }

       .author-info-item {
              display: flex;
              align-items: center;
              gap: 0.8rem;
              padding: 0.8rem;
              background: #f9f9ff;
              border-radius: 10px;
       }

       .author-info-item i {
              color: #667eea;
              font-size: 1.1rem;
              min-width: 20px;
       }

       @keyframes fadeInUp {
              from {
                     opacity: 0;
                     transform: translateY(30px);
              }

              to {
                     opacity: 1;
                     transform: translateY(0);
              }
       }

       .content-card,
       .comments-card,
       .sidebar-widget {
              animation: fadeInUp 0.6s ease;
       }

       @media (max-width: 768px) {
              .post-title {
                     font-size: 1.8rem;
              }

              .content-card,
              .comments-card,
              .sidebar-widget {
                     padding: 1.5rem;
              }

              .post-meta {
                     flex-direction: column;
                     align-items: flex-start;
                     gap: 0.8rem;
              }
       }
</style>

<body>
       <div class="post-header">
              <div class="container">
                     <a href="homepage.php" class="btn-back">
                            <i class="fas fa-arrow-left"></i> Back to Home
                     </a>
                     <h1 class="post-title mt-4"><?php echo htmlspecialchars($task['task_name']); ?></h1>
                     <div class="post-meta">
                            <div class="post-meta-item">
                                   <i class="far fa-calendar"></i>
                                   <span><?php echo date('d M Y, h:i A', strtotime($task['created_at'])); ?></span>
                            </div>
                            <?php if (!empty($task['creater_name'])): ?>
                                   <div class="post-meta-item">
                                          <i class="far fa-user"></i>
                                          <span><?php echo htmlspecialchars($task['creater_name']); ?></span>
                                   </div>
                            <?php endif; ?>
                            <div class="post-meta-item">
                                   <i class="fa-heart fa-solid"></i>
                                   <span><?php echo $totalLikes; ?> Likes</span>
                            </div>
                     </div>
              </div>
       </div>

       <div class="container pb-5">
              <div class="row">
                     <div class="col-lg-8">
                            <div class="content-card">
                                   <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                          <span class="status-badge">
                                                 <i class="fas fa-check-circle me-1"></i> <?php echo htmlspecialchars($task['status']); ?>
                                          </span>
                                          <span class="like-badge">
                                                 <i class="fa-heart fa-solid"></i> <?php echo $totalLikes; ?> Likes
                                          </span>
                                   </div>

                                   <?php if (!empty($task['image'])): ?>
                                          <div class="post-image-wrapper">
                                                 <img src="<?php echo htmlspecialchars($task['image']); ?>" alt="Post Image" class="post-image">
                                          </div>
                                   <?php endif; ?>

                                   <div class="description-section">
                                          <h4><i class="fas fa-align-left me-2"></i>Description</h4>
                                          <p class="description-text"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                                   </div>
                            </div>
                     </div>

                     <div class="col-lg-4">
                            <?php if (!empty($task['creater_name'])): ?>
                                   <div class="sidebar-widget">
                                          <h3 class="widget-title">
                                                 <i class="fas fa-user-circle"></i> Author Info
                                          </h3>
                                          <div class="author-info">
                                                 <div class="author-info-item">
                                                        <i class="fas fa-user"></i>
                                                        <span><?php echo htmlspecialchars($task['creater_name']); ?></span>
                                                 </div>
                                                 <div class="author-info-item">
                                                        <i class="fas fa-envelope"></i>
                                                        <span><?php echo htmlspecialchars($task['creater_email']); ?></span>
                                                 </div>
                                          </div>
                                   </div>
                            <?php endif; ?>

                            <div class="sidebar-widget">
                                   <h3 class="widget-title">
                                          <i class="fas fa-info-circle"></i> Post Info
                                   </h3>
                                   <div class="author-info">
                                          <div class="author-info-item">
                                                 <i class="fas fa-check-circle"></i>
                                                 <span>Status: <?php echo htmlspecialchars($task['status']); ?></span>
                                          </div>
                                          <div class="author-info-item">
                                                 <i class="fas fa-heart"></i>
                                                 <span><?php echo $totalLikes; ?> Total Likes</span>
                                          </div>
                                          <div class="author-info-item">
                                                 <i class="fas fa-comments"></i>
                                                 <span><?php echo htmlspecialchars($totalComments); ?> Comments</span>
                                          </div>
                                   </div>
                            </div>

                            <div class="comments-card">
                                   <div class="comments-header">
                                          <i class="fas fa-comments"></i>
                                          <span>Comments (<?php echo htmlspecialchars($totalComments); ?>)</span>
                                   </div>

                                   <?php if (isset($_SESSION['user_id'])): ?>
                                          <form method="POST" class="add-comment" action="">
                                                 <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                 <div class="input-group">
                                                        <input type="text" name="comment_text" class="form-control" placeholder="Write a comment..." required>

                                                        <button class="btn" type="submit" name="add_comment"><i class="fas fa-paper-plane"></i></button>
                                                 </div>
                                          </form>
                                   <?php else: ?>
                                          <p class="text-muted small mt-2">🔒 <a href="login.php">Login</a> to add a comment.</p>
                                   <?php endif; ?>

                                   <div class="mt-4">
                                          <?php if (mysqli_num_rows($comments) > 0): ?>
                                                 <?php 
                                                 $commentCount = 0;
                                                 $totalCommentsAvailable = mysqli_num_rows($comments);
                                                 mysqli_data_seek($comments, 0); 
                                                 ?>
                                                 
                                                 <?php while ($row = mysqli_fetch_assoc($comments)): ?>
                                                        <?php 
                                                        $commentCount++;
                                                        $isHidden = $commentCount > 2 ? 'hidden-comments' : '';
                                                        ?>
                                                        <div class="comment-item <?php echo $isHidden; ?>">
                                                               <div class="comment-header">
                                                                      <div>
                                                                             <span class="comment-author"><?php echo htmlspecialchars($row['name']); ?></span>
                                                                      </div>
                                                                      <div class="comment-time">
                                                                             <i class="far fa-clock"></i>
                                                                             <span><?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></span>
                                                                      </div>
                                                               </div>
                                                               <p class="comment-text"><?php echo nl2br(htmlspecialchars($row['comment_text'])); ?></p>
                                                        </div>
                                                 <?php endwhile; ?>

                                                 <?php if ($totalCommentsAvailable > 2): ?>
                                                        <button type="button" class="comment-toggle-btn" id="toggleComments">
                                                               <i class="fas fa-chevron-down"></i>
                                                               <span class="toggle-text">See All Comments (<?php echo $totalCommentsAvailable - 4; ?> more)</span>
                                                        </button>
                                                 <?php endif; ?>
                                          <?php else: ?>
                                                 <div class="no-comments">
                                                        <i class="far fa-comment-slash"></i>
                                                        <p class="mb-0">No comments yet. Be the first to comment!</p>
                                                 </div>
                                          <?php endif; ?>
                                   </div>
                            </div>
                     </div>
              </div>
       </div>

       <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
       <script>
              // Toggle comments visibility
              document.addEventListener('DOMContentLoaded', function() {
                     const toggleBtn = document.getElementById('toggleComments');
                     if (toggleBtn) {
                            toggleBtn.addEventListener('click', function() {
                                   const hiddenComments = document.querySelectorAll('.hidden-comments');
                                   const toggleText = this.querySelector('.toggle-text');
                                   const icon = this.querySelector('i');
                                   
                                   hiddenComments.forEach(comment => {
                                          comment.classList.toggle('show');
                                   });

                                   if (this.classList.contains('expanded')) {
                                          this.classList.remove('expanded');
                                          toggleText.textContent = 'See All Comments (<?php echo $totalCommentsAvailable - 4; ?> more)';
                                   } else {
                                          this.classList.add('expanded');
                                          toggleText.textContent = 'Show Less Comments';
                                   }
                            });
                     }
              });
       </script>
</body>

</html>