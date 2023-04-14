<?php
// Include the database connection file and start the session
require_once "database.php";
session_start();

// Initialize variables
$quote = "";
$message = "";

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the quote from the form
    $quote = $_POST["quote"];
    
    // Sanitize the input data
    $quote = mysqli_real_escape_string($mysqli, $quote);
    
    // Check if the user is logged in
    if (isset($_SESSION["user_id"])) {
        $user_id = $_SESSION["user_id"];

        // Check if the user has already posted a quote
        $sql_check = "SELECT quote FROM user WHERE id = ? AND quote IS NOT NULL";
        $stmt_check = $mysqli->stmt_init();

        if (!$stmt_check->prepare($sql_check)) {
            die("SQL error: " . $mysqli->error);
        }

        $stmt_check->bind_param("i", $user_id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // User has already posted a quote, display error message
            $message = "You have already posted a quote today.";
        } else {
            // User has not posted a quote, add new quote
            $sql_add = "UPDATE user SET quote = ?, upvotes = 0 WHERE id = ? AND quote IS NULL";
            $stmt_add = $mysqli->stmt_init();

            if (!$stmt_add->prepare($sql_add)) {
                die("SQL error: " . $mysqli->error);
            }

            $stmt_add->bind_param("si", $quote, $user_id);

        // Execute the prepared statement
        if (!$stmt_add->execute()) {
            die("Execution failed: " . $stmt_add->error);
        }

        // Close the statement
        $stmt_add->close();

        // Set success message
        $message = "Quote added successfully.";
          } 
          
    } else {
    // User is not logged in, display error message
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