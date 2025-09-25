<?php

$host     = 'localhost';
$user     = 'root';
$password = '';
$dbname   = 'study';  // ✅ your MySQL port

$sql = new mysqli($host, $user, $password, $dbname, $port);

if ($sql->connect_errno) {
    echo "Connection failed (errno: {$sql->connect_errno}): {$sql->connect_error}";
} else {
    //echo "Connected successfully to database '{$dbname}' on host '{$host}' (port {$port}).";
}

?>
