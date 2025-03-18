<?php
require_once 'db_config.php'; // includes $conn (PDO)
require_once 'auth.php'; // Ensure only admins can access this page

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $report_id = intval($data['report_id']);

    // Delete the report
    $sql = "DELETE FROM reports WHERE id = :report_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':report_id', $report_id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>