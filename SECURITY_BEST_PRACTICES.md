# Security Best Practices - Quick Reference

## 1. Database Queries - ALWAYS Use Prepared Statements

### ✅ CORRECT:
```php
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
```

### ❌ WRONG - SQL Injection Vulnerable:
```php
$q = "SELECT * FROM users WHERE id=$user_id";
mysqli_query($conn, $q);
```

## 2. File Uploads - Use uploadFile() Helper

### ✅ CORRECT:
```php
$allowedMimes = ['image/jpeg', 'image/png'];
$allowedExts = ['jpg', 'jpeg', 'png'];
$file = uploadFile('file_field', $allowedMimes, $allowedExts, 5, 'uploads/images', 'img');
```

### ❌ WRONG - Insecure:
```php
move_uploaded_file($_FILES['file']['tmp_name'], "uploads/" . $_FILES['file']['name']);
```

## 3. Output Escaping - Prevent XSS

### ✅ CORRECT:
```php
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');
```

### ❌ WRONG - XSS Vulnerable:
```php
echo $user_input;
```

## 4. CSRF Protection

### ✅ CORRECT - In Forms:
```php
<form method="POST">
    <?php echo getCSRFInput(); ?>
    <input type="text" name="data">
</form>
```

### ✅ CORRECT - In Backend:
```php
requireCSRF(); // Add at start of POST handlers
// ... rest of your code
```

## 5. Input Validation

### ✅ CORRECT:
```php
$id = intval($_POST['id'] ?? 0);
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$name = trim(strip_tags($_POST['name'] ?? ''));
```

## 6. Error Handling

### ✅ CORRECT:
```php
if ($stmt->execute()) {
    $stmt->close();
    respond(200, 'Success');
} else {
    $error = $stmt->error;
    $stmt->close();
    respond(500, 'Operation failed: ' . $error);
}
```

## 7. Authorization Checks

### ✅ CORRECT:
```php
requireLogin(); // Check user is logged in
requireRole(['admin', 'coordinator']); // Check user has required role

// For ownership checks:
if ($resource_owner_id != uid()) {
    respond(403, 'Not authorized');
}
```

## Quick Checklist for New Code:
- [ ] All database queries use prepared statements?
- [ ] All file uploads use uploadFile() helper?
- [ ] All output is escaped with htmlspecialchars()?
- [ ] CSRF token added to forms and validated in backend?
- [ ] Input validated and sanitized?
- [ ] Authorization checks in place?
- [ ] Error handling implements properly?
- [ ] Sensitive operations logged?

*Keep this accessible while coding!*
