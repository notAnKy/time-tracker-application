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

$user_id = '';
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
} else {
    echo "Invalid user_id";
    exit;
}

$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $sqlite->prepare($query);
$stmt->bindParam(1, $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$userData = $result->fetchArray(SQLITE3_ASSOC);

$stmt->close();
$username = $userData['username']; 
$role = $userData['role'];

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

function get_dates_between($startDate, $endDate) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, $end->modify('+1 day'));

    $dates = [];
    foreach ($period as $date) {
        $dates[] = $date->format('Y-m-d');
    }

    return $dates;
}

$query_shifts = "SELECT * FROM shifts WHERE user_id = ? AND clock_in BETWEEN ? AND ?";
$stmt_shifts = $sqlite->prepare($query_shifts);
$stmt_shifts->bindParam(1, $user_id, SQLITE3_INTEGER);
$stmt_shifts->bindParam(2, $start_date, SQLITE3_TEXT);
$stmt_shifts->bindParam(3, $end_date, SQLITE3_TEXT);
$stmt_shifts->execute();
$result_shifts = $stmt_shifts->execute();


$total_hours = 0;
$shiftsData = array();

while ($row_shift = $result_shifts->fetchArray(SQLITE3_ASSOC)) {
    $clock_in_time = strtotime($row_shift['clock_in']);
    $clock_out_time = strtotime($row_shift['clock_out']);
    $shift_total_hours = ($clock_out_time - $clock_in_time) / 3600;

    $shiftsData[] = [
        'day' => date('Y-m-d', $clock_in_time),
        'hours' => $shift_total_hours,
    ];

    $total_hours += $shift_total_hours;
}

$stmt_shifts->close();

$query_attendance = "SELECT DISTINCT DATE(clock_in) AS present_date
                     FROM shifts
                     WHERE user_id = ? AND clock_in BETWEEN ? AND ?";
$stmt_attendance = $sqlite->prepare($query_attendance);
$stmt_attendance->bindParam(1, $user_id, SQLITE3_INTEGER);
$stmt_attendance->bindParam(2, $start_date, SQLITE3_TEXT);
$stmt_attendance->bindParam(3, $end_date, SQLITE3_TEXT);
$stmt_attendance->execute();
$result_attendance = $stmt_attendance->execute();

$attendanceData = [];
while ($row_attendance = $result_attendance->fetchArray(SQLITE3_ASSOC)) {
    $attendanceData[] = $row_attendance['present_date'];
}
$stmt_attendance->close();

function get_missing_dates($user_id, $start_date, $end_date) {
    require_once('Config.php');
    $sqlite = new SQLite3(db_file);

    $dateCondition = "AND DATE(clock_in) BETWEEN ? AND ?";

    $query = "SELECT DISTINCT DATE(clock_in) AS present_date
              FROM shifts
              WHERE user_id = ?
                $dateCondition";

    $stmt = $sqlite->prepare($query);
    $stmt->bindValue(1, $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(2, $start_date, SQLITE3_TEXT);
    $stmt->bindValue(3, $end_date, SQLITE3_TEXT);
    $stmt->execute();
    $stmt->bind_result($presentDate);

    $presentDates = [];
    while ($stmt->fetch()) {
        $presentDates[] = $presentDate;
    }

    $stmt->close();
    $mysqli->close();

    $allDates = get_dates_between($start_date, $end_date);

    $missingDates = array_diff($allDates, $presentDates);

    return $missingDates;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="docs.css">
    <script src="bootstrap.bundle.min.js"></script>
    <script src="html2pdf.bundle.js"></script>
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

        .info-container {
            width: 90%;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            margin: auto;
            margin-top: 50px;
        }

        .centered {
            text-align: center;
        }

        .user-info {
            margin-bottom: 10px;
        }
        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="date"] {
            width:25%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        button:hover {
            background-color: #45a049;
        }
        #h{
            height: 400px;
        }
    </style>
</head>
<body>
<br><br>
    <div class="info-container">
        <form action="reports_emp.php" method="get">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo $start_date ? $start_date : date('Y-m-01'); ?>" required>

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo $end_date ? $end_date : date('Y-m-t'); ?>" required>

            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>"><br>

            <button id="generateReportButton" type="submit">Generate Report</button>
            <button type="button"  onclick="generatePDF()" >Generate Report PDF</button>
        </form>

    </div>
    <div id="report-container">
        <div class="info-container" id="d1">
            <h2>User Information</h2>
            <div class="user-info">User ID: <?php echo $user_id; ?></div>
            <div class="user-info">Username: <?php echo $username; ?></div>
            <div class="user-info">Role: <?php echo $role; ?></div>
            <p><?php echo "$start_date to $end_date"; ?></p>
        </div>
        <?php
            require_once('Config.php');
            $sqlite = new SQLite3(db_file);
            $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
            $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

            $query_shifts = "SELECT * FROM shifts WHERE user_id = ? AND clock_in BETWEEN ? AND ?";
            $stmt_shifts = $sqlite->prepare($query_shifts);
            $stmt_shifts->bindParam(1, $user_id, SQLITE3_INTEGER);
            $stmt_shifts->bindParam(2, $start_date, SQLITE3_TEXT);
            $stmt_shifts->bindParam(3, $end_date, SQLITE3_TEXT);
            $stmt_shifts->execute();
            $result_shifts = $stmt_shifts->execute();


            echo '<div class="info-container tabel" id="d3">';
            echo '<h2>Shifts Table</h2>';
            echo "<table border='   '>";
            echo "<tr><th>Clock In</th><th>Clock Out</th><th>Total Hours</th></tr>";

            $total_hours = 0;
            $current_date = null;
            $shiftsData = array();

            while ($row_shift = $result_shifts->fetchArray(SQLITE3_ASSOC)) {

                echo "<tr>";
                echo "<td>" . $row_shift['clock_in'] . "</td>";
                echo "<td>" . $row_shift['clock_out'] . "</td>";

                $clock_in_time = strtotime($row_shift['clock_in']);
                $clock_out_time = strtotime($row_shift['clock_out']);
                $shift_total_hours = ($clock_out_time - $clock_in_time) / 3600;
                
                $formatted_total_hours = sprintf("%02d:%02d", floor($shift_total_hours), ($shift_total_hours % 1) * 60);
                
                echo "<td>$formatted_total_hours</td>";
                echo "</tr>";

                $total_hours += $shift_total_hours;
            }

            echo "<tr>";
            echo "<td colspan='2'><strong>Total Hours:</strong></td>";

            $formatted_total_hours = sprintf("%02d:%02d", floor($total_hours), ($total_hours % 1) * 60);

            echo "<td><strong>$formatted_total_hours</strong></td>";
            echo "</tr>";

            echo "</table>";
            echo "</div>";

            $stmt_shifts->close();

            $sqlite->close();
            
        ?>

    <div class="info-container" id="d4">
        <h2>Calendar</h2>
        <?php
        function drawCalendar($attendanceData, $start_date, $end_date)
        {
            $startMonth = date('m', strtotime($start_date));
            $startYear = date('Y', strtotime($start_date));
            $endMonth = date('m', strtotime($end_date));
            $endYear = date('Y', strtotime($end_date));
        
            while (($startYear < $endYear) || ($startYear == $endYear && $startMonth <= $endMonth)) {
                $firstDay = date("N", mktime(0, 0, 0, $startMonth, 1, $startYear));
                $lastDay = date("t", mktime(0, 0, 0, $startMonth, 1, $startYear));
                echo "<table class='table table-bordered'>";
                echo "<tr><th colspan='7' class='text-center'>" . date('F Y', mktime(0, 0, 0, $startMonth, 1, $startYear)) . "</th></tr>";
                echo "<tr class='text-center'><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th></tr>";
                $dayCounter = 1;
                echo "<tr>";
        
                for ($i = 1; $i < $firstDay; $i++) {
                    echo "<td></td>";
                }
                $currentDate = date('Y-m-d');
                while ($dayCounter <= $lastDay) {
                    for ($i = $firstDay; $i <= 7 && $dayCounter <= $lastDay; $i++) {
                        $date = "$startYear-$startMonth-" . str_pad($dayCounter, 2, '0', STR_PAD_LEFT);
        
                        if ($date == $currentDate) {
                            echo '<td style="background-color: rgba(47, 0, 255, 0.2); color: black;">' . $dayCounter . '</td>';
                        } elseif (in_array($date, $attendanceData)) {
                            echo '<td style="background-color: rgba(136, 253, 128, 0.8); color: black;">' . $dayCounter . '</td>';
                        } elseif ($date > $currentDate) {
                            echo '<td style="background-color: #b7b8b4; color: black;">' . $dayCounter . '</td>';
                        } else {
                            echo '<td style="background-color: rgba(255, 0, 0, 0.2);; color: black;">' . $dayCounter . '</td>';
                        }
                        
                        $dayCounter++;
                    }
        
                    if ($dayCounter <= $lastDay) {
                        echo "</tr><tr>";
                    }

                    $firstDay = 1; 
                }
                while ($i <= 7) {
                    echo "<td></td>";
                    $i++;
                }
                echo "</tr>";
                echo "</table>";
                $startMonth++;
                if ($startMonth > 12) {
                    $startMonth = 1;
                    $startYear++;
                }
            }
        }
        drawCalendar($attendanceData, $start_date, $end_date);
        ?>
    </div>

    <p></p>
    <script>
        function generatePDF() {
            const container = document.getElementById('report-container');

            html2pdf(container, {
                margin: 10,
                filename: 'report.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                jsPDF: { unit: 'mm', format: 'a3', orientation: 'portrait' } ,
                scale: 100
            });
        }
    </script>
    <br><br><br><br><br><br><br><br><br><br><br><br>
</body>
</html>
<script>
