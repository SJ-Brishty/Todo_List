<?php
// Datenbankkonfiguration
$host = "localhost";
$username = "root";   
$password = "";       
$database = "mariadb";

// Verbindung erstellen
$conn = new mysqli($host, $username, $password, $database);

// Verbindung Testen
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo("");
}
?>
