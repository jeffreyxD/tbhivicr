<?php
$conn = new mysqli("localhost", "root", "", "ticket_system");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>