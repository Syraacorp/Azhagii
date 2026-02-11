<?php
require_once 'db.php';
session_start();

// Fetch Upcoming Events
$sql_upcoming = "SELECT * FROM events WHERE event_date >= NOW() ORDER BY event_date ASC LIMIT 6";
$result_upcoming = $conn->query($sql_upcoming);

// Fetch Past Events
$sql_past = "SELECT * FROM events WHERE event_date < NOW() ORDER BY event_date DESC LIMIT 6";
$result_past = $conn->query($sql_past);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ziya - Next Gen Events</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="logo">
                <span class="sparkle-icon"></span> Ziya
            </a>
            <ul class="nav-links">
                <li><a href="#hero">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#upcoming">Upcoming</a></li>
                <li><a href="#past">Past</a></li>
                <li><a href="#contact">Contact</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <?php endif; ?>
            </ul>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Hi,
                        <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Logout</a>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">Sign In</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero -->
    <section id="hero" class="hero">
        <div class="hero-glow"></div>
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-sparkles"></i> AI-Powered Event Management
            </div>
            <h1>The Future of <br><span>Event Experience</span></h1>
            <p>Ziya brings intelligence to your gatherings. Seamlessly organize, manage, and experience events with our
                next-generation platform.</p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="#upcoming" class="btn btn-primary">Explore Events</a>
                <a href="#about" class="btn btn-outline">Learn More</a>
            </div>
        </div>
    </section>

    <!-- About -->
    <section id="about">
        <div class="section-header">
            <h2>Reimagining Connections</h2>
        </div>
        <div class="about-grid">
            <div class="about-card">
                <h3><i class="fas fa-rocket" style="color: var(--accent-purple); margin-right: 10px;"></i> Smart
                    Planning</h3>
                <p>Leverage AI to optimize schedules, guest lists, and logistics. Ziya anticipates your needs before you
                    do.</p>
            </div>
            <div class="about-card" style="border-color: var(--accent-blue);">
                <h3><i class="fas fa-users" style="color: var(--accent-blue); margin-right: 10px;"></i> Community First
                </h3>
                <p>Build thriving communities with tools designed for engagement, from interactive sessions to
                    post-event analytics.</p>
            </div>
        </div>
    </section>

    <!-- Upcoming Events -->
    <section id="upcoming">
        <div class="section-header">
            <h2>Upcoming Events</h2>
            <a href="#" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View All</a>
        </div>

        <?php if ($result_upcoming && $result_upcoming->num_rows > 0): ?>
            <div class="events-grid">
                <?php while ($row = $result_upcoming->fetch_assoc()): ?>
                    <div class="event-card">
                        <div class="event-date">
                            <i class="far fa-calendar-alt"></i>
                            <?php echo date('M d, Y', strtotime($row['event_date'])); ?>
                        </div>
                        <h3 class="event-title">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </h3>
                        <p style="font-size: 0.95rem; margin-bottom: 1.5rem; flex-grow: 1;">
                            <?php echo substr(htmlspecialchars($row['description']), 0, 100) . '...'; ?>
                        </p>
                        <div class="event-details">
                            <span><i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($row['location']); ?>
                            </span>
                            <span><i class="fas fa-user-friends"></i>
                                <?php echo $row['max_participants']; ?> Spots
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-muted" style="text-align: center;">No upcoming events found. Stay tuned!</p>
        <?php endif; ?>
    </section>

    <!-- Past Events -->
    <section id="past" style="opacity: 0.8;">
        <div class="section-header">
            <h2>Past Events</h2>
        </div>

        <?php if ($result_past && $result_past->num_rows > 0): ?>
            <div class="events-grid">
                <?php while ($row = $result_past->fetch_assoc()): ?>
                    <div class="event-card" style="background: rgba(30, 31, 32, 0.4);">
                        <div class="event-date" style="color: var(--text-muted);">
                            <?php echo date('M d, Y', strtotime($row['event_date'])); ?>
                        </div>
                        <h3 class="event-title" style="color: var(--text-muted);">
                            <?php echo htmlspecialchars($row['title']); ?>
                        </h3>
                        <p style="font-size: 0.9rem; color: #777;">
                            <?php echo substr(htmlspecialchars($row['description']), 0, 80) . '...'; ?>
                        </p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-muted" style="text-align: center;">No past events available.</p>
        <?php endif; ?>
    </section>

    <!-- Contact -->
    <section id="contact">
        <div class="contact-container">
            <h2 style="text-align: center; margin-bottom: 2rem;">Get in Touch</h2>
            <form action="#" method="POST">
                <div class="form-group">
                    <input type="text" name="name" class="form-input" placeholder="Your Name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" class="form-input" placeholder="Your Email" required>
                </div>
                <div class="form-group">
                    <textarea name="message" class="form-input" placeholder="How can we help?" required></textarea>
                </div>
                <button type="button" class="btn btn-primary" style="width: 100%;">Send Message</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-content">
            <h3 class="logo" style="justify-content: center; margin-bottom: 1rem;">
                <span class="sparkle-icon"></span> Ziya
            </h3>
            <p>&copy;
                <?php echo date('Y'); ?> Ziya. All rights reserved.
            </p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Support</a>
                <a href="#">Twitter</a>
            </div>
        </div>
    </footer>

</body>

</html>