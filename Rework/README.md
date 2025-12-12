# Pin2Fix - Geo-Location Based Complaint Management System

A comprehensive complaint management system where citizens can raise geo-location based complaints with evidence, which go through a multi-level government approval workflow.

## Features

### For Citizens
- Register and login
- Raise complaints with geo-location (map picker)
- Upload photo evidence
- Track complaint status
- Receive notifications on status updates
- Provide feedback after resolution
- Request rework if unsatisfied

### For Government Body
- View all pending issues in their jurisdiction
- Forward issues to appropriate departments
- Give final approval after department head approval
- View analytics and reports

### For Department Head
- View assigned issues
- Assign workers to issues
- Review work evidence submitted by workers
- Approve or reject and request rework

### For Workers
- View assigned tasks
- Start work on tasks
- Submit work completion evidence with photos
- View task history

### For Admin
- Manage users (create, update, activate/deactivate)
- Manage government bodies
- Manage departments
- View all issues and reports
- System analytics

## Tech Stack

- **Backend**: Spring Boot 3.2.0
- **Database**: MySQL 8.x
- **Frontend**: HTML, CSS, JavaScript
- **Maps**: Leaflet.js with OpenStreetMap
- **Alerts**: SweetAlert2
- **Charts**: Chart.js

## Prerequisites

- Java 17 or higher
- MySQL 8.x
- Maven 3.6+

## Setup Instructions

### 1. Database Setup

```sql
-- Run the SQL script to create database and tables
mysql -u root -p < pin2fix_updated.sql
```

Or run the script manually in MySQL Workbench.

### 2. Configure Application

Edit `src/main/resources/application.properties`:

```properties
spring.datasource.url=jdbc:mysql://localhost:3306/pin2fix
spring.datasource.username=YOUR_USERNAME
spring.datasource.password=YOUR_PASSWORD
```

### 3. Build the Application

```bash
mvn clean install
```

### 4. Run the Application

```bash
mvn spring-boot:run
```

Or run the JAR file:

```bash
java -jar target/pin2fix-1.0.0.jar
```

### 5. Access the Application

Open your browser and go to: `http://localhost:8080`

## Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@pin2fix.com | admin123 |
| Gov Body (Chennai) | gov@chennai.gov.in | gov123 |
| Dept Head (Roads) | roads.head@chennai.gov.in | head123 |
| Worker | rajesh@chennai.gov.in | worker123 |
| Citizen | arun@gmail.com | citizen123 |

## Workflow

1. **Citizen Reports Issue**: Citizen logs in, creates a new complaint with location and photos
2. **Gov Body Reviews**: Government body reviews and forwards to appropriate department
3. **Dept Head Assigns**: Department head assigns the issue to a worker
4. **Worker Completes**: Worker starts work, then submits evidence photos
5. **Dept Head Approves**: Department head reviews evidence and approves
6. **Gov Body Final Approval**: Government body gives final approval
7. **Citizen Notified**: Citizen receives notification of completion
8. **Citizen Feedback**: Citizen provides feedback
   - Positive: Issue marked as complete
   - Negative: Issue reopened for rework

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login

### Issues
- `POST /api/issues` - Create new issue
- `GET /api/issues` - Get all issues
- `GET /api/issues/{id}` - Get issue by ID
- `GET /api/issues/reporter/{id}` - Get issues by reporter
- `POST /api/issues/{id}/photos` - Upload photo for issue
- `POST /api/issues/forward` - Forward issue to department

### Assignments
- `POST /api/assignments` - Create assignment
- `GET /api/assignments/issue/{id}` - Get assignments by issue
- `PUT /api/assignments/{id}/status` - Update assignment status

### Evidence
- `POST /api/evidence` - Submit work evidence
- `GET /api/evidence/assignment/{id}` - Get evidence by assignment

### Approvals
- `POST /api/approvals/head` - Department head approval
- `POST /api/approvals/gov` - Government final approval

### Feedback
- `POST /api/feedback` - Submit feedback
- `GET /api/feedback/issue/{id}` - Get feedback by issue

### Notifications
- `GET /api/notifications/user/{id}` - Get user notifications
- `PUT /api/notifications/{id}/read` - Mark as read

### Organizations
- `GET /api/organizations/gov-bodies` - Get all government bodies
- `GET /api/organizations/departments` - Get all departments

## Project Structure

```
pin2fix/
├── src/main/java/com/pin2fix/
│   ├── config/         # Configuration classes
│   ├── controller/     # REST Controllers
│   ├── dto/            # Data Transfer Objects
│   ├── model/          # Entity classes
│   ├── repository/     # JPA Repositories
│   ├── service/        # Business logic
│   └── Pin2FixApplication.java
├── src/main/resources/
│   ├── static/         # Frontend files
│   │   ├── admin/      # Admin pages
│   │   ├── citizen/    # Citizen pages
│   │   ├── dept/       # Dept Head pages
│   │   ├── gov/        # Gov Body pages
│   │   ├── worker/     # Worker pages
│   │   ├── css/        # Stylesheets
│   │   └── js/         # JavaScript
│   └── application.properties
├── pin2fix_updated.sql # Database schema
└── pom.xml             # Maven configuration
```

## Notes

- **Passwords are stored in plaintext** as per requirements (NOT recommended for production)
- **No color gradients** used in UI as per requirements
- **SweetAlert2** used for all alerts and confirmations
- File uploads are stored in `./uploads` directory

## License

This project is for educational purposes.
