<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';

// Start the session
session_start();

// Handle data deletion via POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $file_id = intval($_POST['file_id']);
    $path = $_POST['path'];
    $customer_id = intval($_POST['customer_id']);

    // Debug output to check received values
    error_log("Received data - file_id: $file_id, path: $path, customer_id: $customer_id");

    if ($file_id > 0 && !empty($path) && $customer_id > 0) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOSTNAME . ";dbname=" . DB_DATABASE, DB_USERNAME, DB_PASSWORD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Delete the record from the database
            $query = "DELETE FROM " . DB_PREFIX . "customer_files WHERE file_id = :file_id AND customer_id = :customer_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':file_id', $file_id, PDO::PARAM_INT);
            $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                // Debug output to confirm record deletion
                error_log("Record deleted from database - file_id: $file_id");

                // Delete the file from the server
                $fullPath = $_SERVER['DOCUMENT_ROOT'] . $path;
                if (file_exists($fullPath)) {
                    if (unlink($fullPath)) {
                        error_log("File deleted from server - path: $fullPath");
                    } else {
                        error_log("Failed to delete file from server - path: $fullPath");
                    }
                } else {
                    error_log("File not found on server - path: $fullPath");
                }

                // Record and file deleted successfully
                $_SESSION['message'] = "Record and file deleted successfully!";
            } else {
                // Failed to delete the record
                $_SESSION['message'] = "Failed to delete the record.";
                error_log("Failed to delete record from database - file_id: $file_id");
            }
        } catch (PDOException $e) {
            // Error in the connection or the query
            $_SESSION['message'] = "Error in the connection or the query: " . $e->getMessage();
            error_log("Error in the connection or the query: " . $e->getMessage());
        }
    } else {
        // Invalid input data
        $_SESSION['message'] = "Invalid input data.";
        error_log("Invalid input data - file_id: $file_id, path: $path, customer_id: $customer_id");
    }

    // Redirect to the previous page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
