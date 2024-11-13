<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

require_once('Config.php');
$conn = new SQLite3(db_file);
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["clock_action"])) {
    $clockAction = $_POST["clock_action"];
    
    if ($clockAction === "clock_in") {
        $userId = getUserId($conn, $username);
        clockIn($conn, $userId);
    } elseif ($clockAction === "clock_out") {
        $userId = getUserId($conn, $username);
        clockOut($conn, $userId);
    }
}

function getClockInTime($conn, $userId) {
    $stmt = $conn->prepare("SELECT clock_in FROM shifts WHERE user_id = ? AND clock_out IS NULL");
    
    if ($stmt) {
        $stmt->bindValue(1, $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();

        if ($result) {
            $shift = $result->fetchArray(SQLITE3_ASSOC);
            return $shift["clock_in"];
        }
    } else {
        echo "Error in preparing the statement: " . $conn->lastErrorMsg();
    }

    return null;
}


function getUserId($conn, $username) {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username=?");
    $stmt->bindValue(1, $username, SQLITE3_TEXT);
    $result = $stmt->execute();

    if ($result) {
        $user_id = $result->fetchArray(SQLITE3_NUM);
        return $user_id[0];
    }

    return null;
}

function clockIn($conn, $userId) {
    $stmt = $conn->prepare("INSERT INTO shifts (user_id, clock_in) VALUES (?, datetime('now'))");

    if ($stmt) {
        $stmt->bindValue(1, $userId, SQLITE3_INTEGER);
        $stmt->execute();
        $stmt->close();

        echo '<script>
                setTimeout(function() {
                    window.location.href = "login.php";
                }, 4000);
              </script>';
    } else {
        echo "Error in preparing the statement: " . $conn->lastErrorMsg();
    }
}

function clockOut($conn, $userId) {
    $stmt = $conn->prepare("UPDATE shifts SET clock_out = datetime('now') WHERE user_id = ? AND clock_out IS NULL");
    $stmt->bindValue(1, $userId, SQLITE3_INTEGER);
    $stmt->execute();
    echo '<script>
                setTimeout(function() {
                    window.location.href = "login.php";
                }, 3000);
            </script>';
}


function isClockedIn($conn, $userId) {
    $stmt = $conn->prepare("SELECT * FROM shifts WHERE user_id = ? AND clock_out IS NULL");

    if ($stmt) {
        $stmt->bindValue(1, $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();

        while ($result->fetchArray()) {
            return true;
        }
        
        return false;
    } else {
        return false;
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="docs.css">
    <title>Employee Time Tracking</title>
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

        form {
            width: 450px;
            height: 510px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .centered {
            text-align: center;
        }

        .btn.btn-success,.btn.btn-danger {
            margin: auto;
            display: block;
        }
        .btn.btn-success {
            margin: auto;
            display: block;
            font-size: 50px;
            padding: 15px 30px;
    }
    </style>
</head>
<body>
    <div class="center-container">
        <form method="post">
            <fieldset>
                <h1 class="centered">Welcome <?php echo htmlspecialchars($username); ?></h1>
                <h6 class="centered">Click CLOCK IN to START your shift</h6>
                <h4 id="current-time" class="centered">Current Time: </h4>
                <h4 id="status">
                    <?php
                    $userId = getUserId($conn, $username);
                    $clockedIn = isClockedIn($conn, $userId);

                    if ($clockedIn) {
                        echo "Click CLOCK OUT to END your shift";
                    } else {
                        echo "Click CLOCK IN to START your shift";
                    }
                    ?>
                </h4>
                <h4 id="clockInTime" class="centered">
                    <?php
                    if ($clockedIn) {
                        $clockInTime = getClockInTime($conn, $userId);
                        echo "Clock In Time: " . $clockInTime;
                    }
                    ?>
                </h4>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="clock_action" value="<?php echo $clockedIn ? 'clock_out' : 'clock_in'; ?>">
                    <br>
                    <button class="btn btn-success" type="submit"><?php echo $clockedIn ? 'Clock Out' : 'Clock In'; ?></button>
                </form><br><br><br><br>
                <div style="display: flex; justify-content: space-between; margin-top: 20px;  margin-left:35px;">
                    <button class="btn btn-primary" type="button" style="width: 150px;" onclick="location.href='employee_profile.php'">My Profile</button>
                    <button class="btn btn-danger" type="button" style="width: 150px;" onclick="location.href='logout.php'">Logout</button>
                </div>
            </fieldset>
        </form>
    </div>
    <script>
        function updateTime() {
            var currentTime = new Date();
            var hours = currentTime.getHours();
            var minutes = currentTime.getMinutes();
            var seconds = currentTime.getSeconds();

            hours = (hours < 10 ? "0" : "") + hours;
            minutes = (minutes < 10 ? "0" : "") + minutes;
            seconds = (seconds < 10 ? "0" : "") + seconds;

            document.getElementById('current-time').innerHTML = "Current Time: " + hours + ":" + minutes + ":" + seconds;

            setTimeout(updateTime, 1000);
        }

        updateTime();
    </script>
</body>
</html>
<?php
$conn->close();
?>