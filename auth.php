<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'login':
        handleLogin($conn);
        break;
    case 'logout':
        handleLogout();
        break;
    case 'check_session':
        checkSession();
        break;
    case 'register':
        handleRegistration($conn);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

function handleLogin($conn) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if(empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Email and password required']);
        return;
    }
    
    try {
        // Check if parent exists and verify password
        $sql = "SELECT ParentID, FullName, Email, Password FROM Parent WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows === 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
            return;
        }
        
        $parent = $result->fetch_assoc();
        
        // Verify password (in production, use password_hash/password_verify)
        if($password !== $parent['Password']) {
            echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
            return;
        }
        
        // Set session variables
        $_SESSION['parent_id'] = $parent['ParentID'];
        $_SESSION['parent_name'] = $parent['FullName'];
        $_SESSION['parent_email'] = $parent['Email'];
        $_SESSION['logged_in'] = true;
        
        // Get parent's children data
        $children_sql = "SELECT LearnerID, FullName, Grade FROM Learner WHERE ParentID = ?";
        $stmt = $conn->prepare($children_sql);
        $stmt->bind_param("i", $parent['ParentID']);
        $stmt->execute();
        $children_result = $stmt->get_result();
        
        $children = [];
        while($child = $children_result->fetch_assoc()) {
            $children[] = $child;
        }
        
        echo json_encode([
            'success' => true,
            'parent' => [
                'id' => $parent['ParentID'],
                'name' => $parent['FullName'],
                'email' => $parent['Email']
            ],
            'children' => $children
        ]);
        
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Login failed: ' . $e->getMessage()]);
    }
}

function handleLogout() {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}

function checkSession() {
    if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        echo json_encode([
            'logged_in' => true,
            'parent' => [
                'id' => $_SESSION['parent_id'],
                'name' => $_SESSION['parent_name'],
                'email' => $_SESSION['parent_email']
            ]
        ]);
    } else {
        echo json_encode(['logged_in' => false]);
    }
}

function handleRegistration($conn) {
    $fullName = $_POST['fullName'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if(empty($fullName) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'All fields required']);
        return;
    }
    
    try {
        // Check if email already exists
        $check_sql = "SELECT ParentID FROM Parent WHERE Email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if($stmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'error' => 'Email already registered']);
            return;
        }
        
        // Insert new parent (in production, hash the password)
        $insert_sql = "INSERT INTO Parent (FullName, Email, PhoneNumber, Password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ssss", $fullName, $email, $phone, $password);
        $stmt->execute();
        
        $parent_id = $conn->insert_id;
        
        // Set session for new parent
        $_SESSION['parent_id'] = $parent_id;
        $_SESSION['parent_name'] = $fullName;
        $_SESSION['parent_email'] = $email;
        $_SESSION['logged_in'] = true;
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'parent' => [
                'id' => $parent_id,
                'name' => $fullName,
                'email' => $email
            ]
        ]);
        
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Registration failed: ' . $e->getMessage()]);
    }
}

$conn->close();
?>