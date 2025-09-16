<?php
include 'db_connect.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$learner_id = $_POST['learner_id'] ?? '';
$route_id = $_POST['route_id'] ?? '';
$parent_id = $_POST['parent_id'] ?? '';

if(empty($learner_id) || empty($route_id) || empty($parent_id)) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

try {
    $conn->begin_transaction();
    
    // Check if learner already has an application for this route
    $check_sql = "SELECT ApplicationID FROM Application WHERE LearnerID = ? AND RouteID = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $learner_id, $route_id);
    $stmt->execute();
    $existing = $stmt->get_result();
    
    if($existing->num_rows > 0) {
        throw new Exception('Application already exists for this learner and route');
    }
    
    // Check bus capacity for the selected route
    $capacity_sql = "SELECT b.Capacity, 
                            COUNT(a.ApplicationID) as CurrentApplications
                     FROM Bus b 
                     LEFT JOIN Application a ON b.RouteID = a.RouteID AND a.Status = 'Approved'
                     WHERE b.RouteID = ?
                     GROUP BY b.BusID, b.Capacity";
    $stmt = $conn->prepare($capacity_sql);
    $stmt->bind_param("i", $route_id);
    $stmt->execute();
    $capacity_info = $stmt->get_result()->fetch_assoc();
    
    if(!$capacity_info) {
        throw new Exception('Invalid route selected');
    }
    
    $has_space = $capacity_info['CurrentApplications'] < $capacity_info['Capacity'];
    
    if($has_space) {
        // Approve application immediately
        $status = 'Approved';
        $insert_sql = "INSERT INTO Application (LearnerID, RouteID, Status, DateApplied) 
                       VALUES (?, ?, ?, CURDATE())";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iis", $learner_id, $route_id, $status);
        $stmt->execute();
        
        $email_subject = "Bus Registration Approved - Strive High Secondary School";
        $email_message = "Dear Parent,\n\nYour child's bus registration application has been approved.\n\n" .
                         "Registration details will be sent separately.\n\n" .
                         "Best regards,\nStrive High Secondary School";
        
        $response_message = 'Application approved successfully! You will receive a confirmation email shortly.';
        
    } else {
        // Add to waiting list
        $waiting_sql = "INSERT INTO WaitingList (LearnerID, RouteID, DateAdded) 
                        VALUES (?, ?, CURDATE())";
        $stmt = $conn->prepare($waiting_sql);
        $stmt->bind_param("ii", $learner_id, $route_id);
        $stmt->execute();
        
        $email_subject = "Bus Registration - Added to Waiting List";
        $email_message = "Dear Parent,\n\nYour child has been added to the waiting list for bus transportation.\n\n" .
                         "You will be notified if a space becomes available.\n\n" .
                         "Best regards,\nStrive High Secondary School";
        
        $response_message = 'Bus is at capacity. Your child has been added to the waiting list.';
    }
    
    // Log email notification
    $email_sql = "INSERT INTO EmailNotification (ParentID, Subject, Message, SentOn) 
                  VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($email_sql);
    $stmt->bind_param("iss", $parent_id, $email_subject, $email_message);
    $stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $response_message,
        'status' => $has_space ? 'approved' : 'waiting_list'
    ]);
    
} catch(Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>