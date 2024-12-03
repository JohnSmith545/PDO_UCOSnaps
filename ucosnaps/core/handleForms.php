<?php  
require_once 'dbConfig.php';
require_once 'models.php';

if (isset($_POST['insertNewUserBtn'])) {
    $username = trim($_POST['username']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!empty($username) && !empty($first_name) && !empty($last_name) && !empty($password) && !empty($confirm_password)) {

        if ($password == $confirm_password) {
            $insertQuery = insertNewUser($pdo, $username, $first_name, $last_name, password_hash($password, PASSWORD_DEFAULT));
            $_SESSION['message'] = $insertQuery['message'];

            if ($insertQuery['status'] == '200') {
                $_SESSION['status'] = $insertQuery['status'];
                header("Location: ../login.php");
            } else {
                $_SESSION['status'] = $insertQuery['status'];
                header("Location: ../register.php");
            }

        } else {
            $_SESSION['message'] = "Please make sure both passwords are equal";
            $_SESSION['status'] = '400';
            header("Location: ../register.php");
        }

    } else {
        $_SESSION['message'] = "Please make sure there are no empty input fields";
        $_SESSION['status'] = '400';
        header("Location: ../register.php");
    }
}

if (isset($_POST['loginUserBtn'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $loginQuery = checkIfUserExists($pdo, $username);
        $userIDFromDB = $loginQuery['userInfoArray']['user_id'];
        $usernameFromDB = $loginQuery['userInfoArray']['username'];
        $passwordFromDB = $loginQuery['userInfoArray']['password'];

        if (password_verify($password, $passwordFromDB)) {
            $_SESSION['user_id'] = $userIDFromDB;
            $_SESSION['username'] = $usernameFromDB;
            header("Location: ../index.php");
        } else {
            $_SESSION['message'] = "Username/password invalid";
            $_SESSION['status'] = "400";
            header("Location: ../login.php");
        }
    } else {
        $_SESSION['message'] = "Please make sure there are no empty input fields";
        $_SESSION['status'] = '400';
        header("Location: ../register.php");
    }
}

if (isset($_GET['logoutUserBtn'])) {
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    header("Location: ../login.php");
}

if (isset($_POST['insertPhotoBtn'])) {
    // Get the album ID from the POST data
    $album_id = $_POST['album_id'];
    $photoDescription = $_POST['photoDescription'];

    // Handle file upload logic and save the photo to the album
    $photoName = $_FILES['image']['name'];
    $targetDir = "../images/";
    $targetFile = $targetDir . basename($photoName);

    // Move the uploaded photo to the target directory
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        // Insert photo into the database
        $stmt = $pdo->prepare("INSERT INTO photos (photo_name, username, description, album_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$photoName, $_SESSION['username'], $photoDescription, $album_id]);

        // Redirect to the album page with success message
        header("Location: ../index.php?album_id=$album_id");
        exit;
    } else {
        // Handle error if file upload failed
        $_SESSION['message'] = "Photo upload failed.";
        header("Location: ../index.php?album_id=$album_id");
        exit;
    }
}


if (isset($_POST['createAlbumBtn'])) {
    // Ensure user is logged in
    if (isset($_SESSION['user_id'])) {
        // Get the album name from the form input
        $album_name = trim($_POST['album_name']);

        // Check if album name is provided
        if (!empty($album_name)) {
            // Insert the album into the database
            $user_id = $_SESSION['user_id']; // Get the logged-in user's ID
            $albumCreationResult = createAlbum($pdo, $user_id, $album_name);

            // Check if album creation was successful
            if ($albumCreationResult) {
                $_SESSION['message'] = "Album created successfully!";
                $_SESSION['status'] = '200';
                header("Location: ../index.php"); // Redirect back to index page
            } else {
                $_SESSION['message'] = "Error creating album.";
                $_SESSION['status'] = '400';
                header("Location: ../index.php"); // Redirect back with error
            }
        } else {
            $_SESSION['message'] = "Please provide an album name.";
            $_SESSION['status'] = '400';
            header("Location: ../index.php"); // Redirect back with error
        }
    } else {
        $_SESSION['message'] = "You must be logged in to create an album.";
        $_SESSION['status'] = '400';
        header("Location: ../login.php"); // Redirect to login page if not logged in
    }
}

if (isset($_POST['updateAlbumBtn'])) {
    $albumId = $_POST['album_id'];
    $newAlbumName = trim($_POST['album_name']);
    if (!empty($newAlbumName)) {
        // Update album name
        $updateAlbum = updateAlbumName($pdo, $albumId, $newAlbumName);
        if ($updateAlbum) {
            $_SESSION['message'] = "Album name updated successfully!";
            $_SESSION['status'] = "200";
            header("Location: ../index.php");
        } else {
            $_SESSION['message'] = "Failed to update album name.";
            $_SESSION['status'] = "400";
            header("Location: ../index.php");
        }
    } else {
        $_SESSION['message'] = "Album name cannot be empty.";
        $_SESSION['status'] = "400";
        header("Location: ../index.php");
    }
}

if (isset($_POST['deleteAlbumBtn'])) {
    $albumId = $_POST['album_id'];
    $deleteAlbum = deleteAlbum($pdo, $albumId);
    if ($deleteAlbum) {
        $_SESSION['message'] = "Album deleted successfully!";
        $_SESSION['status'] = "200";
        header("Location: ../index.php");
    } else {
        $_SESSION['message'] = "Failed to delete album.";
        $_SESSION['status'] = "400";
        header("Location: ../index.php");
    }
}

if (isset($_POST['deletePhotoBtn'])) {
    // Get the photo ID, photo name, and album ID from the POST request
    $photo_id = $_POST['photo_id'];
    $photo_name = $_POST['photo_name'];
    $album_id = $_POST['album_id']; // Fetch album_id from POST instead of GET

    // Delete the photo from the database
    $sql = "DELETE FROM photos WHERE photo_id = :photo_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':photo_id', $photo_id, PDO::PARAM_INT);
    $stmt->execute();

    // Optionally delete the photo file from the server
    $imagePath = "../images/" . $photo_name; // Ensure path matches the stored location
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }

    // Redirect back to the album page
    header("Location: ../index.php?album_id=" . $album_id);
    exit;
}

if (isset($_POST['editPhotoBtn'])) {
    // Get the photo details
    $photo_id = $_POST['photo_id'];
    $album_id = $_POST['album_id']; // Retrieve album_id from the form
    $newDescription = $_POST['photoDescription'];
    $newImage = $_FILES['image'];

    // Update the description in the database
    $sql = "UPDATE photos SET description = :description WHERE photo_id = :photo_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':description', $newDescription, PDO::PARAM_STR);
    $stmt->bindParam(':photo_id', $photo_id, PDO::PARAM_INT);
    $stmt->execute();

    // Handle optional new image upload
    if (!empty($newImage['name'])) {
        $imagePath = "../images/" . basename($newImage['name']);
        if (move_uploaded_file($newImage['tmp_name'], $imagePath)) {
            // Update the photo name in the database
            $updateImageSQL = "UPDATE photos SET photo_name = :photo_name WHERE photo_id = :photo_id";
            $updateStmt = $pdo->prepare($updateImageSQL);
            $updateStmt->bindParam(':photo_name', $newImage['name'], PDO::PARAM_STR);
            $updateStmt->bindParam(':photo_id', $photo_id, PDO::PARAM_INT);
            $updateStmt->execute();
        }
    }

    // Redirect back to the album page
    header("Location: ../index.php?album_id=" . $album_id);
    exit;
}