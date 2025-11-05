<?php
session_start();
include 'db_connect.php';

function hasPermission($permission_name = null)
{
    global $conn;
    
    if (!isset($_SESSION['role_id'])) {
        return false;
    }
    
    $role_id = $_SESSION['role_id'];
    
    if ($permission_name) {
        $sql = "
            SELECT p.id
            FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.id
            WHERE rp.role_id = $role_id AND p.name = '$permission_name'
            LIMIT 1
        ";
        $result = mysqli_query($conn, $sql);
        
        return mysqli_num_rows($result) > 0;
    } else {
        $sql = "
            SELECT p.name AS permission_name
            FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.id
            WHERE rp.role_id = $role_id
        ";
        $permissions = mysqli_query($conn, $sql);
        
        $permission_list = [];
        if (mysqli_num_rows($permissions) > 0) {
            while ($row = mysqli_fetch_assoc($permissions)) {
                $permission_list[] = $row['permission_name'];
            }
        }
        
        return $permission_list;
    }
}

?>
