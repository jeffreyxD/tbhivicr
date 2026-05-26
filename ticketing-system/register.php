<?php
include 'config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username   = trim($_POST['username']);
    $email      = trim($_POST['email']);
    $department = trim($_POST['department']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role       = 'user';

    // ✅ CHECK IF USERNAME OR EMAIL EXISTS
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $message = "Username or Email already exists ❌";
    } else {

        // ✅ INSERT USER
        $stmt = $conn->prepare("INSERT INTO users (username, email, department, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $department, $password, $role);

        if ($stmt->execute()) {
            $message = "User created successfully ✅";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $check->close();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Register</title>
  <style>

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Segoe UI', sans-serif;
}

/* BODY */
body {
  height: 100vh;
  background: linear-gradient(135deg, #ff00cc, #3333ff, #00ffcc);
  background-size: 400% 400%;
  animation: gradientMove 10s ease infinite;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
}

/* ANIMATION */
@keyframes gradientMove {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

/* 3D BACKGROUND */
.background .cube {
  position: absolute;
  width: 80px;
  height: 80px;
  background: rgba(255,255,255,0.1);
  animation: rotateCube 15s linear infinite;
}

.background .cube:nth-child(1) { top: 10%; left: 20%; }
.background .cube:nth-child(2) { top: 70%; left: 70%; }
.background .cube:nth-child(3) { top: 50%; left: 10%; }

@keyframes rotateCube {
  0% { transform: rotateX(0) rotateY(0); }
  100% { transform: rotateX(360deg) rotateY(360deg); }
}

/* CARD */
.card {
  width: 350px;
  padding: 40px;
  border-radius: 20px;
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(15px);
  box-shadow: 0 15px 40px rgba(0,0,0,0.4);
  text-align: center;
  color: white;
  transform: perspective(1000px) rotateX(5deg);
  transition: 0.3s;
}

.card:hover {
  transform: perspective(1000px) rotateX(0deg) scale(1.03);
}

/* INPUT */
.input-group {
  position: relative;
  margin-bottom: 20px;
}

.input-group input {
  width: 100%;
  padding: 10px;
  border: none;
  border-bottom: 2px solid white;
  background: transparent;
  color: white;
  outline: none;
}

.input-group label {
  position: absolute;
  left: 0;
  top: 10px;
  transition: 0.3s;
}

.input-group input:focus + label,
.input-group input:valid + label {
  top: -10px;
  font-size: 12px;
  color: #00ffcc;
}

/* BUTTON */
button {
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 10px;
  background: linear-gradient(45deg, #ff00cc, #3333ff);
  color: white;
  cursor: pointer;
  transition: 0.3s;
  font-weight: bold;
}

button:hover {
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 10px 20px rgba(0,0,0,0.5);
}

/* MESSAGE */
.message {
  margin-bottom: 15px;
  padding: 10px;
  border-radius: 8px;
  background: rgba(0,0,0,0.3);
}

/* BACK LINK */
.back {
  margin-top: 15px;
  display: block;
  color: #fff;
  text-decoration: none;
}

.back:hover {
  text-decoration: underline;
}

  </style>
</head>
<body>

<div class="background">
  <div class="cube"></div>
  <div class="cube"></div>
  <div class="cube"></div>
</div>

<div class="card">

  <h1>Create Account 🚀</h1>

  <?php if ($message): ?>
    <div class="message"><?php echo $message; ?></div>
  <?php endif; ?>

  <form method="POST">

    <div class="input-group">
      <input type="text" name="username" required>
      <label>Username</label>
    </div>

    <div class="input-group">
      <input type="email" name="email" required>
      <label>Email</label>
    </div>

    <div class="input-group">
      <input type="text" name="department" required>
      <label>Department</label>
    </div>

    <div class="input-group">
      <input type="password" name="password" required>
      <label>Password</label>
    </div>

    <button type="submit">Register</button>

  </form>

  <a href="login.php" class="back">← Back to Login</a>

</div>

</body>
</html>