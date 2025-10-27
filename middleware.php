<?php

function checkRole($allowedRoles) {
    if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowedRoles)) {
        header("Location: homepage.php");
        exit();
    }
}
?>