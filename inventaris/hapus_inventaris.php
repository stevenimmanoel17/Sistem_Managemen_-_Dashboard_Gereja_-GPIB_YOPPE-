<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "db_gpib");

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    echo "error_akses";
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $query = "DELETE FROM inventaris WHERE id='$id'";
    
    if (mysqli_query($conn, $query)) {
        echo "success";
        exit;
    } else {
        echo "error_query";
        exit;
    }
}

echo "error_id";
exit;
?>