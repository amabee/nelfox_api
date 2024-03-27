<?php
$server = "localhost";
$username = "root";
$password = "";
$database = "events";


try {
    $conn = new PDO("mysql:host=$server;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo json_decode("Connected");
} catch (PDOException $ex) {
    echo json_encode(array('error' => $ex->getMessage()));
    exit();
}

?>