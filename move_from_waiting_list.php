<?php
include 'db_connect.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$entry_id = $_POST['entry_id'] ?? '';

if(empty($entry_id)) {
    echo json_encode(['success' => false, 'error' => 'Missing entry ID']);
    exit();
}

try {
    $conn->begin_transaction();
    
    // Get waiting list entry details
    $sql = "SELECT w.LearnerID, w.RouteID, l.FullName, p.Email 
            FROM WaitingList w
            JOIN Learner l ON w.LearnerID = l.LearnerID
            JOIN Parent p ON l.ParentID = p.ParentID
            WHERE w.EntryID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $entry_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows === 0) {
        throw new Exception('Waiting list entry not found');
    }
    
    $entry = $result->fetch_assoc();
    
    // Create approved application
    $insert_sql = "INSERT INTO Application (LearnerID, RouteID, Status, DateApplied) 
                   VALUES (?, ?, 'Approved', CURDATE())";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("ii", $entry['LearnerID'], $entry['RouteID']);
    $stmt->execute();
    
    // Remove from waiting list
    $delete_sql = "DELETE FROM WaitingList WHERE EntryID = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $entry_id);
    $stmt->execute();
    
    // Send notification email
    $email_subject = "Bus Registration Approved - Strive High Secondary School";
    $email_message = "Dear Parent,\n\nWe are pleased to inform you that " . $entry['FullName'] . 
                     " has been moved from the waiting list and approved for bus transportation.\n\n" .
                     "Please contact the school office for further details.\n\n" .
                     "Best regards,\nStrive High Secondary School";
    
    $email_sql = "INSERT INTO EmailNotification (ParentID, Subject, Message, SentOn) 
                  VALUES ((SELECT ParentID FROM Learner WHERE LearnerID = ?), ?, ?, NOW())";
    $stmt = $conn->prepare($email_sql);
    $stmt->bind_param("iss", $entry['LearnerID'], $email_subject, $email_message);
    $stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Learner successfully moved from waiting list',
        'learner_name' => $entry['FullName']
    ]);
    
} catch(Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>