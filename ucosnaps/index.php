<?php
require_once 'core/dbConfig.php';
require_once 'core/models.php';

if (!isset($_SESSION['username'])) {
    $_SESSION['message'] = "You must be logged in to view your albums.";
    $_SESSION['status'] = "400";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$getAllAlbums = getUserAlbums($pdo, $user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Albums</title>
    <link rel="stylesheet" href="styles/styles.css">
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h1>Your Photo Albums</h1>

        <!-- Form to create a new album -->
        <div class="create-album-form">
            <form action="core/handleForms.php" method="POST">
                <center><label for="album_name">Album Name:</label></center>
                <input type="text" name="album_name" required>
                <center><input type="submit" name="createAlbumBtn" value="Create Album"></center>
            </form>
        </div>

        <!-- Display all albums -->
        <div class="albums-list">
		    <h2>Your Albums</h2>
		    <?php if (!empty($getAllAlbums)): ?>
		        <ul>
		            <?php foreach ($getAllAlbums as $album): ?>
		                <li>
		                    <a href="index.php?album_id=<?php echo $album['album_id']; ?>">
		                        <strong><?php echo htmlspecialchars($album['album_name']); ?></strong>
		                    </a>
		                    <!-- Edit and delete options for album owner -->
		                    <?php if ($_SESSION['username'] == $album['username']): ?>
		                        <a href="editAlbum.php?album_id=<?php echo $album['album_id']; ?>">Edit</a>
		                        <a href="deleteAlbum.php?album_id=<?php echo $album['album_id']; ?>">Delete</a>
		                    <?php endif; ?>
		                </li>
		            <?php endforeach; ?>
		        </ul>
		    <?php else: ?>
		        <p>No albums found. Create one now!</p>
		    <?php endif; ?>
		</div>
        <!-- Display photos for the selected album -->
        <?php
        if (isset($_GET['album_id'])) {
            $album_id = $_GET['album_id'];
            $selectedAlbum = null;

            foreach ($getAllAlbums as $album) {
                if ($album['album_id'] == $album_id) {
                    $selectedAlbum = $album;
                    break;
                }
            }

            if ($selectedAlbum) {
                // Fetch photos only if album exists and belongs to the logged-in user
                $photos = getPhotosByAlbum($pdo, $album_id);
        ?>
                <h2>Photos in Album: <?php echo htmlspecialchars($selectedAlbum['album_name']); ?></h2>

                <!-- Form to upload a photo to the selected album -->
                <div class="upload-photo-form">
                    <form action="core/handleForms.php" method="POST" enctype="multipart/form-data">
                        <label for="photoDescription">Description:</label>
                        <input type="text" name="photoDescription" required>
                        <label for="image"><br>Upload Photo:</label>
                        <input type="file" name="image" required>
                        <input type="hidden" name="album_id" value="<?php echo $album_id; ?>"> <!-- Pass album_id -->
                        <center><input type="submit" name="insertPhotoBtn" value="Upload Photo"></center>
                    </form>
                </div>

                <div class="photos-list">
                    <?php if (!empty($photos)): ?>
                        <?php foreach ($photos as $photo): ?>
                            <div class="photo-container">
                                <img src="images/<?php echo $photo['photo_name']; ?>" alt="Photo">
                                <p><?php echo htmlspecialchars($photo['description']); ?></p>
                                <p><i>Uploaded on: <?php echo $photo['date_added']; ?></i></p>

                                <?php if ($_SESSION['username'] == $photo['username']): ?>
                                    <a href="editPhoto.php?photo_id=<?php echo $photo['photo_id']; ?>&album_id=<?php echo $album_id; ?>">Edit</a>
                                    <a href="deletePhoto.php?photo_id=<?php echo $photo['photo_id']; ?>&album_id=<?php echo $album_id; ?>">Delete</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <center><p>No photos in this album yet. Upload your first photo!</p></center>
                    <?php endif; ?>
                </div>
        <?php
            } else {
                echo "<p>Album not found or you don't have access to this album.</p>";
            }
        }
        ?>
    </div>
</body>
</html>