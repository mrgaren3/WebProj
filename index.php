<?php
session_start();
include("database.php");

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}

$user_id = $_SESSION['user_id']; // Current user's ID
$username = $_SESSION['username']; // Current user's username

// Check if a search query is provided
$search_query = isset($_GET['query']) ? trim($_GET['query']) : "";

// Fetch novels from other users (filtering by search query if provided)
if ($search_query !== "") {
    $novels_query = "SELECT novel_id, title, description, photo FROM Novels 
                     WHERE author != ? AND title LIKE ? LIMIT 6";
    $stmt = mysqli_prepare($conn, $novels_query);
    $like_query = '%' . $search_query . '%';
    mysqli_stmt_bind_param($stmt, "ss", $username, $like_query);
} else {
    $novels_query = "SELECT novel_id, title, description, photo FROM Novels 
                     WHERE author != ? LIMIT 6";
    $stmt = mysqli_prepare($conn, $novels_query);
    mysqli_stmt_bind_param($stmt, "s", $username);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$novels = [];
while ($row = mysqli_fetch_assoc($result)) {
    $novels[] = $row;
}

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novel Library</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="navlist">
        <a href="index.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="about.php">About Us</a>
        <div class="search">
            <form method="GET" action="index.php">
                <input type="text" placeholder="Search Novels" name="query" value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
    </div>
    <main>
        <h2>Other Users' Novels</h2>
        <div id="novel-table" class="novel-table">
            <?php if (!empty($novels)): ?>
                <?php foreach ($novels as $novel): ?>
                    <div class="novel-card">
                        <img src="<?php echo htmlspecialchars($novel['photo']); ?>" alt="Novel Cover">
                        <h3><?php echo htmlspecialchars($novel['title']); ?></h3>
                        <p><?php echo htmlspecialchars($novel['description']); ?></p>
                        <a href="chapters.php?novel_id=<?php echo $novel['novel_id']; ?>">View Chapters</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No novels to display<?php echo $search_query ? " matching '$search_query'" : ""; ?>.</p>
            <?php endif; ?>
        </div>
    </main>
    <footer>
        <div>This Website is made by college students as a project for their Web Programming course<br>
            Contact Us<br>
            01228774305</div>
    </footer>
</body>
</html>
