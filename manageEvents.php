<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <a href="manageEvents.php" class="active">
                    <i class="fas fa-calendar-alt"></i> Manage Events
                </a>
                <a href="manageUsers.php">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="registrations.php">
                    <i class="fas fa-clipboard-list"></i> Registrations
                </a>
                <a href="analytics.php">
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
                    <h2 style="font-size: 1.25rem; margin: 0;">Manage Events</h2>
                </div>
                <!-- Profile logic same as Dashboard -->
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

            <!-- Events List -->
            <div class="card"
                style="border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-md); padding: 0; overflow: hidden;">
                <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0;">Existing Events</h3>
                    <button class="btn btn-primary" onclick="openCreateModal()">
                        <i class="fas fa-plus"></i> Create New Event
                    </button>
                </div>
                <div class="table-responsive" style="border: none; border-radius: 0;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Date & Time</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Capacity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $statusClass = $row['status'] == 'upcoming' ? 'status-success' : ($row['status'] == 'completed' ? 'status-warning' : 'status-danger');
                                    // Use format like: Jan 01, 2024
                                    $dateFormatted = date('M d, Y', strtotime($row['event_date']));
                                    // Use format like: 10:00 AM
                                    $timeFormatted = date('h:i A', strtotime($row['event_time']));
                                    
                                    echo "<tr>";
                                    echo "<td>#{$row['id']}</td>";
                                    echo "<td>{$row['title']}</td>";
                                    echo "<td>{$dateFormatted} <br><small class='text-muted'>{$timeFormatted}</small></td>";
                                    echo "<td>{$row['location']}</td>";
                                    echo "<td><span class='status-badge {$statusClass}'>" . ucfirst($row['status']) . "</span></td>";
                                    echo "<td>{$row['capacity']}</td>";
                                    echo "<td>
                                            <a href='editEvent.php?id={$row['id']}' class='btn btn-outline' style='padding: 0.25rem 0.5rem; font-size: 0.8rem;'>Edit</a>
                                            <button class='btn btn-outline' style='padding: 0.25rem 0.5rem; font-size: 0.8rem; color: #f87171; border-color: rgba(248, 113, 113, 0.5);' onclick='confirmDelete({$row['id']})'>Delete</button>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' style='text-align:center;'>No events found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- Script for SweetAlert Modal -->
    <script>
        function openCreateModal() {
            Swal.fire({
                title: 'Create New Event',
                html: `
                    <form id="createEventForm" style="text-align: left;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <!-- Row 1 -->
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 0.25rem; font-size: 0.9rem;">Event Title <span style="color:red">*</span></label>
                                <input type="text" id="title" class="swal2-input" style="margin: 0; width: 100%; box-sizing: border-box;" required>
                            </div>
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 0.25rem; font-size: 0.9rem;">Date <span style="color:red">*</span></label>
                                <input type="date" id="event_date" class="swal2-input" style="margin: 0; width: 100%; box-sizing: border-box;" required>
                            </div>

                            <!-- Row 2 -->
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 0.25rem; font-size: 0.9rem;">Time <span style="color:red">*</span></label>
                                <input type="time" id="event_time" class="swal2-input" style="margin: 0; width: 100%; box-sizing: border-box;" required>
                            </div>
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 0.25rem; font-size: 0.9rem;">Location <span style="color:red">*</span></label>
                                <input type="text" id="location" class="swal2-input" style="margin: 0; width: 100%; box-sizing: border-box;" required>
                            </div>

                            <!-- Row 3 -->
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 0.25rem; font-size: 0.9rem;">Category</label>
                                <select id="category" class="swal2-select" style="margin: 0; width: 100%; box-sizing: border-box;">
                                    <option value="Workshop">Workshop</option>
                                    <option value="Seminar">Seminar</option>
                                    <option value="Webinar">Webinar</option>
                                    <option value="Hackathon">Hackathon</option>
                                    <option value="Cultural">Cultural</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 0.25rem; font-size: 0.9rem;">Capacity</label>
                                <input type="number" id="capacity" class="swal2-input" value="100" style="margin: 0; width: 100%; box-sizing: border-box;">
                            </div>

                            <!-- Row 4 -->
                            <div class="form-group">
                                <label style="display: block; margin-bottom: 0.25rem; font-size: 0.9rem;">Price ($)</label>
                                <input type="number" id="price" class="swal2-input" value="0.00" step="0.01" style="margin: 0; width: 100%; box-sizing: border-box;">
                            </div>
                            
                             <div class="form-group">
                                <label style="display: block; margin-bottom: 0.25rem; font-size: 0.9rem;">Image URL</label>
                                <input type="text" id="image_url" class="swal2-input" placeholder="https://example.com/image.jpg" style="margin: 0; width: 100%; box-sizing: border-box;">
                            </div>
                            
                            <!-- Row 5 -->
                            <div class="form-group" style="grid-column: span 2;">
                                <label style="display: block; margin-bottom: 0.25rem; font-size: 0.9rem;">Description</label>
                                <textarea id="description" class="swal2-textarea" style="margin: 0; width: 100%; box-sizing: border-box;" rows="2"></textarea>
                            </div>
                        </div>
                    </form>
                `,
                width: '800px',
                showCancelButton: true,
                confirmButtonText: 'Create Event',
                confirmButtonColor: '#4285f4',
                cancelButtonColor: '#d33',
                background: '#1e1e1e', 
                color: '#fff',
                focusConfirm: false,
                preConfirm: () => {
                    const title = document.getElementById('title').value;
                    const event_date = document.getElementById('event_date').value;
                    const event_time = document.getElementById('event_time').value;
                    const location = document.getElementById('location').value;
                    const category = document.getElementById('category').value;
                    const capacity = document.getElementById('capacity').value;
                    const price = document.getElementById('price').value;
                    const image_url = document.getElementById('image_url').value;
                    const description = document.getElementById('description').value;

                    if (!title || !event_date || !event_time || !location) {
                        Swal.showValidationMessage('Please fill in all required fields');
                        return false;
                    }

                    return { 
                        action: 'create',
                        title: title,
                        event_date: event_date,
                        event_time: event_time,
                        location: location,
                        category: category,
                        capacity: capacity,
                        price: price,
                        image_url: image_url,
                        description: description
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitEvent(result.value);
                }
            });
        }

        function submitEvent(data) {
             // Create form data to send
            const formData = new FormData();
            for (const key in data) {
                formData.append(key, data[key]);
            }

            fetch('eventActions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.redirected) {
                     window.location.href = response.url;
                } else {
                     // Reload to see new event
                     window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Something went wrong', 'error');
            });
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                background: '#1e1e1e',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `eventActions.php?action=delete&id=${id}`;
                }
            })
        }
    </script>
    
    <style>
        /* Custom styles for SweetAlert dark mode inputs */
        .swal2-input, .swal2-textarea, .swal2-select {
            background: #2d2d2d !important;
            color: white !important;
            border: 1px solid #444 !important;
        }
        .swal2-input:focus, .swal2-textarea:focus, .swal2-select:focus {
            box-shadow: 0 0 0 2px rgba(66, 133, 244, 0.5) !important;
        }
    </style>

</body>

</html>