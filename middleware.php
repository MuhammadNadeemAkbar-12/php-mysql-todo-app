<?php

// session_start();
function checkRole($allowedRoles) {
    if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], $allowedRoles)) {
        header("Location: homepage.php");
        exit();
    }
}
?>