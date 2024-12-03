<?php require_once 'core/dbConfig.php'; ?>
<?php require_once 'core/models.php'; ?>

<?php  
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
}

$photo_id = $_GET['photo_id'];
$album_id = $_GET['album_id'];
$getPhotoByID = getPhotoByID($pdo, $photo_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Photo</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="editPhotoForm" style="display: flex; justify-content: center;">
        <form action="core/handleForms.php" method="POST" enctype="multipart/form-data">
            <p>
                <label for="#">Description</label>
                <input type="hidden" name="photo_id" value="<?php echo $photo_id; ?>">
                <input type="hidden" name="album_id" value="<?php echo $album_id; ?>">
                <input type="text" name="photoDescription" value="<?php echo $getPhotoByID['description']; ?>">
            </p>
            <p>
                <label for="#">Photo Upload</label>
                <input type="file" name="image">
                <input type="submit" name="editPhotoBtn" style="margin-top: 10px;" value="Save Changes">
            </p>
        </form>
    </div>
</body>
</html>