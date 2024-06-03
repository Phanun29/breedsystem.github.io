<?php
session_start();

if (isset($_POST['update'])) {
    // Retrieve other form data
    $userId = $_POST['user_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];

    // Handle file upload
    $images = $_FILES['images'];

    // Check if a file is selected
    if ($images['name']) {
        // Check if the uploaded file is an image
        $check = getimagesize($images["tmp_name"]);
        if ($check === false) {
            echo "File is not an image.";
            exit();
        }

        $target_dir = "uploads/";

        // Ensure the uploads directory exists and is writable
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0755, true)) {
                echo "Failed to create directory.";
                exit();
            }
        }

        // Set the target file path
        $target_file = $target_dir . basename($images["name"]);

        // Check file size (optional)
        if ($images["size"] > 5000000) {
            echo "Sorry, your file is too large.";
            exit();
        }

        // Check if file already exists (optional)
        if (file_exists($target_file)) {
            echo "Sorry, file already exists.";
            exit();
        }

        // Move uploaded file to the target directory
        if (!move_uploaded_file($images["tmp_name"], $target_file)) {
            echo "Sorry, there was an error uploading your file.";
            exit();
        }

        // Update the profile picture path only if a new image is uploaded
        $imageUpdateQuery = ", images = '$target_file'";
    } else {
        // If no file is uploaded, set the target file to empty
        $imageUpdateQuery = "";
    }

    include_once "database/db.php";

    // Use prepared statements to prevent SQL injection
    $sql = "UPDATE users SET
            first_name = ?,
            last_name = ?,
            username = ?,
            email = ?,
            phone_number = ?
            $imageUpdateQuery
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $first_name, $last_name, $username, $email, $phone_number, $userId);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating user profile: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
    header("Location: profile.php?id=" . $userId);
    exit();
}
