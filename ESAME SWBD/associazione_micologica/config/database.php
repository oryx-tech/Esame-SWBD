<?php

$host = 'localhost';
$db_name = 'associazione_micologica'; 
$username = 'root'; 
$password = '';     

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>