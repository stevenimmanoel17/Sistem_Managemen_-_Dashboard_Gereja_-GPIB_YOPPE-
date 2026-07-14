<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "db_gpib");

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "DELETE FROM keuangan WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo "success"; 
    } else {
        echo "error";
    }
}
exit; // Pastikan skrip berhenti di sini
?>