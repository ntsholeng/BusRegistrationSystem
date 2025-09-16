-- Sample data for testing
USE BusRegistrationSystem;

-- Insert sample routes
INSERT INTO Route (RouteName, MorningDeparture, AfternoonDeparture) VALUES
('Rooihuiskraal', '06:20:00', '14:30:00'),
('Wierdapark', '06:25:00', '14:25:00'),
('Centurion', '06:20:00', '14:30:00');

-- Insert buses for each route
INSERT INTO Bus (RouteID, Capacity) VALUES
(1, 30), -- Bus 1 for Rooihuiskraal
(2, 30), -- Bus 2 for Wierdapark  
(3, 30); -- Bus 3 for Centurion

-- Insert pickup points for Route 1 (Rooihuiskraal)
INSERT INTO PickUpPoint (RouteID, Location, Sequence, Time) VALUES
(1, 'Corner of Panorama and Marabou Road', 1, '06:22:00'),
(1, 'Corner of Kolgansstraat and Skimmerstraat', 2, '06:30:00');

-- Insert pickup points for Route 2 (Wierdapark)
INSERT INTO PickUpPoint (RouteID, Location, Sequence, Time) VALUES
(2, 'Corner of Reddersburg Street and Mafeking Drive', 1, '06:25:00'),
(2, 'Corner of Theuns van Niekerkstraat and Roosmarynstraat', 2, '06:35:00');

-- Insert pickup points for Route 3 (Centurion)
INSERT INTO PickUpPoint (RouteID, Location, Sequence, Time) VALUES
(3, 'Corner of Jasper Drive and Tieroog Street', 1, '06:20:00'),
(3, 'Corner of Louise Street and Von Willich Drive', 2, '06:40:00');

-- Insert drop-off points for Route 1 (Rooihuiskraal)
INSERT INTO DropOffPoint (RouteID, Location, Sequence, Time) VALUES
(1, 'Corner of Panorama and Marabou Road', 1, '14:30:00'),
(1, 'Corner of Kolgansstraat and Skimmerstraat', 2, '14:39:00');

-- Insert drop-off points for Route 2 (Wierdapark)
INSERT INTO DropOffPoint (RouteID, Location, Sequence, Time) VALUES
(2, 'Corner of Reddersburg Street and Mafeking Drive', 1, '14:25:00'),
(2, 'Corner of Theuns van Niekerkstraat and Roosmarynstraat', 2, '14:30:00');

-- Insert drop-off points for Route 3 (Centurion)
INSERT INTO DropOffPoint (RouteID, Location, Sequence, Time) VALUES
(3, 'Corner of Jasper Drive and Tieroog Street', 1, '14:30:00'),
(3, 'Corner of Louise Street and Von Willich Drive', 2, '14:40:00');

-- Insert sample parents with passwords
INSERT INTO Parent (FullName, Email, PhoneNumber, Password) VALUES
('Mary Smith', 'mary.smith@email.com', '011-123-4567', 'password123'),
('David Johnson', 'david.johnson@email.com', '011-234-5678', 'password123'),
('Lisa Brown', 'lisa.brown@email.com', '011-345-6789', 'password123'),
('James Wilson', 'james.wilson@email.com', '011-456-7890', 'password123'),
('Anna Lee', 'anna.lee@email.com', '011-567-8901', 'password123'),
('Bob Cooper', 'bob.cooper@email.com', '011-678-9012', 'password123'),
('Sue Anderson', 'sue.anderson@email.com', '011-789-0123', 'password123'),
('Mark Davis', 'mark.davis@email.com', '011-890-1234', 'password123'),
('Jane Miller', 'jane.miller@email.com', '011-901-2345', 'password123'),
('Paul Taylor', 'paul.taylor@email.com', '011-012-3456', 'password123');

-- Insert sample learners
INSERT INTO Learner (FullName, Grade, Email, Registered, ParentID) VALUES
('John Smith', 10, 'john.smith@student.strive.edu', TRUE, 1),
('Sarah Johnson', 9, 'sarah.johnson@student.strive.edu', TRUE, 2),
('Michael Brown', 11, 'michael.brown@student.strive.edu', TRUE, 3),
('Emma Wilson', 10, 'emma.wilson@student.strive.edu', TRUE, 4),
('David Lee', 12, 'david.lee@student.strive.edu', TRUE, 5),
('Alice Cooper', 10, 'alice.cooper@student.strive.edu', TRUE, 6),
('Tom Anderson', 11, 'tom.anderson@student.strive.edu', TRUE, 7),
('Lisa Davis', 9, 'lisa.davis@student.strive.edu', TRUE, 8),
('Chris Miller', 12, 'chris.miller@student.strive.edu', TRUE, 9),
('Amy Taylor', 10, 'amy.taylor@student.strive.edu', TRUE, 10);

-- Insert some approved applications
INSERT INTO Application (LearnerID, RouteID, Status, DateApplied) VALUES
(6, 1, 'Approved', '2024-10-15'), -- Alice Cooper - Route 1
(7, 2, 'Approved', '2024-10-16'), -- Tom Anderson - Route 2
(8, 3, 'Approved', '2024-10-17'), -- Lisa Davis - Route 3
(9, 1, 'Approved', '2024-10-18'), -- Chris Miller - Route 1
(10, 2, 'Approved', '2024-10-19'); -- Amy Taylor - Route 2

-- Insert some waiting list entries
INSERT INTO WaitingList (LearnerID, RouteID, DateAdded) VALUES
(1, 1, '2024-11-08'), -- John Smith waiting for Route 1
(2, 2, '2024-11-07'), -- Sarah Johnson waiting for Route 2
(3, 1, '2024-11-06'), -- Michael Brown waiting for Route 1
(4, 3, '2024-11-05'), -- Emma Wilson waiting for Route 3
(5, 2, '2024-11-04'); -- David Lee waiting for Route 2

-- Insert some sample email notifications
INSERT INTO EmailNotification (ParentID, Subject, Message, SentOn) VALUES
(6, 'Bus Registration Approved', 'Your child has been approved for bus transportation.', '2024-10-15 10:00:00'),
(1, 'Added to Waiting List', 'Your child has been added to the waiting list.', '2024-11-08 14:30:00');

-- Insert sample MIS reports
INSERT INTO MISReport (ReportType, GeneratedOn, Summary) VALUES
('Daily Waiting List', CURDATE(), 'Current waiting list contains 5 learners across 3 routes'),
('Weekly Summary', CURDATE(), 'Total weekly transport usage: 315 learner trips across all routes');

-- Insert a sample backup log
INSERT INTO BackupLog (BackupDate, BackupType, FilePath) VALUES
(CURDATE(), 'Full Database', 'backups/backup_2024-11-11_10-30-00.sql');