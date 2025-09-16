<?php
include 'db_connect.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');
$route_id = $_GET['route_id'] ?? '';

try {
    switch($type) {
        case 'daily_waiting':
            $data = getDailyWaitingList($conn, $date);
            break;
        case 'daily_transport':
            $data = getDailyTransportUsers($conn, $date, $route_id);
            break;
        case 'weekly_summary':
            $week_start = $_GET['week_start'] ?? date('Y-m-d', strtotime('monday this week'));
            $data = getWeeklySummary($conn, $week_start);
            break;
        case 'route_capacity':
            $data = getRouteCapacityAnalysis($conn);
            break;
        default:
            throw new Exception('Invalid report type');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function getDailyWaitingList($conn, $date) {
    $sql = "SELECT 
                w.EntryID,
                l.FullName as LearnerName,
                l.Grade,
                r.RouteName,
                w.DateAdded,
                w.EntryID as Position,
                p.Email as ParentContact,
                p.PhoneNumber,
                'Waiting' as Status
            FROM WaitingList w
            JOIN Learner l ON w.LearnerID = l.LearnerID
            JOIN Route r ON w.RouteID = r.RouteID
            JOIN Parent p ON l.ParentID = p.ParentID
            WHERE DATE(w.DateAdded) = ?
            ORDER BY w.DateAdded ASC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $waiting_list = [];
    $position = 1;
    while($row = $result->fetch_assoc()) {
        $row['Position'] = $position++;
        $waiting_list[] = $row;
    }
    
    $summary_sql = "SELECT 
                        COUNT(*) as total_waiting,
                        COUNT(CASE WHEN DATE(DateAdded) = ? THEN 1 END) as new_today
                    FROM WaitingList";
    $stmt = $conn->prepare($summary_sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $summary = $stmt->get_result()->fetch_assoc();
    
    return [
        'waiting_list' => $waiting_list,
        'summary' => $summary
    ];
}

function getDailyTransportUsers($conn, $date, $route_id = '') {
    $sql = "SELECT 
                l.FullName as LearnerName,
                l.Grade,
                r.RouteName,
                b.BusID,
                'Yes' as MorningTransport,
                'Yes' as AfternoonTransport,
                pp.Location as PickupPoint,
                p.Email as ParentContact,
                p.PhoneNumber
            FROM Application a
            JOIN Learner l ON a.LearnerID = l.LearnerID
            JOIN Route r ON a.RouteID = r.RouteID
            JOIN Bus b ON r.RouteID = b.RouteID
            JOIN Parent p ON l.ParentID = p.ParentID
            LEFT JOIN PickUpPoint pp ON r.RouteID = pp.RouteID AND pp.Sequence = 1
            WHERE a.Status = 'Approved'";
    
    $params = [];
    $types = "";
    
    if($route_id) {
        $sql .= " AND r.RouteID = ?";
        $params[] = $route_id;
        $types .= "i";
    }
    
    $sql .= " ORDER BY r.RouteName, l.FullName";
    
    $stmt = $conn->prepare($sql);
    if($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transport_users = [];
    while($row = $result->fetch_assoc()) {
        $transport_users[] = $row;
    }
    
    $summary_sql = "SELECT 
                        COUNT(*) as morning_count,
                        COUNT(*) as afternoon_count
                    FROM Application a
                    JOIN Route r ON a.RouteID = r.RouteID
                    WHERE a.Status = 'Approved'";
    
    if($route_id) {
        $summary_sql .= " AND r.RouteID = ?";
        $stmt = $conn->prepare($summary_sql);
        $stmt->bind_param("i", $route_id);
    } else {
        $stmt = $conn->prepare($summary_sql);
    }
    
    $stmt->execute();
    $summary = $stmt->get_result()->fetch_assoc();
    
    return [
        'transport_users' => $transport_users,
        'summary' => $summary
    ];
}

function getWeeklySummary($conn, $week_start) {
    $week_end = date('Y-m-d', strtotime($week_start . ' +6 days'));
    
    $sql = "SELECT 
                r.RouteID,
                r.RouteName,
                b.BusID,
                b.Capacity,
                COUNT(a.ApplicationID) as ApprovedApplications,
                COUNT(a.ApplicationID) as MorningUsers,
                COUNT(a.ApplicationID) as AfternoonUsers,
                ROUND((COUNT(a.ApplicationID) / b.Capacity) * 100, 1) as Utilization,
                COUNT(a.ApplicationID) * 5 as WeeklyTotal
            FROM Route r
            JOIN Bus b ON r.RouteID = b.RouteID
            LEFT JOIN Application a ON r.RouteID = a.RouteID AND a.Status = 'Approved'
            GROUP BY r.RouteID, r.RouteName, b.BusID, b.Capacity
            ORDER BY r.RouteID";
            
    $result = $conn->query($sql);
    
    $weekly_summary = [];
    while($row = $result->fetch_assoc()) {
        $weekly_summary[] = $row;
    }
    
    return [
        'weekly_summary' => $weekly_summary,
        'week_start' => $week_start,
        'week_end' => $week_end
    ];
}

function getRouteCapacityAnalysis($conn) {
    $sql = "SELECT 
                r.RouteID,
                r.RouteName,
                b.BusID,
                b.Capacity,
                COUNT(a.ApplicationID) as CurrentUsers,
                (b.Capacity - COUNT(a.ApplicationID)) as AvailableSpots,
                COUNT(w.EntryID) as WaitingListCount,
                ROUND((COUNT(a.ApplicationID) / b.Capacity) * 100, 1) as Utilization,
                CASE 
                    WHEN COUNT(a.ApplicationID) / b.Capacity > 0.8 THEN 'Consider additional bus during peak times'
                    WHEN COUNT(a.ApplicationID) / b.Capacity < 0.6 THEN 'Promote route to increase usage'
                    ELSE 'Good capacity available'
                END as Recommendation
            FROM Route r
            JOIN Bus b ON r.RouteID = b.RouteID
            LEFT JOIN Application a ON r.RouteID = a.RouteID AND a.Status = 'Approved'
            LEFT JOIN WaitingList w ON r.RouteID = w.RouteID
            GROUP BY r.RouteID, r.RouteName, b.BusID, b.Capacity
            ORDER BY r.RouteID";
            
    $result = $conn->query($sql);
    $capacity_analysis = [];
    while($row = $result->fetch_assoc()) {
        $capacity_analysis[] = $row;
    }
    
    $overall_sql = "SELECT 
                        SUM(b.Capacity) as total_capacity,
                        COUNT(CASE WHEN a.Status = 'Approved' THEN 1 END) as total_users
                    FROM Route r
                    JOIN Bus b ON r.RouteID = b.RouteID
                    LEFT JOIN Application a ON r.RouteID = a.RouteID";
                    
    $overall_stats = $conn->query($overall_sql)->fetch_assoc();
    $overall_stats['available_spots'] = $overall_stats['total_capacity'] - $overall_stats['total_users'];
    $overall_stats['overall_utilization'] = round(($overall_stats['total_users'] / $overall_stats['total_capacity']) * 100, 1);
    
    return [
        'capacity_analysis' => $capacity_analysis,
        'overall_stats' => $overall_stats
    ];
}

$conn->close();
?>