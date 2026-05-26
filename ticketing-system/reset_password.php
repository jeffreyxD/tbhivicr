<?php
// reset_password.php - handles the reset link
session_start();
include 'config.php';

if (!isset($_GET['token'])) {
    die('Invalid reset link');
}

$token = $_GET['token'];

// Verify token is valid and not expired
$stmt = $conn->prepare("SELECT * FROM password_resets WHERE token=? AND expires > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Invalid or expired reset link');
}

$reset = $result->fetch_assoc();
?>
<!-- Reset password form here -->
<form method="POST" action="reset_password.php">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <input type="password" name="new_password" required>
    <input type="password" name="confirm_password" required>
    <button type="submit">Reset Password</button>
</form>