<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Friend System</title>
    <link rel="stylesheet" href="style/style.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600;700&display=swap" rel="stylesheet"> 
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="index.php" class="logo-link">My Friend System</a> 
        <div class="navbar">
            <a href="signup.php">Sign-Up</a>
            <a href="login.php">Log-In</a>
            <a href="about.php">About</a>
        </div>
        <!-- Footer content -->
        <div class="sidebar-footer">
            INF30020 <br>
            Advanced Web Development
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="container homepage-container">
        <h1>Assignment Home Page</h1>
        <p><strong>Name:</strong> Zheng Chean Chia</p>
        <p><strong>Student ID:</strong> 104225166</p>
        <p><strong>Email:</strong> <a href="mailto:104225166@student.swin.edu.au">104225166@student.swin.edu.au</a></p>
        <p><em>I declare that this assignment is my individual work. I have not worked collaboratively nor have I copied from any other student&rsquo;s work or from any other source.</em></p>

        <!-- PHP for table creation and success message -->
        <?php
        // MySQL connection details
        $servername = "feenix-mariadb.swin.edu.au"; 
        $username = "s104225166"; 
        $password = "161201";  
        $dbname = "s104225166_db";  

        // Create connection to MySQL
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Create `friends` table if it doesn't exist
        $createFriendsTable = "CREATE TABLE IF NOT EXISTS friends (
            friend_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            friend_email VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(20) NOT NULL,
            profile_name VARCHAR(30) NOT NULL,
            date_started DATE NOT NULL,
            num_of_friends INT UNSIGNED NOT NULL DEFAULT 0,
            status_message VARCHAR(255) DEFAULT 'Hey, I am using My Friend System!' -- New column for additional features (Update status messages)
        )";

        if ($conn->query($createFriendsTable) === TRUE) {
            echo "<p class='success'>Table 'friends' created successfully.</p>";
        } else {
            echo "<p class='error'>Error creating table 'friends': " . $conn->error . "</p>";
        }

        // Create `myfriends` table if it doesn't exist
        $createMyFriendsTable = "CREATE TABLE IF NOT EXISTS myfriends (
            friend_id1 INT NOT NULL,
            friend_id2 INT NOT NULL,
            PRIMARY KEY (friend_id1, friend_id2),
            FOREIGN KEY (friend_id1) REFERENCES friends(friend_id),
            FOREIGN KEY (friend_id2) REFERENCES friends(friend_id)
        )";

        if ($conn->query($createMyFriendsTable) === TRUE) {
            echo "<p class='success'>Table 'myfriends' created successfully.</p>";
        } else {
            echo "<p class='error'>Error creating table 'myfriends': " . $conn->error . "</p>";
        }

        // Sample data for `friends` table (10 records)
        $friendsData = [
            ['jxjx12@gmail.com', 'password123', 'Jun Xien', '2024-01-15', 5],
            ['kaichip03@hotmail.com', 'qwerty789', 'Kai Chi', '2024-02-10', 10],
            ['jov11@gmail.com', 'abc456', 'Jovin Tan', '2024-03-05', 3],
            ['juljul99@gmail.com', 'mypassword', 'Juliet Lee', '2024-03-15', 7],
            ['tomjerry71@outlook.com', 'pass321', 'Tom Jerry', '2024-04-22', 2],
            ['carchm81@gmail.com', '123abc', 'Carlisle Chan', '2024-05-30', 6],
            ['jayjay@gmail.com', 'jkl456', 'Jayden Chia', '2024-06-10', 8],
            ['jessyc1212@outlook.com', 'xyz123', 'Jessy Chok', '2024-07-12', 9],
            ['giselle33@hotmail.com', '987xyz', 'Giselle Chia', '2024-08-01', 4],
            ['petery0205@hotmail.com', 'xyz789', 'Peter Chia', '2024-09-19', 1]
        ];

        // Insert sample records into `friends` table if they don't already exist
        foreach ($friendsData as $friend) {
            $checkFriendExists = $conn->prepare("SELECT friend_id FROM friends WHERE friend_email = ?");
            $checkFriendExists->bind_param("s", $friend[0]);
            $checkFriendExists->execute();
            $checkFriendExists->store_result();

            if ($checkFriendExists->num_rows === 0) {
                $insertFriend = $conn->prepare("INSERT INTO friends (friend_email, password, profile_name, date_started, num_of_friends) VALUES (?, ?, ?, ?, ?)");
                $insertFriend->bind_param("ssssi", $friend[0], $friend[1], $friend[2], $friend[3], $friend[4]);
                $insertFriend->execute();
            }
            $checkFriendExists->close();
        }

        // Unique sample data for `myfriends` table (20 relationships)
        $myFriendsData = [
            [1, 3], [2, 5], [4, 6], [7, 9], [8, 10], 
            [1, 6], [3, 7], [4, 8], [2, 9], [5, 10], 
            [1, 2], [2, 3], [3, 4], [4, 5], [5, 6], 
            [6, 7], [7, 8], [8, 9], [9, 10], [10, 1] 
        ];

        // Insert sample records into `myfriends` table if they don't already exist
        foreach ($myFriendsData as $friendPair) {
            $checkRelationshipExists = $conn->prepare("SELECT friend_id1 FROM myfriends WHERE friend_id1 = ? AND friend_id2 = ?");
            $checkRelationshipExists->bind_param("ii", $friendPair[0], $friendPair[1]);
            $checkRelationshipExists->execute();
            $checkRelationshipExists->store_result();

            if ($checkRelationshipExists->num_rows === 0) {
                $insertRelationship = $conn->prepare("INSERT INTO myfriends (friend_id1, friend_id2) VALUES (?, ?)");
                $insertRelationship->bind_param("ii", $friendPair[0], $friendPair[1]);
                $insertRelationship->execute();
            }
            $checkRelationshipExists->close();
        }

        echo "<p class='success'>Sample data inserted successfully.</p>";

        // Close the connection
        $conn->close();
        ?>
    </div>

</body>
</html>
