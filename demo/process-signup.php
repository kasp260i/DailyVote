<html>
    <head>
    <title>Password validation fail</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    </head>
    <body>
        <a href="signup.html"> Back </a>
        <br>
        <br>
</body>


</html>


<?php

// Hvis $_POST["name"] er tom, så stoppes scriptet og viser en besked om at navn er påkrævet.
if (empty($_POST["name"])) {
    die("Name is required");
}

// Hvis længden af $_POST["password"] er mindre end 8, så stoppes scriptet og viser en besked om at password skal være mindst 8 tegn.
if (strlen($_POST["password"]) < 8) {
    die("Password must be at least 8 characters");
}

// Hvis der ikke er mindst et bogstav i $_POST["password"], så stoppes scriptet og viser en besked om at passwordet skal indeholde mindst et bogstav.
if ( ! preg_match("/[a-z]/i", $_POST["password"])) {
    die("Password must contain at least one letter");
}

// Hvis der ikke er mindst et tal i $_POST["password"], så stoppes scriptet og viser en besked om at passwordet skal indeholde mindst et tal.
if ( ! preg_match("/[0-9]/", $_POST["password"])) {
    die("Password must contain at least one number");
}

// Hvis værdien af $_POST["password"] ikke er ens med værdien af $_POST["password_confirmation"], så stoppes scriptet og viser en besked om at passwords skal matche.
if ($_POST["password"] !== $_POST["password_confirmation"]) {
    die("Passwords must match");
}

// Hasher passwordet ved hjælp af password_hash funktionen.
$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

// Indlæser databasen ved at inkludere database.php filen.
$mysqli = require __DIR__ . "/database.php";

// SQL-kommandoen til at indsætte brugeroplysninger i databasen.
$sql = "INSERT INTO user (name, email, password_hash)
        VALUES (?, ?, ?)";

// Initialiserer en ny MySQLi-udtalelse.
$stmt = $mysqli->stmt_init();

// Hvis forberedelse af MySQLi-udtalelsen fejler, så stoppes scriptet og viser en fejlmeddelelse.
if ( ! $stmt->prepare($sql)) {
    die("SQL error: " . $mysqli->error);
}

// Binder parametre til MySQLi-udtalelsen.
$stmt->bind_param("sss",
                  $_POST["name"],
                  $_POST["email"],
                  $password_hash);
                  
// Hvis MySQLi-udtalelsen bliver udført korrekt, så redirectes brugeren til en succes-side.
if ($stmt->execute()) {

    header("Location: signup-success.html");
    exit;
    
} else {
    
    // Hvis fejlkoden er 1062, så betyder det at brugernavnet allerede er taget, og scriptet stoppes og viser en besked om at brugernavnet allerede er taget.
    if ($mysqli->errno === 1062) {
        die("Username already taken");
    } else {
        // Ellers vises fejlbeskeden og fejlkoden.
        die($mysqli->error . " " . $mysqli->errno);
    }
}

?>