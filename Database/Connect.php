<?php
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "moisturegames";

    $connection = new mysqli($servername, $username, $password, $database);
    
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }
?>