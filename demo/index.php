<?php
// Start session
session_start();

// Inkluder database forbindelsesfilen
$mysqli = require __DIR__ . "/database.php";

// Tjek om brugeren er logget ind
if (isset($_SESSION["user_id"])) {
    
    // Hent brugerens data fra databasen baseret på deres id, som er gemt i sessionen
    $sql = "SELECT * FROM user
            WHERE id = {$_SESSION["user_id"]}";
            
    $result = $mysqli->query($sql);
    
    // Gem brugerens data i en variabel
    $user = $result->fetch_assoc();
}

// Tjek om brugeren har upvoted et citat
if (isset($_POST["upvote"])) {
    $quote_id = $_POST["upvote"];
    $timestamp = date("Y-m-d H:i:s");
    
    // Tjek om brugeren allerede har upvoted indenfor de sidste 24 timer
    $sql = "SELECT last_upvoted_timestamp FROM user WHERE id = {$_SESSION["user_id"]}";
    $result = $mysqli->query($sql);
    $row = $result->fetch_assoc();
    $last_upvoted_timestamp = strtotime($row["last_upvoted_timestamp"]);
    $current_time = time();
    $time_diff = $current_time - $last_upvoted_timestamp;
    if ($time_diff < 86400) {
        // Hvis brugeren har upvoted indenfor de sidste 24 timer, vis en fejlbesked med hvor lang tid der er tilbage
        $remaining_time = 86400 - $time_diff;
        $hours = floor($remaining_time / 3600);
        $minutes = floor(($remaining_time % 3600) / 60);
        $upvote_error = "You can only opvote a quote once every 24 hours. Try again in " . $hours . " hours and " . $minutes . " minutes.";
        
    } else {
        // Hvis brugeren ikke har upvoted indenfor de sidste 24 timer, opdater timestamp for deres seneste upvote i databasen
        $sql = "UPDATE user
                SET last_upvoted_timestamp = '$timestamp'
                WHERE id = {$_SESSION["user_id"]}";
        $mysqli->query($sql);
        
        // Opdater upvotes for det citat, som brugeren har upvoted
        $sql = "UPDATE user
                SET upvotes = upvotes + 1
                WHERE id = $quote_id";
        $mysqli->query($sql);

        // Redirect til den samme side for at undgå flerer upvotes ved opdatering af siden
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit;
    }
}

// Reset knap funktionalitet for bruger med id 1
if (isset($_POST["reset"]) && $_SESSION["user_id"] == 1) {
    // Nulstil alle brugerers oplysninger i databasen
    $sql = "UPDATE user
            SET upvotes = 0,
                quote = NULL,
                last_upvoted_timestamp = NULL";
    $mysqli->query($sql);
}



$sql = "SELECT user.id, user.email, user.quote, user.upvotes FROM user WHERE user.quote IS NOT NULL ORDER BY user.upvotes DESC";
$result = $mysqli->query($sql);
// Her hentes alle citater fra databasen, der kun vises hvis der er et citat i databasen og sorteres efter antallet af upvotes.


if (!$result) {
die("SQL error: " . $mysqli->error);
}
// Hvis der er en fejl i forbindelse med forespørgslen til databasen, vil programmet stoppe med at køre og vise en fejlmeddelelse.

?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <script>
        function conf(){
            return confirm("Are you sure you want to reset?")
        }
    </script>
</head>
<body>
    
<h1>Home</h1>

<?php if (isset($user)): ?>
    <!-- Hvis der er logget en bruger ind, vil denne del af koden blive udført.-->

    <p>Hello <?= htmlspecialchars($user["name"]) ?>, Welcome to daily quote</p>
    <!-- Der vises en hilsen til brugeren med brugerens navn.-->

    <p><a href="logout.php">Log out</a></p>
    <!-- Der vises en knap til at logge ud af systemet.-->

    <form action="main.php">
        <input type="submit" value="Post quote" />
    </form>
    <!-- Der vises en knap til at oprette et nyt citat.-->

    <?php if ($user["id"] == 1): ?>
        <!-- Hvis brugerens id er lig med 1, vil denne del af koden blive udført.-->

        <form action="" method="POST" onsubmit="return conf();">
          <input type="hidden" name="reset" value="true">
          <input type="submit" value="Reset">
    </form>
  
    <?php endif; ?>
    
<?php else: ?>
    <!-- Hvis der ikke er logget en bruger ind, vil denne del af koden blive udført.-->

    <p><a href="login.php">Log in</a> or <a href="signup.html">sign up</a></p>
    <!-- Der vises links til login eller oprettelse af en bruger.-->
  
    
    <?php endif; ?> <!-- slut på if statement, som ikke er inkluderet i koden-->

<h1>Quotes</h1>

<?php if (isset($upvote_error)): ?> <!--hvis brugeren ikke kan opvote vil bekseden blive skrevet -->
    <p><?php echo $upvote_error; ?></p>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Quote</th>
            <th>Up Votes</th>
            <?php if (isset($user)): ?> <!-- hvis variablen $user er sat altså hvis man er logget ind, vises nedenstående kode som er der upvote knappen ligger-->
                <th>Action</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?> <!-- så længe der er rækker i $result, fortsættes nedenstående kode-->
    <tr>
        <!--Opretter en tabel med "email" som nu er username, quote og hvor mange opvotes der er-->
        <td><?php echo htmlspecialchars($row["email"]); ?></td> 
        <td><?php echo htmlspecialchars($row["quote"]); ?></td> 
        <td><?php echo htmlspecialchars($row["upvotes"]); ?></td> 
        <?php if (isset($user) && $user["id"] !== $row["id"]): ?> 
            <td>
                <form method="post"> <!--opret en formular med post metoden-->
                    <input type="hidden" name="upvote" value="<?php echo $row["id"]; ?>" /><!--opret et skjult inputfelt med rækkens id-->
                    <input type="submit" value="Upvote" /><!--opret en knap med teksten "Upvote"-->
                </form>
            </td>
        <?php else: ?> <!--ellers vises en tom celle-->
            <td> </td>
        <?php endif; ?>
    </tr>
    <?php endwhile; ?>
    </tbody>
</table>

</body>
</html> 

