<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== "admin") {
    header("Location: login.php");
    exit();
}

require_once('Config.php');
$conn = new SQLite3(db_file);

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $role = $_POST["role"];

    // Check if 'user_id' is set for updating an existing user
    if (isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];

        // Prepare an UPDATE statement
        $stmt = $conn->prepare("UPDATE users SET username=?, password=?, role=? WHERE user_id=?");
        $stmt->bindValue(1, $username, SQLITE3_TEXT);
        $stmt->bindValue(2, password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
        $stmt->bindValue(3, $role, SQLITE3_TEXT);
        $stmt->bindValue(4, $user_id, SQLITE3_INTEGER);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: reports.php");
            exit();
        } else {
            echo "Error updating user: " . $conn->lastErrorMsg();
        }
    } else {
        // Insert new user if 'user_id' is not set
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bindValue(1, $username, SQLITE3_TEXT);
        $stmt->bindValue(2, password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
        $stmt->bindValue(3, $role, SQLITE3_TEXT);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: reports.php");
            exit();
        } else {
            echo "Error creating user: " . $conn->lastErrorMsg();
        }
    }
}

// If 'user_id' is set in the URL, fetch user details for updating
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bindValue(1, $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $userDetails = $result->fetchArray(SQLITE3_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Employee Time Tracking</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="docs.css">
    <script src="bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #C7F1F7;
        }
        form {
            width: 600px;
            height: 450px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 1cm;
        }
        #logout {
            margin-left: auto;
        }

        .nav-link {
            margin-right: 15px;
        }

        .nav-logout {
            margin-right: 10px;
        }

        nav {
            background-color: white;
        }
        .center-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
    </style>
    <script>
        function confirmLogout() {
            var result = confirm("Are you sure you want to log out?");
            if (result) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Time Clock</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="nav-link active" aria-current="page" href="reports.php">Reports</a>
            <a class="nav-link active" aria-current="page" href="creat_uesr.php">Create user</a>
            <a class="nav-link nav-logout" id="logout" aria-current="page" href="#" onclick="confirmLogout()">LOG OUT</a>
        </div>
    </nav>
    <div class="center-container">
        <form action="creat_uesr.php" method="post">
            <h2><?php echo isset($userDetails) ? 'Update User' : 'Create User'; ?></h2>

            <?php if (isset($userDetails)): ?>
                <input type="hidden" name="user_id" value="<?php echo $userDetails['user_id']; ?>">
            <?php endif; ?>

            <label for="username" class="form-label">Name:</label>
            <input type="text" class="form-control" name="username" value="<?php echo isset($userDetails) ? $userDetails['username'] : ''; ?>" required><br>

            <label for="password" class="form-label">Password:</label>
            <input type="text" class="form-control" name="password" <?php echo isset($userDetails) ? 'placeholder="Leave blank to keep the current password"' : 'required'; ?>><br>

            <label for="role" class="form-label">Role:</label><br>
            <select name="role" class="form-select">
                <option value="admin" <?php echo isset($userDetails) && $userDetails['role'] === 'admin' ? 'selected' : ''; ?>>admin</option>
                <option value="Employee" <?php echo isset($userDetails) && $userDetails['role'] === 'Employee' ? 'selected' : ''; ?>>Employee</option>
            </select><br>
            
            <button type="submit" class="btn btn-primary" name="<?php echo isset($userDetails) ? 'update' : 'create'; ?>">
                <?php echo isset($userDetails) ? 'Update' : 'Create'; ?>
            </button>
        </form>
    </div>
</body>
</html>
