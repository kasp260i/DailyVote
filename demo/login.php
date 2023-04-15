<?php

// Sætter en variabel til false, som kan bruges til at kontrollere om loginet er gyldigt
$is_invalid = false;

// Tjekker om HTTP-anmodningen er foretaget med POST-metoden
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Opretter en forbindelse til databasen, og kræver database.php-filen for at gøre det
    $mysqli = require __DIR__ . "/database.php";
    
    // Sætter en SQL-forespørgsel, der henter brugeren, som matcher den indtastede email
    $sql = sprintf("SELECT * FROM user
                    WHERE email = '%s'",
                   $mysqli->real_escape_string($_POST["email"]));
    
    // Udfører SQL-forespørgslen og returnerer et resultat
    $result = $mysqli->query($sql);
    
    // Henter brugerens data fra resultatet
    $user = $result->fetch_assoc();
    
    // Hvis en bruger blev fundet i databasen med den indtastede email
    if ($user) {
        
        // Tjekker, om det indtastede kodeord matcher brugerens hashed kodeord
        if (password_verify($_POST["password"], $user["password_hash"])) {
            
            // Starter en ny sessions for brugeren
            session_start();
            
            // Genererer en ny session-id og sletter den gamle
            session_regenerate_id();
            
            // Gemmer brugerens id i sessionsvariablen
            $_SESSION["user_id"] = $user["id"];
            
            // Sender en omdirigering til index.php
            header("Location: index.php");
            exit;
        }
    }
    
    // Hvis login er ugyldigt, sættes variablen til true
    $is_invalid = true;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    
<h1>Login</h1>

<!-- Viser en besked, hvis login er ugyldigt -->
<?php if ($is_invalid): ?>
    <em>Invalid login</em>
<?php endif; ?>

<p><a href="index.php">Back to homepage</a> or <a href="signup.html">sign up</a></p>

<!-- Loginformularen -->
<form method="post">
    <label for="texts">Username</label>
    <input type="text" name="email" id="email">
    <label for="password">Password</label>
    <input type="password" name="password" id="password">
    
    <button>Log in</button>
</form>

    
</body>
</html>








