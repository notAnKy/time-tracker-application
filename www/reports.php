<?php

session_start();
include "nav.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== "admin") {
    header("Location: login.php");
    exit();
}

require_once('Config.php');
$sqlite = new SQLite3(db_file);

function getAllUsers() {
    global $sqlite;

    $stmt = $sqlite->prepare("SELECT * FROM users");
    $result = $stmt->execute();

    if ($result->numColumns() > 0) {
        echo "<p> </p><br><br><table class='table table-hover'>";
        echo "<tr><th>User ID</th><th>Username</th><th>Update</th><th>Delete</th></tr>";

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row["user_id"] . "</td>";
            echo "<td><a href='reports_emp.php?user_id=" . $row["user_id"] . "'>" . $row["username"] . "</a></td>";
            echo "<td><a href='creat_uesr.php?user_id=" . $row["user_id"] . "'><button type='button' class='btn btn-success'>Update</button></a></td>";
            
            $deleteButton = ($row["role"] !== "admin") ? "<button type='button' class='btn btn-danger' onclick='confirmDelete(" . $row["user_id"] . ")'>Delete</button>" : "";
            
            echo "<td>" . $deleteButton . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "No users found";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Time Tracking</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="docs.css">
    <style>
        body {
            background-color: #C7F1F7;
        }

        .center-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        table {
            background-color: white;
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
    <script>
        function confirmDelete(userId) {
            var result = confirm("Are you sure you want to delete this user?");
            if (result) {
                // Customize your confirmation dialog style here
                var dialogStyles = "padding: 20px; background-color: #f2f2f2; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);";

                var dialog = document.createElement("div");
                dialog.style.cssText = dialogStyles;

                var message = document.createElement("p");
                message.textContent = "Deleting user. Please wait...";
                dialog.appendChild(message);

                document.body.appendChild(dialog);

                // Redirect after a short delay
                setTimeout(function () {
                    window.location.href = 'delete_user.php?user_id=' + userId;
                }, 1000); // You can adjust the delay time as needed
            }
        }
    </script>

</head>
<body>
    <?php
        getAllUsers();
    ?>
</body>
</html>
