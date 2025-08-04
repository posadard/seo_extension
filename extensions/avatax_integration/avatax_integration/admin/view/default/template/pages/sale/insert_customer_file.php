<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';

// Start the session
session_start();

// Handle data insertion via POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = intval($_POST['customer_id']);
    $preset = $_POST['preset'];
    $other = isset($_POST['other']) ? trim($_POST['other']) : '';
    $date = date('dmY');

    // Determine the file prefix
    if ($preset === 'Other' && !empty($other)) {
        $filePrefix = $other;
    } else {
        $filePrefix = $preset;
    }

    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileExtension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $fileName = $filePrefix . "-" . $customer_id . "-" . $date . "." . $fileExtension;
        $uploadFileDir = $_SERVER['DOCUMENT_ROOT'] . '/resources/archive/';
        $destPath = $uploadFileDir . $fileName;

        // Move the file to the destination directory
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $path = '/resources/archive/' . $fileName;

            if ($customer_id > 0 && !empty($path)) {
                try {
                    $pdo = new PDO("mysql:host=" . DB_HOSTNAME . ";dbname=" . DB_DATABASE, DB_USERNAME, DB_PASSWORD);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $query = "INSERT INTO " . DB_PREFIX . "customer_files (customer_id, path) VALUES (:customer_id, :path)";
                    $stmt = $pdo->prepare($query);

                    $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
                    $stmt->bindParam(':path', $path, PDO::PARAM_STR);

                    if ($stmt->execute()) {
                        // Record inserted successfully
                        $_SESSION['message'] = "Record inserted successfully!";
                    } else {
                        // Failed to insert the record
                        $_SESSION['message'] = "Failed to insert the record.";
                    }
                } catch (PDOException $e) {
                    // Error in the connection or the query
                    $_SESSION['message'] = "Error in the connection or the query: " . $e->getMessage();
                }
            } else {
                // Invalid input data
                $_SESSION['message'] = "Please provide valid input data.";
            }
        } else {
            // Failed to move the uploaded file
            $_SESSION['message'] = "There was an error moving the uploaded file.";
        }
    } else {
        // No file uploaded or upload error
        $_SESSION['message'] = "Please upload a valid file.";
    }

    // Redirect to the previous page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
