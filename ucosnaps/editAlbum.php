<?php
require_once 'core/dbConfig.php';
require_once 'core/models.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['album_id'])) {
    $album_id = $_GET['album_id'];

    $albumQuery = getAlbumById($pdo, $album_id);

    if ($albumQuery && $albumQuery['user_id'] == $_SESSION['user_id']) {
        if (isset($_POST['updateAlbumBtn'])) {
            $album_name = $_POST['album_name'];

            updateAlbumName($pdo, $album_id, $album_name);

            header("Location: index.php?album_id=" . $album_id);
            exit;
        }
    } else {
        echo "You do not have permission to edit this album.";
        exit;
    }
} else {
    echo "No album selected.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Album</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h1>Edit Album</h1>
        <form action="editAlbum.php?album_id=<?php echo $album_id; ?>" method="POST">
            <label for="album_name">Album Name:</label>
            <input type="text" name="album_name" value="<?php echo htmlspecialchars($albumQuery['album_name']); ?>" required>
            <input type="submit" name="updateAlbumBtn" value="Update Album">
        </form>
    </div>
</body>
</html>