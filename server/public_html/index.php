<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    date_default_timezone_set('Europe/Helsinki');

    function dbConnect() {
        $host = 'mysql';
        $user = '';
        $pass = '';
        $conn = new mysqli($host, $user, $pass, 'dbtest');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }

    function getName($id) {
        return $id == 1 ? "Jaakko" : "Erika";
    }

    function getStatus($status) {
        return $status == 1 ? "At Home" : "Outside";
    }

    function getMyDate($date) {
        if(strlen($date)){
            $arr = date_parse($date);
            $newDate = $arr['day'] . "." . $arr['month'] . "." . $arr['year'] . " " . $arr['hour'] . ":" . $arr['minute'];
            return $newDate;
        } else {
            return "-";
        }
    }

    function getDifference($a, $b) {
        $a = new DateTime($a);
        $b = new DateTime($b);
        $interval = date_diff($a, $b);
        return $interval->format('%h'.'h '.'%i' . 'min');
    }

    function listReadings() {
        $conn = dbConnect();
        $result = $conn->query('SELECT * FROM readings');
        if ($result->num_rows > 0) {
            echo "<table cellspacing=0>
                <thead>
                    <tr>
                        <th align='left'>ID</th>
                        <th align='left'>User</th>
                        <th align='left'>Home</th>
                        <th align='left'>Outside</th>
                        <th align='left'>Time Away</th>
                    </tr>
                </thead>
            <tbody>";
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>" . $row['id'] . "</td>
                    <td>" . getName($row['user_id']) . "</td>
                    <td>" . getMyDate($row['home']) . "</td>
                    <td>" . getMyDate($row['outside']) . "</td>";
                echo "<td>";
                    if(strlen($row['home']) && strlen($row['outside'])) {
                        echo getDifference($row['home'], $row['outside']);
                    } 
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "0 results";
        }
        $conn->close();
    }

    function insertNew($userId, $state) {
        $conn = dbConnect();
        $currentDate = date("Y-m-d H:i:s");
        $state 
        ? $conn->query("INSERT INTO readings (user_id, home) VALUES (".$userId.", '".$currentDate."')")
        : $conn->query("INSERT INTO readings (user_id, outside) VALUES (".$userId.", '".$currentDate."')");
        $conn->close();
    }

    function updateOutside($rowId) {
        $conn = dbConnect();
        $currentDate = date("Y-m-d H:i:s");
        $conn->query("UPDATE readings SET outside = '" . $currentDate . "' WHERE id = " . $rowId);
        $conn->close();
    }

    function updateHome($rowId) {
        $conn = dbConnect();
        $currentDate = date("Y-m-d H:i:s");
        $conn->query("UPDATE readings SET home = '" . $currentDate . "' WHERE id = " . $rowId);
        $conn->close();
    }

    function getCurrentState($userId) {
        $conn = dbConnect();
        $result = $conn->query("SELECT state FROM states WHERE user_id = " . $userId);
        $result = $result->fetch_assoc();
        $conn->close();
        return $result['state'];
    }

    function updateState($userId, $newState) {
        $conn = dbConnect();
        $result = $conn->query("UPDATE states SET state = " . $newState . " WHERE user_id = " . $userId);
        $conn->close();
    }
    
    function getLatestRowWithoutOutside($userId) {
        $conn = dbConnect();
        $result = $conn->query('SELECT * FROM readings WHERE user_id = ' . $userId . ' AND outside IS NULL ORDER BY outside DESC LIMIT 1');
        $conn->close();
        return $result->num_rows > 0 ? $result->fetch_assoc() : false;
    }

    function getLatestRowWithoutHome($userId) {
        $conn = dbConnect();
        $result = $conn->query('SELECT * FROM readings WHERE user_id = ' . $userId . ' AND home IS NULL ORDER BY home DESC LIMIT 1');
        $conn->close();
        return $result->num_rows > 0 ? $result->fetch_assoc() : false;
    }

    function statusReadings() {
        $conn = dbConnect();
        $result = $conn->query("SELECT user_id, state FROM states");
        $conn->close();
        echo "<ul style='list-style: none'>";
        while($row = $result->fetch_assoc()) {
            echo "<li><strong>" . getName($row['user_id']) . "</strong>: <span>" . getStatus($row['state']) . "</span></li>";
        }
        echo "</ul>";
    }

    function checkIfBothSet($rowId) {
        $conn = dbConnect();
        $result = $conn->query('SELECT * FROM readings WHERE id = ' . $rowId . ' AND home IS NOT NULL AND outside IS NOT NULL');
        $conn->close();
        return $result->num_rows > 0 ? true : false;
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <title>List of Readings</title>
        <style>
            * {
                padding: 0;
                margin: 0;
                box-sizing: border-box
            }
            ul {
                margin-top: 5px
            }
            main {
                width: 100%;
                max-width: 900px;
                margin-top: 10px;
                margin-left: auto;
                margin-right: auto
            }
            table {
                margin-top: 10px;
                border: 1px solid #eeeeee;
                width: 100%;
                table-layout: fixed;
            }
            table tr td,
            table tr th {
                padding: 8px 10px
            }
            table tbody tr:nth-child(odd) {
                background-color: #ccc
            }
        </style>
    </head>
    <body>
        <main>
            <?php
                if($_GET['action'] == 'list') {
            ?>
            <h2>Records</h2>
            <?php
                    statusReadings();
                    listReadings();
                } else if($_GET['action'] == 'update') {
                    $userId = $_SERVER['HTTP_MYUSER'];
                    $routerState = $_SERVER['HTTP_MYSTATE'] == "in" ? 1 : 0;
                    $currentState = getCurrentState($userId);
                    if($currentState != $routerState) {
                        $home = getLatestRowWithoutHome($userId);
                        $outside = getLatestRowWithoutOutside($userId);
                        if(!is_array($home) && !is_array($outside)) {
                            insertNew($userId, $routerState);
                        } else if(is_array($home) && !is_array($outside)) {
                            updateHome($home['id']);
                            if(checkIfBothSet($home['id'])) {
                                insertNew($userId, 1);
                            }
                        } else if(is_array($outside) && !is_array($home)) {
                            updateOutside($outside['id']);
                            if(checkIfBothSet($outside['id'])) {
                                insertNew($userId, 0);
                            }
                        } else {
                            return;
                        }
                        updateState($userId, $routerState);
                    } 
                } else {
                    die('You are not using this correctly!');
                }
            ?>
        </main>
    </body>
</html>
