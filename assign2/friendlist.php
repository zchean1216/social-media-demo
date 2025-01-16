<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit();
}

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

// Ensure profile_name is set
if (isset($_SESSION['profile_name'])) {
    $profile_name = $_SESSION['profile_name'];
} else {
    $profile_name = "User";
}

// Fetch logged-in user's friend_id from session
$loggedInUserID = $_SESSION['friend_id'];

// Initialize the status_message variable with a default value
$status_message = "Hey, I am using My Friend System!";  // Default status message

// Handle status message update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['status_message'])) {
    $new_status_message = $_POST['status_message'];

    // Update status message in the database
    $sql_update_status = "UPDATE friends SET status_message = ? WHERE friend_id = ?";
    if ($stmt = $conn->prepare($sql_update_status)) {
        $stmt->bind_param("si", $new_status_message, $loggedInUserID);
        $stmt->execute();
        $stmt->close();
    }

    // Update session status message for immediate display
    $_SESSION['status_message'] = $new_status_message;

    // Refresh the page to show updated status message
    header("Location: friendlist.php");
    exit();
}

// Fetch the current status message of the logged-in user
if (!isset($_SESSION['status_message'])) {
    $sql_status = "SELECT status_message FROM friends WHERE friend_id = ?";
    if ($stmt = $conn->prepare($sql_status)) {
        $stmt->bind_param("i", $loggedInUserID);
        $stmt->execute();
        $stmt->bind_result($fetched_status_message);
        if ($stmt->fetch()) {
            $status_message = $fetched_status_message;  // Use the database value if available
        }
        $stmt->close();

        // Store the status message in session for further requests
        $_SESSION['status_message'] = $status_message;
    }
} else {
    // If the session has the status message, use that
    $status_message = $_SESSION['status_message'];
}

// Fetch total number of friends that the logged-in user has added (one-way relationship)
$totalFriends = 0;

// Get the current page number, default to 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$friends_per_page = 10; // Number of friends per page
$offset = ($page - 1) * $friends_per_page; // Calculate the offset for SQL

// Query to count the total number of friends the user has added (one-way relationship)
$sql = "SELECT COUNT(friend_id2) as totalFriends 
        FROM myfriends 
        WHERE friend_id1 = ?"; // Only count the friends added by the user

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $loggedInUserID);
    $stmt->execute();
    $stmt->bind_result($totalFriends);
    $stmt->fetch();
    $stmt->close();
}

// Calculate total pages based on the total number of friends
$total_pages = ceil($totalFriends / $friends_per_page);

// Fetch the friends that the logged-in user has added with pagination
$friends = [];

$sql_friends = "
    SELECT f.friend_id, f.profile_name 
    FROM friends f
    JOIN myfriends m ON m.friend_id2 = f.friend_id
    WHERE m.friend_id1 = ? 
    LIMIT ? OFFSET ?";  

if ($stmt = $conn->prepare($sql_friends)) {
    $stmt->bind_param("iii", $loggedInUserID, $friends_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $friends[] = $row;
    }
    $stmt->close();
}

// Unfriend action if the user clicks "Unfriend"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['unfriend_id'])) {
    $unfriend_id = $_POST['unfriend_id'];

    // Delete from myfriends table where the logged-in user is friend_id1
    $sql_unfriend = "DELETE FROM myfriends WHERE friend_id1 = ? AND friend_id2 = ?";
    if ($stmt = $conn->prepare($sql_unfriend)) {
        $stmt->bind_param("ii", $loggedInUserID, $unfriend_id);
        $stmt->execute();
        $stmt->close();
    }

    // Refresh the page to show updated friend count and list
    header("Location: friendlist.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Friend System - Friend List</title>
    <link rel="stylesheet" href="style/style.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="friendlist-body">

<div class="friendlist-sidebar">
    <h2 class="friendlist-logo">My Friend System</h2>

    <!-- Status update form -->
    <div class="sidebar-update-form">
        <form method="POST" action="friendlist.php">
            <label for="status_message">Update Status:</label>
            <input type="text" id="status_message" name="status_message" value="<?php echo htmlspecialchars($status_message); ?>" placeholder="Enter your new status">
            <button type="submit">Update</button>
        </form>
    </div>

    <div class="sidebar-button-container">
        <a href="friendadd.php" class="sidebar-button">
            <p class="btnText">Add Friends</p>
            <div class="btnTwo">
                <p class="btnText2">Go!</p>
            </div>
        </a>
        <a href="logout.php" class="sidebar-button">
            <p class="btnText">Log Out</p>
            <div class="btnTwo">
                <p class="btnText2">X</p>
            </div>
        </a>
    </div>
</div>

<!-- Main Content -->
<div class="friendlist-main-content">
    <div class="friendlist-box-container">
        <!-- Animated Greeting -->
        <div class="animated-greeting-container">
            <h2 class="animated-profile-name">
                <span>Welcome,</span>
                <span class="animated-greeting"><?php echo htmlspecialchars($profile_name); ?>!</span>
            </h2>
        </div>

        <!-- Status display -->
        <p><i>Your Status: <?php echo htmlspecialchars($status_message); ?></i></p>

        <!-- Friend List Title -->
        <h2><?php echo htmlspecialchars($profile_name); ?>'s Friend List</h2>
        <p>Total number of friends: <?php echo $totalFriends; ?></p>

        <!-- Pagination controls -->
        <div class="pagination-right">
            <!-- Previous Button (disabled if on the first page) -->
            <?php if ($page > 1) { ?>
                <a href="friendlist.php?page=<?php echo $page - 1; ?>" class="pagination-btn">Previous</a>
            <?php } else { ?>
                <span class="pagination-btn disabled">Previous</span>
            <?php } ?>

            <!-- Display current page number (even if there's only 1 page) -->
            <span class="pagination-btn current">Page <?php echo $page; ?> of <?php echo $total_pages ? $total_pages : 1; ?></span>

            <!-- Next Button (disabled if on the last page) -->
            <?php if ($page < $total_pages) { ?>
                <a href="friendlist.php?page=<?php echo $page + 1; ?>" class="pagination-btn">Next</a>
            <?php } else { ?>
                <span class="pagination-btn disabled">Next</span>
            <?php } ?>
        </div>

        <!-- Friends list container -->
        <div class="friendlist-container">
            <div class="friendlist-friends-list">
                <?php if (!empty($friends)) { ?>
                    <?php foreach ($friends as $friend) { ?>
                        <div class="friendlist-friend-item">
                            <span><?php echo htmlspecialchars($friend['profile_name']); ?></span>
                            <form method="POST" action="friendlist.php">
                                <input type="hidden" name="unfriend_id" value="<?php echo $friend['friend_id']; ?>">
                                <button type="submit" class="friendlist-unfriend-button">Unfriend</button>
                            </form>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p>You have no friends added yet. Add some friends!</p>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
