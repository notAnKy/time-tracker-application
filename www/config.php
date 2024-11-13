<?php
        define('db_file', 'time_clock.db');

        $mysqli = new SQLite3(db_file);
    
        if (!$mysqli) {
            die("Connection failed: " . $mysqli->lastErrorMsg());
        }
?>
