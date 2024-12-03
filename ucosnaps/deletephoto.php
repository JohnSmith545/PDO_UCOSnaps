<?php
require_once 'core/dbConfig.php';
require_once 'core/models.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['photo_id']) || empty($_GET['photo_id'])) {
    echo "<p>Invalid photo ID.</p>";
    exit;
}

$photo_id = $_GET['photo_id'];
$getPhotoByID = getPhotoByID($pdo, $photo_id);

if (!$getPhotoByID || $getPhotoByID['username'] !== $_SESSION['username']) {
    echo "<p>Photo not found or you do not have permission to delete this photo.</p>";
    exit;
}

$album_id = $getPhotoByID['album_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Photo</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="deletePhotoForm" style="display: flex; justify-content: center;">
        <div class="deleteForm" style="border-style: solid; border-color: red; background-color: #ffcbd1; padding: 10px; width: 50%;">
            <form action="core/handleForms.php" method="POST">
                <p>
                    <label for=""><h2>Are you sure you want to delete this photo below?</h2></label>
                    <input type="hidden" name="photo_id" value="<?php echo $photo_id; ?>">
                    <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
                    <input type="submit" name="deletePhotoBtn" style="margin-top: 10px;" value="Delete">
                </p>
            </form>
        </div>
    </div>
    <div class="images" style="display: flex; justify-content: center; margin-top: 25px;">
        <div class="photoContainer" style="background-color: ghostwhite; border-style: solid; border-color: gray;width: 50%;">

            <img src="images/<?php echo $getPhotoByID['photo_name']; ?>" alt="Photo" style="width: 100%;">

            <div class="photoDescription" style="padding: 25px;">
                <h2><?php echo htmlspecialchars($getPhotoByID['username']); ?></h2>
                <h4><?php echo htmlspecialchars($getPhotoByID['description']); ?></h4>
            </div>
        </div>
    </div>
</body>
</html>