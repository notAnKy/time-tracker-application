<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="docs.css">
    <script src="bootstrap.bundle.min.js"></script>
    <style>
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
</body>
</html>
