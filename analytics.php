<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once 'db.php';

// --- Data Fetching for Charts ---

// 1. Registrations per Event (Top 5)
$reg_chart_sql = "SELECT e.title, count(r.id) as count 
                  FROM events e 
                  LEFT JOIN registrations r ON e.id = r.event_id 
                  GROUP BY e.id 
                  ORDER BY count DESC 
                  LIMIT 5";
$reg_chart_res = $conn->query($reg_chart_sql);
$reg_labels = [];
$reg_data = [];
while ($row = $reg_chart_res->fetch_assoc()) {
    $reg_labels[] = $row['title'];
    $reg_data[] = $row['count'];
}

// 2. Registrations by Status
$status_sql = "SELECT status, COUNT(*) as count FROM registrations GROUP BY status";
$status_res = $conn->query($status_sql);
$status_labels = [];
$status_data = [];
while ($row = $status_res->fetch_assoc()) {
    $status_labels[] = ucfirst($row['status']);
    $status_data[] = $row['count'];
}

// 3. Revenue by Event (Top 5)
$rev_chart_sql = "SELECT e.title, SUM(e.price) as revenue 
                  FROM events e 
                  JOIN registrations r ON e.id = r.event_id 
                  WHERE r.status = 'registered' 
                  GROUP BY e.id 
                  ORDER BY revenue DESC 
                  LIMIT 5";
$rev_chart_res = $conn->query($rev_chart_sql);
$rev_labels = [];
$rev_data = [];
while ($row = $rev_chart_res->fetch_assoc()) {
    $rev_labels[] = $row['title'];
    $rev_data[] = $row['revenue'];
}

// 4. Monthly Registrations (Last 6 months)
$monthly_sql = "SELECT DATE_FORMAT(registration_date, '%Y-%m') as month, COUNT(*) as count 
                FROM registrations 
                WHERE registration_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
                GROUP BY month 
                ORDER BY month ASC";
$monthly_res = $conn->query($monthly_sql);
$monthly_labels = [];
$monthly_data = [];
while ($row = $monthly_res->fetch_assoc()) {
    $monthly_labels[] = date('M Y', strtotime($row['month'] . '-01'));
    $monthly_data[] = $row['count'];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="dashboard-body">

    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="adminDashboard.php" class="logo" style="font-size: 1.25rem;">
                    <span class="sparkle-icon"></span> Ziya Admin
                </a>
            </div>

            <nav class="sidebar-menu">
                <a href="adminDashboard.php">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </a>
                <a href="manageEvents.php">
                    <i class="fas fa-calendar-alt"></i> Manage Events
                </a>
                <a href="manageUsers.php">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="registrations.php">
                    <i class="fas fa-clipboard-list"></i> Registrations
                </a>
                <a href="analytics.php" class="active">
                    <i class="fas fa-chart-line"></i> Analytics
                </a>
                <a href="settings.php">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </nav>

            <div class="sidebar-footer">
                <a href="logout.php"
                    style="color: var(--text-muted); display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 0.9rem;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <div>
                    <h2 style="font-size: 1.25rem; margin: 0;">Analytics Dashboard</h2>
                </div>
                <!-- Profile logic -->
                <div class="user-profile">
                    <div style="text-align: right;">
                        <div style="font-weight: 600; font-size: 0.9rem;">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Administrator</div>
                    </div>
                    <div class="avatar-circle">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                </div>
            </header>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">

                <!-- Chart 1: Registrations per Event -->
                <div class="card"
                    style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem;">
                    <h3 style="margin-top: 0; font-size: 1rem; color: var(--text-muted);">Top 5 Events by Registration
                    </h3>
                    <canvas id="regChart"></canvas>
                </div>

                <!-- Chart 2: Registration Status Distribution -->
                <div class="card"
                    style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem;">
                    <h3 style="margin-top: 0; font-size: 1rem; color: var(--text-muted);">Registration Status Overview
                    </h3>
                    <div style="max-height: 300px; display: flex; justify-content: center;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <!-- Chart 3: Revenue per Top 5 Events -->
                <div class="card"
                    style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem;">
                    <h3 style="margin-top: 0; font-size: 1rem; color: var(--text-muted);">Top Generated Revenue by Event
                    </h3>
                    <canvas id="revChart"></canvas>
                </div>

                <!-- Chart 4: Growth Over Time -->
                <div class="card"
                    style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem;">
                    <h3 style="margin-top: 0; font-size: 1rem; color: var(--text-muted);">Registration Growth (Last 6
                        Months)</h3>
                    <canvas id="growthChart"></canvas>
                </div>

            </div>

        </main>
    </div>

    <script>
        // Common Options
        Chart.defaults.color = '#94a3b8';
        Chart.defaults.borderColor = '#334155';

        // 1. Registration Chart (Bar)
        const ctxReg = document.getElementById('regChart').getContext('2d');
        new Chart(ctxReg, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($reg_labels); ?>,
                datasets: [{
                    label: 'Registrations',
                    data: <?php echo json_encode($reg_data); ?>,
                    backgroundColor: 'rgba(66, 133, 244, 0.5)',
                    borderColor: '#4285f4',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // 2. Status Chart (Doughnut)
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($status_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($status_data); ?>,
                    backgroundColor: [
                        'rgba(52, 211, 153, 0.6)',
                        'rgba(248, 113, 113, 0.6)',
                        'rgba(251, 191, 36, 0.6)'
                    ],
                    borderColor: [
                        '#34d399',
                        '#f87171',
                        '#fbbf24'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // 3. Revenue Chart (Bar)
        const ctxRev = document.getElementById('revChart').getContext('2d');
        new Chart(ctxRev, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($rev_labels); ?>,
                datasets: [{
                    label: 'Revenue ($)',
                    data: <?php echo json_encode($rev_data); ?>,
                    backgroundColor: 'rgba(155, 114, 203, 0.5)',
                    borderColor: '#9b72cb',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // 4. Growth Chart (Line)
        const ctxGrowth = document.getElementById('growthChart').getContext('2d');
        new Chart(ctxGrowth, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($monthly_labels); ?>,
                datasets: [{
                    label: 'New Registrations',
                    data: <?php echo json_encode($monthly_data); ?>,
                    borderColor: '#d96570',
                    backgroundColor: 'rgba(217, 101, 112, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>

</body>

</html>