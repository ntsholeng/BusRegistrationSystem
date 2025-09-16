<?php
session_start();
include 'db_connect.php';

// Simple admin authentication - in production, implement proper admin login
$admin_logged_in = true; // Set to false to require login

if(!$admin_logged_in) {
    // Redirect to admin login page
    header('Location: admin_login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bus Registration System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .admin-header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .admin-nav {
            background: #34495e;
            padding: 15px;
        }
        .nav-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .nav-btn:hover {
            background: #2980b9;
        }
        .admin-content {
            padding: 20px;
        }
        .action-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .waiting-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #ddd;
            margin: 10px 0;
            border-radius: 5px;
            background: #f8f9fa;
        }
        .approve-btn {
            background: #27ae60;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
        }
        .approve-btn:hover {
            background: #229954;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Administrator Dashboard</h1>
            <p>Strive High Secondary School - Bus Registration Management</p>
        </div>
        
        <div class="admin-nav">
            <button class="nav-btn" onclick="showSection('waiting-management')">Manage Waiting List</button>
            <a href="mis_dashboard.html" class="nav-btn">View MIS Reports</a>
            <button class="nav-btn" onclick="showSection('applications')">Review Applications</button>
            <button class="nav-btn" onclick="showSection('backup')">Backup & Recovery</button>
        </div>
        
        <div class="admin-content">
            <div id="waiting-management" class="action-section">
                <h3>Waiting List Management</h3>
                <p>Move learners from waiting list to approved registration when space becomes available.</p>
                
                <div id="waiting-list-container">
                    <!-- Waiting list items will be loaded here via AJAX -->
                </div>
                
                <button onclick="loadWaitingList()" class="nav-btn">Refresh Waiting List</button>
            </div>
            
            <div id="applications" class="action-section" style="display:none;">
                <h3>Application Management</h3>
                <p>Review and approve new bus registration applications.</p>
                
                <div id="applications-container">
                    <!-- Applications will be loaded here via AJAX -->
                </div>
            </div>
            
            <div id="backup" class="action-section" style="display:none;">
                <h3>Backup & Recovery</h3>
                <p>Perform database backup and recovery operations.</p>
                
                <button onclick="performBackup()" class="nav-btn">Create Database Backup</button>
                <button onclick="showBackupHistory()" class="nav-btn">View Backup History</button>
                
                <div id="backup-status"></div>
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            document.querySelectorAll('.action-section').forEach(section => {
                section.style.display = 'none';
            });
            
            document.getElementById(sectionId).style.display = 'block';
            
            if(sectionId === 'waiting-management') {
                loadWaitingList();
            }
        }
        
        function loadWaitingList() {
            fetch('mis_reports_handler.php?type=daily_waiting')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        displayWaitingList(data.data.waiting_list);
                    } else {
                        console.error('Error loading waiting list:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
        
        function displayWaitingList(waitingList) {
            const container = document.getElementById('waiting-list-container');
            container.innerHTML = '';
            
            if(waitingList.length === 0) {
                container.innerHTML = '<p>No learners currently on waiting list.</p>';
                return;
            }
            
            waitingList.forEach(learner => {
                const item = document.createElement('div');
                item.className = 'waiting-list-item';
                item.innerHTML = `
                    <div>
                        <strong>${learner.LearnerName}</strong> (Grade ${learner.Grade})<br>
                        Route: ${learner.RouteName}<br>
                        Parent: ${learner.ParentContact}<br>
                        Added: ${learner.DateAdded}
                    </div>
                    <button class="approve-btn" onclick="moveFromWaitingList(${learner.EntryID}, '${learner.LearnerName}')">
                        Approve & Move
                    </button>
                `;
                container.appendChild(item);
            });
        }
        
        function moveFromWaitingList(entryId, learnerName) {
            if(confirm(`Are you sure you want to move ${learnerName} from waiting list to approved registration?`)) {
                fetch('move_from_waiting_list.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `entry_id=${entryId}`
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        showMessage('Learner successfully moved from waiting list!', 'success');
                        loadWaitingList();
                    } else {
                        showMessage('Error: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    showMessage('Error processing request', 'error');
                    console.error('Error:', error);
                });
            }
        }
        
        function showMessage(message, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = type + '-message';
            messageDiv.textContent = message;
            
            const container = document.getElementById('waiting-list-container');
            container.insertBefore(messageDiv, container.firstChild);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }
        
        function performBackup() {
            fetch('backup_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=backup'
            })
            .then(response => response.json())
            .then(data => {
                const statusDiv = document.getElementById('backup-status');
                if(data.success) {
                    statusDiv.innerHTML = `<div class="success-message">Backup completed successfully!<br>File: ${data.filename}</div>`;
                } else {
                    statusDiv.innerHTML = `<div class="error-message">Backup failed: ${data.error}</div>`;
                }
            })
            .catch(error => {
                document.getElementById('backup-status').innerHTML = `<div class="error-message">Error performing backup</div>`;
                console.error('Error:', error);
            });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            loadWaitingList();
        });
    </script>
</body>
</html>