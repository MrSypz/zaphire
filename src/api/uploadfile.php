<?php

use Zernite\element\User;
use Zernite\util\ResponseHandler;

require_once __DIR__ . '/../../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['profileImage'];
        $user = new User();
        $username = $user->getUsername();
        if (!$username) {
            ResponseHandler::sendError("Username is required.");
            return;
        }

        $old_file_path = $user->getCurrentUser()['ProfilePicture'];

        if ($file['size'] > 10485760) { // 10MB limit
            ResponseHandler::sendError("File is too large. Max size is 10MB.");
            return;
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'jfif'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            ResponseHandler::sendError("Invalid file type. Only JPG, PNG, and JFIF are allowed.");
            return;
        }
        if (!empty('./' .$old_file_path) && file_exists('./' . $old_file_path) && $old_file_path != null) {
            unlink('./' .$old_file_path);
        }

        $uploadDir = '../../resource/profileimg/';
        ensureDirectoryExists($uploadDir);

        $newFileName = 'profile_' . $username . '.' . $fileExtension;
        $filePath = $uploadDir . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            ResponseHandler::sendError("Failed to save the file.");
            return;
        }
        ResponseHandler::sendSuccess("File uploaded successfully.");
        $user->updateUser($user->getUserID(), ['ProfilePicture' => $filePath]);
    } else {
        ResponseHandler::sendError('No file uploaded or upload error occurred.');
    }
}
function ensureDirectoryExists(string $path): void {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}
