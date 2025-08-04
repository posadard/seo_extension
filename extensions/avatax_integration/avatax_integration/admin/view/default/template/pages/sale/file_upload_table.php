<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/system/config.php';

// Retrieve and display records if customer_id is provided
$customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;

if ($customer_id > 0) {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOSTNAME . ";dbname=" . DB_DATABASE . ";charset=utf8mb4", DB_USERNAME, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "SELECT file_id, customer_id, path FROM " . DB_PREFIX . "customer_files WHERE customer_id = :customer_id ORDER BY file_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
        $stmt->execute();
        $tableData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($tableData)) {
            echo '<h2 style="text-align:center;">Customer Documents</h2>';
            echo '<div style="display:flex;justify-content:center;"><ul style="list-style-type:none;padding:0;">';
            foreach ($tableData as $row) {
                $fullPath = htmlspecialchars($row['path']);
                $displayPath = str_replace('/resources/archive/', '', $fullPath);
                echo '<li style="margin-bottom: 10px; display: flex; align-items: center;"><a href="' . $fullPath . '" target="_blank" style="background-color: #000; color: #0f0; padding: 5px 10px; text-decoration: none; font-weight: bold; border-radius: 3px; margin-right: 10px; display: flex; align-items: center;">
                <span style="margin-right: 5px;">🔍</span>' . $displayPath . '</a>';
                echo '<form action="extensions/avatax_integration/admin/view/default/template/pages/sale/delete_customer_file.php" method="post" style="display:inline;">
                <input type="hidden" name="file_id" value="' . htmlspecialchars($row['file_id']) . '">
                <input type="hidden" name="path" value="' . $fullPath . '">
                <input type="hidden" name="customer_id" value="' . htmlspecialchars($row['customer_id']) . '">
                <input type="submit" value="Delete" style="background-color: red; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 3px;"></form></li>';
            }
            echo '</ul></div>';
        } else {
            echo 'No data available.<br>';
        }

    } catch (PDOException $e) {
        echo "Error in the connection or the query: " . $e->getMessage() . "<br>";
    }
} else {
    echo 'Invalid customer ID.<br>';
}
?>
