<?php
require_once 'db_config.php'; // includes $conn (PDO)
require_once 'auth.php'; // Ensure only admins can access this page

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $report_id = intval($data['report_id']);

    try {
        // Start a transaction
        $conn->beginTransaction();

        // Check if the report is for a question or an answer
        $checkReportSql = "SELECT * FROM reports WHERE id = :report_id";
        $checkReportStmt = $conn->prepare($checkReportSql);
        $checkReportStmt->bindValue(':report_id', $report_id, PDO::PARAM_INT);
        $checkReportStmt->execute();
        $report = $checkReportStmt->fetch(PDO::FETCH_ASSOC);

        if ($report) {
            // Delete the question report
            $deleteReportSql = "DELETE FROM reports WHERE id = :report_id";
            $deleteReportStmt = $conn->prepare($deleteReportSql);
            $deleteReportStmt->bindValue(':report_id', $report_id, PDO::PARAM_INT);
            $deleteReportStmt->execute();
        } else {
            // Delete the answer report
            $deleteReportSql = "DELETE FROM answer_reports WHERE id = :report_id";
            $deleteReportStmt = $conn->prepare($deleteReportSql);
            $deleteReportStmt->bindValue(':report_id', $report_id, PDO::PARAM_INT);
            $deleteReportStmt->execute();
        }

        // Commit the transaction
        $conn->commit();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to ignore report.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>