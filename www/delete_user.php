<?php
session_start();
require_once('Config.php');
$sqlite = new SQLite3(db_file);

if (!isset($_SESSION['username']) || $_SESSION['role'] !== "admin") {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $stmt = $sqlite->prepare("DELETE FROM users WHERE user_id = :user_id");
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);

    $result = $stmt->execute();

    if ($result) {
        header("Location: reports.php");
        exit();
    } else {
        echo "Error deleting user.";
    }
} else {
    echo "Invalid request.";
}

$sqlite->close();
?>
