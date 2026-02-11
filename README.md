# EventManager

A full-featured event management web application built with PHP, MySQL, and jQuery. Create events, manage registrations, track attendance, and auto-generate participation certificates — all from a clean, responsive dashboard.

---

## Features

### Admin
- **Dashboard** with real-time stats (total events, users, registrations, completions)
- **Create / Delete Events** with title, description, date, location, and participant limits
- **Manage Attendees** — view registrations per event, mark users as completed, and provide personalized feedback
- **User Management** — view all registered users with roles and join dates

### User
- **Dashboard** with personal stats (registered, completed, cancelled)
- **Browse & Register** for upcoming events in one click
- **Cancel Registrations** before the event
- **Download Certificates** (auto-generated PNG) for completed events
- **View Coordinator Feedback** left by the admin

### General
- Responsive UI — works on desktop, tablet, and mobile
- Role-based access control (admin / user)
- SweetAlert2 confirmation dialogs for destructive actions
- Sticky navbar with mobile hamburger menu
- Dashboard sidebar with mobile overlay

---

## Tech Stack

| Layer      | Technology                          |
|------------|-------------------------------------|
| Backend    | PHP 7.4+                            |
| Database   | MySQL / MariaDB                     |
| Frontend   | HTML5, CSS3 (custom), JavaScript    |
| Libraries  | jQuery 3.6, SweetAlert2, Font Awesome 6 |
| Font       | Inter (Google Fonts)                |
| Server     | Apache (XAMPP / WAMP / LAMP)        |

---

## Project Structure

```
Ziya/
├── index.php                  # Landing page (hero + features)
├── login.php                  # User login
├── register.php               # User registration
├── logout.php                 # Session destroy + redirect
├── certificate.php            # Certificate PNG generator (GD library)
├── check_status.php           # Environment health check
├── setup.php                  # Database initializer (runs database.sql)
├── seed.php                   # Sample data seeder (admin + events)
├── database.sql               # Full schema (users, events, registrations)
│
├── config/
│   └── db.php                 # Database connection + BASE_URL constant
│
├── includes/
│   ├── header.php             # Public page header (navbar)
│   ├── footer.php             # Public page footer
│   ├── dashboard_header.php   # Dashboard layout (sidebar + top bar)
│   └── dashboard_footer.php   # Dashboard closing tags + scripts
│
├── admin/
│   ├── index.php              # Admin dashboard (stats + event list)
│   ├── create_event.php       # Create event form
│   ├── event_details.php      # View event registrations + mark complete
│   ├── update_status.php      # AJAX: update registration status/feedback
│   ├── delete_event.php       # AJAX: delete an event
│   └── users.php              # User management table
│
├── user/
│   ├── index.php              # User dashboard (events + registrations)
│   ├── register_event.php     # AJAX: register for an event
│   └── cancel_registration.php# AJAX: cancel a registration
│
└── assets/
    ├── css/
    │   └── style.css          # All styles (responsive, dashboard, components)
    └── js/
        └── script.js          # Nav toggle, sidebar, dropdown, SweetAlert
```

---

## Database Schema

### `users`
| Column     | Type                            | Notes              |
|------------|---------------------------------|--------------------|
| id         | INT, AUTO_INCREMENT, PK         |                    |
| username   | VARCHAR(50), UNIQUE             |                    |
| email      | VARCHAR(100), UNIQUE            |                    |
| password   | VARCHAR(255)                    |                    |
| role       | ENUM('admin', 'user')           | Default: `user`    |
| created_at | TIMESTAMP                       | Auto-set           |

### `events`
| Column           | Type                    | Notes                     |
|------------------|-------------------------|---------------------------|
| id               | INT, AUTO_INCREMENT, PK |                           |
| title            | VARCHAR(100)            |                           |
| description      | TEXT                    |                           |
| event_date       | DATETIME                |                           |
| location         | VARCHAR(100)            |                           |
| max_participants | INT                     | `0` = unlimited           |
| created_at       | TIMESTAMP               | Auto-set                  |

### `registrations`
| Column            | Type                                          | Notes                      |
|-------------------|-----------------------------------------------|----------------------------|
| id                | INT, AUTO_INCREMENT, PK                       |                            |
| user_id           | INT, FK → users(id)                           | CASCADE on delete          |
| event_id          | INT, FK → events(id)                          | CASCADE on delete          |
| status            | ENUM('registered', 'attended', 'cancelled')   | Default: `registered`      |
| feedback          | TEXT, NULLABLE                                | Admin feedback to user     |
| registration_date | TIMESTAMP                                     | Auto-set                   |

> Unique constraint on `(user_id, event_id)` prevents duplicate registrations.

---

## Installation

### Prerequisites
- **PHP 7.4+** with GD extension enabled (for certificate generation)
- **MySQL 5.7+** or MariaDB
- **Apache** with `mod_rewrite` (XAMPP, WAMP, LAMP, or similar)

### Steps

1. **Clone the repository** into your web server's document root:
   ```bash
   git clone https://github.com/jayanthansenthilkumar/Ziya.git
   ```

2. **Configure the base URL** in `config/db.php` if your folder name differs:
   ```php
   define('BASE_URL', '/Ziya');
   ```

3. **Update database credentials** in `config/db.php` if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'event_manager');
   ```

4. **Initialize the database** by visiting:
   ```
   http://localhost/Ziya/setup.php
   ```

5. **Seed sample data** (optional — creates an admin account + 3 sample events):
   ```
   http://localhost/Ziya/seed.php
   ```

6. **Login** with the seeded admin account:
   ```
   Email:    admin@example.com
   Password: admin123
   ```

7. **Verify environment** (optional):
   ```
   http://localhost/Ziya/check_status.php
   ```

---

## Usage

| Action                 | URL / Path                          |
|------------------------|-------------------------------------|
| Home page              | `/Ziya/`                            |
| Login                  | `/Ziya/login.php`                   |
| Register               | `/Ziya/register.php`                |
| Admin Dashboard        | `/Ziya/admin/index.php`             |
| User Dashboard         | `/Ziya/user/index.php`              |
| Environment Check      | `/Ziya/check_status.php`            |
| Download Certificate   | `/Ziya/certificate.php?id={event}`  |

---

## Screenshots

> The app features a gradient hero section, card-based feature highlights, a sidebar dashboard layout with stat cards, and mobile-responsive navigation.

---

## License

This project is open source and available under the [MIT License](https://opensource.org/licenses/MIT).
