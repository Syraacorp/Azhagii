<?php
require_once 'db.php';
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// ==========================================
// LOGIN USER
// ==========================================
if (isset($_POST['login_user'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if fields are empty
    if (empty($username) || empty($password)) {
        echo json_encode([
            'status' => 422,
            'message' => 'Please fill in all fields'
        ]);
        return;
    }

    $query = "SELECT * FROM users WHERE username='$username'";
    $query_run = mysqli_query($conn, $query);

    if ($query_run && mysqli_num_rows($query_run) > 0) {
        $row = mysqli_fetch_array($query_run);

        // Direct password comparison (No hashing as requested)
        if ($password === $row['password']) {
            // Regenerate session ID
            session_regenerate_id(true);

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            $redirectLink = 'studentDashboard.php';
            if ($row['role'] === 'admin') {
                $redirectLink = 'adminDashboard.php';
            }

            echo json_encode([
                'status' => 200,
                'message' => 'Login Successful',
                'redirect' => $redirectLink,
                'user' => [
                    'id' => $row['id'],
                    'username' => $row['username'],
                    'role' => $row['role']
                ]
            ]);
            return;
        } else {
            echo json_encode([
                'status' => 401,
                'message' => 'Invalid Password'
            ]);
            return;
        }
    } else {
        echo json_encode([
            'status' => 404,
            'message' => 'No account found with that username'
        ]);
        return;
    }
}

// ==========================================
// REGISTER USER
// ==========================================
if (isset($_POST['register_user'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    $regno = mysqli_real_escape_string($conn, $_POST['regno']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    if (empty($name) || empty($department) || empty($year) || empty($regno) || empty($email) || empty($phone) || empty($username) || empty($password)) {
        echo json_encode([
            'status' => 422,
            'message' => 'All fields are required'
        ]);
        return;
    }

    if ($password !== $confirm_password) {
        echo json_encode([
            'status' => 422,
            'message' => 'Passwords do not match'
        ]);
        return;
    }

    // Check RegNo
    $check_reg = "SELECT id FROM users WHERE regno='$regno' LIMIT 1";
    $check_reg_run = mysqli_query($conn, $check_reg);
    if (mysqli_num_rows($check_reg_run) > 0) {
        echo json_encode([
            'status' => 422,
            'message' => 'Register Number already exists'
        ]);
        return;
    }

    // Check Email
    $check_email = "SELECT id FROM users WHERE email='$email' LIMIT 1";
    $check_email_run = mysqli_query($conn, $check_email);
    if (mysqli_num_rows($check_email_run) > 0) {
        echo json_encode([
            'status' => 422,
            'message' => 'Email is already taken'
        ]);
        return;
    }

    // Check Username
    $check_user = "SELECT id FROM users WHERE username='$username' LIMIT 1";
    $check_user_run = mysqli_query($conn, $check_user);
    if (mysqli_num_rows($check_user_run) > 0) {
        echo json_encode([
            'status' => 422,
            'message' => 'Username is already taken'
        ]);
        return;
    }

    // Insert User
    // NOTE: Storing password as plain text as requested
    $query = "INSERT INTO users (name, department, year, regno, email, phone, username, password, role) VALUES ('$name', '$department', '$year', '$regno', '$email', '$phone', '$username', '$password', 'user')";
    $query_run = mysqli_query($conn, $query);

    if ($query_run) {
        echo json_encode([
            'status' => 200,
            'message' => 'Registration Successful',
            'redirect' => 'login.php'
        ]);
        return;
    } else {
        echo json_encode([
            'status' => 500,
            'message' => 'Something went wrong: ' . mysqli_error($conn)
        ]);
        return;
    }
}
?>