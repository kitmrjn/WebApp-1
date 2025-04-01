<?php
require_once '../includes/db_config.php'; 
require_once '../includes/auth.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $report_id = intval($data['report_id']);

    try {
        $conn->beginTransaction();

        $checkReportSql = "SELECT * FROM reports WHERE id = :report_id";
        $checkReportStmt = $conn->prepare($checkReportSql);
        $checkReportStmt->bindValue(':report_id', $report_id, PDO::PARAM_INT);
        $checkReportStmt->execute();
        $report = $checkReportStmt->fetch(PDO::FETCH_ASSOC);

        if ($report) {
            $deleteReportSql = "DELETE FROM reports WHERE id = :report_id";
            $deleteReportStmt = $conn->prepare($deleteReportSql);
            $deleteReportStmt->bindValue(':report_id', $report_id, PDO::PARAM_INT);
            $deleteReportStmt->execute();
        } else {
            $deleteReportSql = "DELETE FROM answer_reports WHERE id = :report_id";
            $deleteReportStmt = $conn->prepare($deleteReportSql);
            $deleteReportStmt->bindValue(':report_id', $report_id, PDO::PARAM_INT);
            $deleteReportStmt->execute();
        }

        $conn->commit();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to ignore report.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>