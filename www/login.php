<?php
require_once('Config.php');
$mysqli = new SQLite3(db_file);

session_start();

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $mysqli->prepare("SELECT * FROM users WHERE username=:username");
    $stmt->bindParam(':username', $username);
    $result = $stmt->execute();

    $user = $result->fetchArray(SQLITE3_ASSOC);

    if ($user) {
        if (password_verify($password, $user["password"])) {
            $_SESSION['user_id'] = $user["user_id"];
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user["role"];

            if ($user["role"] == "admin") {
                header("Location: reports.php");
            } else {
                header("Location: time_clock.php");
            }
            exit();
        } else {
            $error_message = "Invalid username or password";
        }
    } else {
        $error_message = "Invalid username or password";
    }

    $stmt->close();
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

        form {
            width: 600px;
            height: 390px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="center-container">
        <form method="post">
            <fieldset>
                <legend>Identify yourself</legend>
                <?php if ($error_message): ?>
                    <p style="color: red;"><?php echo $error_message; ?></p>
                <?php endif; ?>
                <p>
                    <label for="username" class="form-label">username :</label>
                    <input type="text" name="username" id="username" value="" class="form-control" required>
                </p>
                <p>
                    <label for="password" class="form-label">Password :</label>
                    <input type="password" name="password" id="password" value="" class="form-control" required><br>
                    <input class="btn btn-primary" type="submit" name="submit" value="Log in" style="width: 100%;height: 80PX;">
                </p>

            </fieldset>
        </form>
    </div>
</body>
</html>
