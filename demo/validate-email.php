<?php

// Indlæser databasen ved at inkludere database.php filen.
$mysqli = require __DIR__ . "/database.php";

// SQL-kommandoen for at vælge brugeroplysninger fra databasen, hvor e-mailen matcher $_GET["email"].
$sql = sprintf("SELECT * FROM user
                WHERE email = '%s'",
                $mysqli->real_escape_string($_GET["email"]));

// Udfører SQL-kommandoen og gemmer resultatet i $result.
$result = $mysqli->query($sql);

// $is_available bliver til "true", hvis der ikke er nogen rækker i resultatet.
$is_available = $result->num_rows === 0;

// Sætter content-type headeren til at være JSON.
header("Content-Type: application/json");

// Encoder et JSON objekt med værdien af $is_available, og sender det som svar tilbage til klienten.
echo json_encode(["available" => $is_available]);
?>