<?php
session_start();

// Database connection details
$servername = "feenix-mariadb.swin.edu.au";
$username = "s104225166"; 
$password = "161201";      
$dbname = "s104225166_db"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $profile = $password = $confirm_password = "";
$email_err = $profile_err = $password_err = $confirm_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Trim inputs first
    $email = trim($_POST["email"]);
    $profile = trim($_POST["profile"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    // Validate Email
    if (empty($email)) {
        $email_err = "Please enter an email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    } else {
        // Check if email exists in the 'friends' table
        $sql = "SELECT friend_id FROM friends WHERE friend_email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $email_err = "This email is already registered.";
            }
            $stmt->close();
        }
    }

    // Validate Profile Name
    if (empty($profile)) {
        $profile_err = "Please enter a profile name.";
    } elseif (!preg_match("/^[a-zA-Z]+$/", $profile)) {
        $profile_err = "Profile name can only contain letters.";
    }

    // Validate Password
    if (empty($password)) {
        $password_err = "Please enter a password.";
    } elseif (!preg_match("/^[a-zA-Z0-9]+$/", $password)) {
        $password_err = "Password can only contain letters and numbers.";
    }

    // Confirm Password
    if (empty($confirm_password)) {
        $confirm_password_err = "Please confirm the password.";
    } elseif ($password !== $confirm_password) {
        $confirm_password_err = "Passwords do not match.";
    }

    // If there are no errors, insert the data into the database
    if (empty($email_err) && empty($profile_err) && empty($password_err) && empty($confirm_password_err)) {
        $sql = "INSERT INTO friends (friend_email, password, profile_name, date_started, num_of_friends) VALUES (?, ?, ?, NOW(), 0)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sss", $email, $password, $profile);

            if ($stmt->execute()) {
                // Retrieve the last inserted friend_id for the new user
                $new_friend_id = $stmt->insert_id;

                // Start session and set session variables
                $_SESSION['loggedin'] = true;
                $_SESSION['friend_id'] = $new_friend_id; // Store the newly created friend_id
                $_SESSION['email'] = $email;
                $_SESSION['profile_name'] = $profile; // Store the profile name for future reference

                // Redirect to friendadd.php
                header("Location: friendadd.php");
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
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
    <title>Sign-Up</title>
    <link rel="stylesheet" href="style/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<script>
    window.addEventListener('load', function() {
        const heading = document.getElementById('heading');
        
        // Set a timeout that matches the duration of the typing animation (4s)
        setTimeout(() => {
            heading.classList.add('no-caret'); // Add the class that removes the caret
        }, 4000); 
    });
</script>
<body class="signup-body">
    <!-- Signup Container -->
    <div class="signup-container">
        <!-- Rotated Logo -->
        <a href="index.php" class="rotated-logo">My Friend System</a>

        <!-- Signup Box -->
        <div class="signup-box">
            <h1><span id="heading" class="typing-effect">Create Your Account</span></h1>
            <form method="POST" action="signup.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" placeholder="Enter your email" value="<?php echo $email; ?>">
                    <span class="error"><?php echo $email_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="profile_name">Profile Name</label>
                    <input type="text" id="profile_name" name="profile" placeholder="Enter your profile name" value="<?php echo $profile; ?>">
                    <span class="error"><?php echo $profile_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password">
                    <span class="error"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password">
                    <span class="error"><?php echo $confirm_password_err; ?></span>
                </div>
                <div class="buttons">
                    <button type="submit" class="register">Register</button>
                    <button type="reset" class="clear">Clear</button>
                </div>
            </form>
            <p class="existing-user">Already a user? <a href="login.php">Log in here</a></p>
            <a href="index.php" class="home-link">Home</a>
        </div>
    </div>
</body>
</html>
