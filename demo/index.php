<?php
// Start session
session_start();

// Include the database connection file
$mysqli = require __DIR__ . "/database.php";

// Check if user is logged in
if (isset($_SESSION["user_id"])) {
    
    $sql = "SELECT * FROM user
            WHERE id = {$_SESSION["user_id"]}";
            
    $result = $mysqli->query($sql);
    
    $user = $result->fetch_assoc();
}


// Check if the user has upvoted a quote
if (isset($_POST["upvote"])) {
    $quote_id = $_POST["upvote"];
    $timestamp = date("Y-m-d H:i:s");
    
    // Check if user has already upvoted in the last 24 hours
    $sql = "SELECT last_upvoted_timestamp FROM user WHERE id = {$_SESSION["user_id"]}";
    $result = $mysqli->query($sql);
    $row = $result->fetch_assoc();
    $last_upvoted_timestamp = strtotime($row["last_upvoted_timestamp"]);
    $current_time = time();
    $time_diff = $current_time - $last_upvoted_timestamp;
    if ($time_diff < 86400) {
        $remaining_time = 86400 - $time_diff;
        $hours = floor($remaining_time / 3600);
        $minutes = floor(($remaining_time % 3600) / 60);
        $upvote_error = "You can only upvote once every 24 hours. Please try again in " . $hours . " hours and " . $minutes . " minutes.";
        
    } else {
        $sql = "UPDATE user
                SET last_upvoted_timestamp = '$timestamp'
                WHERE id = {$_SESSION["user_id"]}";
        $mysqli->query($sql);
        
        // Update the upvotes column in the quote row with the specified quote_id
        $sql = "UPDATE user
                SET upvotes = upvotes + 1
                WHERE id = $quote_id";
        $mysqli->query($sql);

        // Redirect to the same page to prevent multiple upvotes on page refresh
        header("Location: " . $_SERVER["REQUEST_URI"]);
        exit;
    }
}


// Reset button functionality for user with id 1
if (isset($_POST["reset"]) && $_SESSION["user_id"] == 1) {
    $sql = "UPDATE user
            SET upvotes = 0,
                quote = NULL,
                last_upvoted_timestamp = NULL";
    $mysqli->query($sql);
}


// Retrieve all quotes from the database
$sql = "SELECT user.id, user.email, user.quote, user.upvotes FROM user WHERE user.quote IS NOT NULL ORDER BY user.upvotes DESC";
$result = $mysqli->query($sql);

// Check for errors
if (!$result) {
    die("SQL error: " . $mysqli->error);
}
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
        
        <p>Hello <?= htmlspecialchars($user["name"]) ?>, Welcome to daily quote</p>
        
        <p><a href="logout.php">Log out</a></p>

        <form action="main.php">
            <input type="submit" value="Post quote" />
        </form>
        
        <?php if ($user["id"] == 1): ?>
          
            <form action="" method="POST" onsubmit="return conf();">
              <input type="hidden" name="reset" value="true">
              <input type="submit" value="Reset">
           </form>

      
        <?php endif; ?>
        
    <?php else: ?>
        
        <p><a href="login.php">Log in</a> or <a href="signup.html">sign up</a></p>
      
        
    <?php endif; ?>

    <h1>Quotes</h1>
    
    <?php if (isset($upvote_error)): ?>
        <p><?php echo $upvote_error; ?></p>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Quote</th>
                <th>Up Votes</th>
                <?php if (isset($user)): ?>
                    <th>Action</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($row["email"]); ?></td>
        <td><?php echo htmlspecialchars($row["quote"]); ?></td>
        <td><?php echo htmlspecialchars($row["upvotes"]); ?></td>
        <?php if (isset($user) && $user["id"] !== $row["id"]): ?>
            <td>
                <form method="post">
                    <input type="hidden" name="upvote" value="<?php echo $row["id"]; ?>" />
                    <input type="submit" value="Upvote" />
                </form>
            </td>
        <?php else: ?>
            <td> </td>
        <?php endif; ?>
    </tr>
<?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>
