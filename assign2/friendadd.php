<?php
session_start();

// Check if the user is logged in; if not, redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}

// Database connection details
$servername = "feenix-mariadb.swin.edu.au";
$username = "s104225166";  
$password = "161201";      
$dbname = "s104225166_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the logged-in user's ID from the session
$loggedInUserID = $_SESSION["friend_id"];

// Retrieve profile name of the logged-in user
$sql = "SELECT profile_name FROM friends WHERE friend_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $loggedInUserID);
    $stmt->execute();
    $stmt->bind_result($profile_name);
    $stmt->fetch();
    $stmt->close();
}

// Calculate the total number of non-friends
$totalNonFriendsSql = "
    SELECT COUNT(*) 
    FROM friends f 
    WHERE f.friend_id != ? 
    AND f.friend_id NOT IN (
        SELECT m.friend_id2 FROM myfriends m WHERE m.friend_id1 = ?
    )";
if ($stmt = $conn->prepare($totalNonFriendsSql)) {
    $stmt->bind_param("ii", $loggedInUserID, $loggedInUserID);
    $stmt->execute();
    $stmt->bind_result($totalNonFriends);
    $stmt->fetch();
    $stmt->close();
}

// Get the current page number, default to 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$friends_per_page = 10; // Number of friends per page
$offset = ($page - 1) * $friends_per_page; // Calculate the offset for SQL

// Retrieve the list of registered users who are not friends with the logged-in user
// Also, calculate the mutual friends count
$nonFriendListSql = "
    SELECT f.friend_id, f.profile_name, 
    (
        SELECT COUNT(*)
        FROM myfriends mf1 
        JOIN myfriends mf2 ON mf1.friend_id2 = mf2.friend_id2 
        WHERE mf1.friend_id1 = ? AND mf2.friend_id1 = f.friend_id
    ) AS mutual_friends
    FROM friends f 
    WHERE f.friend_id != ? 
    AND f.friend_id NOT IN (
        SELECT m.friend_id2 FROM myfriends m WHERE m.friend_id1 = ?
    )
    ORDER BY f.profile_name ASC
    LIMIT ? OFFSET ?";
$nonFriends = [];
if ($stmt = $conn->prepare($nonFriendListSql)) {
    $stmt->bind_param("iiiii", $loggedInUserID, $loggedInUserID, $loggedInUserID, $friends_per_page, $offset);
    $stmt->execute();
    $stmt->bind_result($friend_id, $friend_name, $mutual_friends);

    while ($stmt->fetch()) {
        $nonFriends[] = [
            'friend_id' => $friend_id, 
            'friend_name' => $friend_name, 
            'mutual_friends' => $mutual_friends
        ];
    }
    $stmt->close();
}

// Handle adding friends (one-way relationship)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_friend"])) {
    $newFriendID = intval($_POST["friend_id"]);

    // Check if the relationship already exists (one-way relationship)
    $checkFriendSql = "SELECT * FROM myfriends WHERE friend_id1 = ? AND friend_id2 = ?";
    if ($stmt = $conn->prepare($checkFriendSql)) {
        $stmt->bind_param("ii", $loggedInUserID, $newFriendID);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 0) {
            // Insert into `myfriends` table (one-way relationship)
            $addFriendSql = "INSERT INTO myfriends (friend_id1, friend_id2) VALUES (?, ?)";
            if ($stmt = $conn->prepare($addFriendSql)) {
                $stmt->bind_param("ii", $loggedInUserID, $newFriendID);
                $stmt->execute();
                $stmt->close();
            }

            // Update the number of friends for the logged-in user
            $updateFriendCountSql = "UPDATE friends SET num_of_friends = num_of_friends + 1 WHERE friend_id = ?";
            if ($stmt = $conn->prepare($updateFriendCountSql)) {
                $stmt->bind_param("i", $loggedInUserID);
                $stmt->execute();
                $stmt->close();
            }
        }
        $stmt->close();
    }

    // Redirect to refresh the list of potential friends
    header("Location: friendadd.php?page=" . $page);
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Friends - My Friend System</title>
    <link rel="stylesheet" href="style/style.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="friendlist-body">

<div class="friendlist-sidebar">
    <h2 class="friendlist-logo">My Friend System</h2>

    <div class="sidebar-button-container">
        <a href="friendlist.php" class="sidebar-button">
            <p class="btnText">Friend List</p>
            <div class="btnTwo">
                <p class="btnText2">View</p>
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
    <div class="friendlist-box-container addfriend-box-container">
        <!-- Animated Greeting -->
        <div class="animated-greeting-container">
            <h2 class="animated-profile-name">
                <span>Welcome,</span>
                <span class="animated-greeting"><?php echo htmlspecialchars($profile_name); ?>!</span>
            </h2>
        </div>

        <h2><?php echo htmlspecialchars($profile_name); ?>'s Add Friends Page</h2>
        <p>Total number of non-friends: <?php echo $totalNonFriends; ?></p>

        <!-- Add Friends list container -->
        <div class="addfriend-container">
            <table class="addfriend-friends-list">
                <?php if (!empty($nonFriends)) { ?>
                    <?php foreach ($nonFriends as $nonFriend): ?>
                        <tr class="addfriend-friend-item">
                            <td class="mutual-names"><?php echo htmlspecialchars($nonFriend['friend_name']); ?></td>
                            <td class="mutual-friends">Mutuals: <?php echo $nonFriend['mutual_friends']; ?></td>
                            <td>
                                <!-- Add Friend button -->
                                <form method="POST" action="friendadd.php?page=<?php echo $page; ?>" style="display:inline;">
                                    <input type="hidden" name="friend_id" value="<?php echo $nonFriend['friend_id']; ?>">
                                    <button type="submit" name="add_friend" class="addfriend-button">Add as friend</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php } else { ?>
                    <p>No users to add. You've added everyone!</p>
                <?php } ?>
            </table>
        </div>
        <!-- Pagination controls -->
        <br><div class="pagination-right">
            <!-- Previous Button (disabled if on the first page) -->
            <?php if ($page > 1) { ?>
                <a href="friendadd.php?page=<?php echo $page - 1; ?>" class="pagination-btn">Previous</a>
            <?php } else { ?>
                <span class="pagination-btn disabled">Previous</span>
            <?php } ?>

            <!-- Display current page number (even if there's only 1 page) -->
            <span class="pagination-btn current">Page <?php echo $page; ?> of <?php echo ceil($totalNonFriends / $friends_per_page); ?></span>

            <!-- Next Button (disabled if on the last page) -->
            <?php if ($page < ceil($totalNonFriends / $friends_per_page)) { ?>
                <a href="friendadd.php?page=<?php echo $page + 1; ?>" class="pagination-btn">Next</a>
            <?php } else { ?>
                <span class="pagination-btn disabled">Next</span>
            <?php } ?>
        </div>
    </div>
</div>

</body>
</html>
