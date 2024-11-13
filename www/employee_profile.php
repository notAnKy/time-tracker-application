<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

function getUserById($user_id) {
    require_once('Config.php');
    $sqlite = new SQLite3(db_file);

    $user_id = filter_var($user_id, FILTER_VALIDATE_INT);

    $stmt = $sqlite->prepare("SELECT clock_in, clock_out, strftime('%H:%M:%S', clock_out - clock_in) AS total FROM shifts WHERE user_id = ?");
    $stmt->bindParam(1, $user_id, SQLITE3_INTEGER);

    $result = $stmt->execute();

    if ($result) {
        echo "<p><br><br>";
        echo "<h2>Shifts</h2>";

        echo "<table class='table table-hover'>";
        echo "<tr><th>Clock In</th><th>Clock Out</th></tr>";

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["clock_in"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["clock_out"]) . "</td>";
            echo "</tr>";
        }

        echo "</table>";
        $stmt->close();
    } else {
        echo "No shift found for User ID: " . $user_id;
        $stmt->close();
    }

    $sqlite->close();
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
    <script src="bootstrap.bundle.min.js"></script>
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
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background-color :white;

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
</head>
<body>
<body>
    <?php
    if(isset($user_id)) {
        getUserById($user_id);
    } else {
        echo "User ID is not set.";
    }
    ?>
    <button class="btn btn-success" type="button" onclick="location.href='time_clock.php'">Back</button>
    <button class="btn btn-danger" type="button" onclick="location.href='logout.php'">Logout</button>
</body>
</html>