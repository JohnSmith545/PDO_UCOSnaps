<?php
require_once 'core/dbConfig.php';
require_once 'core/models.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['album_id'])) {
    $album_id = $_GET['album_id'];

    $albumQuery = getAlbumById($pdo, $album_id);

    // Check if the album exists and belongs to the logged-in user
    if ($albumQuery && $albumQuery['user_id'] == $_SESSION['user_id']) {
        if (isset($_POST['deleteAlbumBtn'])) {
            deletePhotosByAlbum($pdo, $album_id);

            deleteAlbum($pdo, $album_id);

            header("Location: index.php");
            exit;
        }
    } else {
        echo "You do not have permission to delete this album.";
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
    <title>Delete Album</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h1>Are you sure you want to delete this album?</h1>
        <form action="deleteAlbum.php?album_id=<?php echo $album_id; ?>" method="POST">
            <input type="submit" name="deleteAlbumBtn" value="Yes, Delete Album">
            <a href="index.php?album_id=<?php echo $album_id; ?>">Cancel</a>
        </form>
    </div>
</body>
</html>