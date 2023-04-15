<?php
// Inkluderer databaseforbindelsesfilen og starter sessionen
require_once "database.php";
session_start();

// Initialiserer variabler
$quote = "";
$message = "";

// Checker om formularen er blevet sendt via POST-metoden
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Henter citatet fra formularen
    $quote = $_POST["quote"];
    
    // Sanitizes inputdataene for at undgå SQL-injektion
    $quote = mysqli_real_escape_string($mysqli, $quote);
    
    // Checker om brugeren er logget ind
    if (isset($_SESSION["user_id"])) {
        $user_id = $_SESSION["user_id"];

        // Checker om brugeren allerede har indsendt et citat
        $sql_check = "SELECT quote FROM user WHERE id = ? AND quote IS NOT NULL";
        $stmt_check = $mysqli->stmt_init();

        // Forbereder et SQL-statement og tjekker om det lykkes
        if (!$stmt_check->prepare($sql_check)) {
            die("SQL-error: " . $mysqli->error);
        }

        $stmt_check->bind_param("i", $user_id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // Brugeren har allerede indsendt et citat, vis en fejlbesked
            $message = "You have already posted a quote today";
        } else {
            // Brugeren har ikke indsendt et citat, så vi tilføjer et nyt citat
            $sql_add = "UPDATE user SET quote = ?, upvotes = 0 WHERE id = ? AND quote IS NULL";
            $stmt_add = $mysqli->stmt_init();

            // Forbereder et SQL-statement og tjekker om det lykkes
            if (!$stmt_add->prepare($sql_add)) {
                die("SQL-error: " . $mysqli->error);
            }

            $stmt_add->bind_param("si", $quote, $user_id);

        // Eksekverer det forberedte statement
        if (!$stmt_add->execute()) {
            die("Execution failed: " . $stmt_add->error);
        }

        // Lukker statementet
        $stmt_add->close();

        // Sætter en success-besked
        $message = "Quote posted succesfully";
          } 
          
    } else {
    // Brugeren er ikke logget ind, vis en fejlbesked
    $message = "You need to be logged in to post a quote.";
    }
}


?>

<html>
<head>
    <title>Post Daily Quote</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <script>
        function conf(){
            return confirm("Are you sure you want to post the quote? You can only post once a day")
        }
    </script>
</head>
<body>
    <p><a href="index.php">Back</a></p>
    <h1>Post Daily Quote</h1>
    <form method="post" onsubmit="return conf()">
        <label for="quote">Quote</label>
        <input type="text" name="quote" id="quote" value="<?php echo $quote; ?>">
        <br>
        <button type="submit">Post Quote</button>
    </form>
    <?php echo $message; ?>
</body>
</html>