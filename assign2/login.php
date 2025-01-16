<?php
session_start();

// Database connection details
$servername = "feenix-mariadb.swin.edu.au";
$username = "s104225166";  
$password = "161201";      
$dbname = "s104225166_db"; 

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$email = $password = "";
$email_err = $password_err = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Trim and sanitize inputs
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Validate email
    if (empty($email)) {
        $email_err = "Please enter your email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email.";
    }

    // Validate password
    if (empty($password)) {
        $password_err = "Please enter your password.";
    }

    // If no errors, proceed to check the database
    if (empty($email_err) && empty($password_err)) {
        $sql = "SELECT friend_id, profile_name, password FROM friends WHERE friend_email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                // Email exists, now verify the password and fetch profile_name
                $stmt->bind_result($friend_id, $profile_name, $hashed_password);
                $stmt->fetch();

                if ($password === $hashed_password) { // Assuming passwords are stored in plain text, else use password_verify
                    // Set session variables
                    $_SESSION["loggedin"] = true;
                    $_SESSION["friend_id"] = $friend_id;
                    $_SESSION["email"] = $email;
                    $_SESSION["profile_name"] = $profile_name;  // Set the profile_name in session

                    // Redirect to friendlist.php
                    header("Location: friendlist.php");
                    exit();
                } else {
                    $password_err = "The password you entered is not correct.";
                }
            } else {
                $email_err = "No account found with that email.";
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - My Friend System</title>
    <link rel="stylesheet" href="style/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="login-body">
    <div class="login-container">
        <!-- Logo -->
        <a href="index.php" class="login-logo">My Friend System</a>

        <!-- Login Form Box -->
        <div class="login-box">
            <h1><span id="heading" class="typing-effect">Log In</span></h1>
            <form method="POST" action="login.php">
                <!-- Email Input Field -->
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>"> <!-- Retains the input value -->
                    <span class="error"><?php echo $email_err; ?></span> <!-- Displays the error message -->
                </div>
                <!-- Password Input Field -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password">
                    <span class="error"><?php echo $password_err; ?></span>
                </div>
                <!-- Buttons -->
                <div class="buttons">
                    <button type="submit" class="login">Log In</button>
                    <button type="reset" class="clear">Clear</button>
                </div>
            </form>
            <!-- Links -->
            <p class="existing-user">New user? <a href="signup.php">Register here</a></p>
            <a href="index.php" class="home-link">Home</a>
        </div>
    </div>

    <script>
        // JavaScript for typing effect removal after complete
        window.addEventListener('load', function() {
            const heading = document.querySelector('.typing-effect');
            
            // Set a timeout matching the animation duration 
            setTimeout(() => {
                heading.classList.add('no-caret'); 
            }, 1500); 
        });
    </script>
</body>
</html>
