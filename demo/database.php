<?php

// Opsæt forbindelsesdetaljer
$host = "localhost";
$dbname = "login_db";
$username = "root";
$password = "";

// Opretter mysqli objekt og forbind til databasen
$mysqli = new mysqli(hostname: $host, // værtsnavn
                     username: $username, // brugernavn
                     password: $password, // adgangskode
                     database: $dbname); // databasenavn

// Tjek for forbindelsesfejl
if ($mysqli->connect_errno) { // tjekker for fejl
    die("Forbindelsesfejl: " . $mysqli->connect_error); // udskriv fejlmeddelelse og afslut
}

// Returner mysqli-objektet
return $mysqli;
