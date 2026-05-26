<?php
session_start();
include 'config.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'forgot_password') {
        $email = trim($_POST['email']);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address ❌";
        } else {
            $stmt = $conn->prepare("SELECT id, username FROM users WHERE email=?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user) {
                $reset_token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token=?, expires=?");
                $stmt->bind_param("sssss", $email, $reset_token, $expires, $reset_token, $expires);
                $stmt->execute();
                
                $reset_link = "http://yourdomain.com/reset_password.php?token=" . $reset_token;
                $subject = "Password Reset Request";
                $message = "Click this link to reset your password: " . $reset_link . "\n\nLink expires in 1 hour.";
                $headers = "From: noreply@yourdomain.com";
                
                if (mail($email, $subject, $message, $headers)) {
                    $success = "Password reset link sent to your email! ✅";
                } else {
                    $error = "Failed to send email. Please try again. ❌";
                }
            } else {
                $success = "If an account exists, password reset link sent to your email! ✅";
            }
        }
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: admin.php");
                exit();
            } else {
                header("Location: dashboard.php");
                exit();
            }
        } else {
            $error = "Invalid username or password ❌";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>NeoLogin - Cyber Portal</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Exo+2:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Exo 2', sans-serif;
            height: 100vh;
            overflow: hidden;
            background: #0a0a0a;
            position: relative;
        }

        /* Matrix Background */
        .matrix-bg {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 1;
            background: radial-gradient(ellipse at center, #0d1117 0%, #000 70%);
        }

        .hologrid {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-image: 
                linear-gradient(rgba(0,245,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,245,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: grid-scan 20s linear infinite;
            z-index: 2;
        }

        @keyframes grid-scan {
            0% { background-position: 0 0; }
            100% { background-position: 100px 100px; }
        }

        /* Floating Particles */
        .particles {
            position: absolute;
            width: 100%; height: 100%;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%,100% { transform: translateY(0px) rotate(0deg) scale(1); }
            33% { transform: translateY(-30px) rotate(120deg) scale(1.1); }
            66% { transform: translateY(-15px) rotate(240deg) scale(0.9); }
        }

        /* Main Container */
        .cyber-container {
            position: relative;
            z-index: 10;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            perspective: 1000px;
        }

        /* 3D Cyber Card */
        .cyber-card {
            width: 420px;
            padding: 60px 40px;
            background: rgba(15,15,25,0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(0,245,255,0.3);
            border-radius: 25px;
            box-shadow: 
                0 25px 50px rgba(0,0,0,0.5),
                inset 0 1px 0 rgba(255,255,255,0.1),
                0 0 100px rgba(0,245,255,0.1);
            transform-style: preserve-3d;
            transition: all 0.6s cubic-bezier(0.23,1,0.320,1);
            animation: cardFloat 6s ease-in-out infinite;
            position: relative;
        }

        .cyber-card:hover {
            transform: rotateX(5deg) rotateY(5deg) translateY(-10px);
            box-shadow: 
                0 35px 70px rgba(0,0,0,0.7),
                0 0 150px rgba(0,245,255,0.3);
        }

        @keyframes cardFloat {
            0%,100% { transform: translateY(0px) rotateX(0deg); }
            50% { transform: translateY(-15px) rotateX(2deg); }
        }

        /* Header */
        .cyber-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .cyber-title {
            font-family: 'Orbitron', monospace;
            font-size: 2.5rem;
            font-weight: 900;
            background: linear-gradient(45deg, #00f5ff, #ff00ff, #00ff88, #00f5ff);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: neon-glow 3s ease-in-out infinite alternate;
            margin-bottom: 10px;
            text-shadow: 0 0 30px rgba(0,245,255,0.5);
        }

        .cyber-subtitle {
            color: rgba(255,255,255,0.7);
            font-size: 1.1rem;
            font-weight: 300;
            letter-spacing: 2px;
        }

        @keyframes neon-glow {
            0% { filter: hue-rotate(0deg) drop-shadow(0 0 10px rgba(0,245,255,0.5)); }
            100% { filter: hue-rotate(360deg) drop-shadow(0 0 20px rgba(255,0,255,0.8)); }
        }

        /* Inputs */
        .input-group {
            position: relative;
            margin-bottom: 30px;
        }

        .input-field {
            width: 100%;
            padding: 18px 20px;
            background: rgba(255,255,255,0.05);
            border: 2px solid rgba(0,245,255,0.2);
            border-radius: 15px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.4s ease;
        }

        .input-field:focus {
            outline: none;
            border-color: #00f5ff;
            box-shadow: 0 0 25px rgba(0,245,255,0.4);
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        .input-label {
            position: absolute;
            left: 20px;
            top: 18px;
            color: rgba(255,255,255,0.5);
            font-size: 1rem;
            pointer-events: none;
            transition: all 0.3s ease;
            font-family: 'Orbitron', monospace;
        }

        .input-field:focus + .input-label,
        .input-field:not(:placeholder-shown) + .input-label {
            top: -10px;
            left: 15px;
            font-size: 0.8rem;
            color: #00f5ff;
            background: rgba(15,15,25,0.9);
            padding: 0 8px;
            border-radius: 5px;
        }

        /* Button */
        .cyber-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(45deg, #00f5ff, #0099ff);
            border: none;
            border-radius: 15px;
            color: #000;
            font-size: 1.1rem;
            font-weight: 700;
            font-family: 'Orbitron', monospace;
            cursor: pointer;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .cyber-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,245,255,0.4);
        }

        /* Messages */
        .message {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .error {
            background: rgba(255,50,50,0.2);
            border: 1px solid rgba(255,50,50,0.5);
            color: #ff6666;
        }

        .success {
            background: rgba(50,255,50,0.2);
            border: 1px solid rgba(50,255,50,0.5);
            color: #66ff66;
        }

        /* Links */
        .cyber-link {
            color: #00f5ff;
            text-decoration: none;
            font-weight: 500;
        }

        .cyber-link:hover {
            color: #ff00ff;
            text-shadow: 0 0 10px rgba(255,0,255,0.5);
        }

        /* Modal */
        .cyber-modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(15px);
            z-index: 1000;
        }

        .cyber-modal-card {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 380px;
            padding: 50px 30px;
            background: rgba(15,15,25,0.95);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(0,245,255,0.4);
            border-radius: 25px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.8), 0 0 100px rgba(0,245,255,0.2);
        }

        .close-btn {
            position: absolute;
            top: 20px; right: 25px;
            background: none;
            border: none;
            font-size: 2rem;
            color: rgba(255,255,255,0.5);
            cursor: pointer;
            width: 40px; height: 40px;
            border-radius: 50%;
        }

        .close-btn:hover {
            background: rgba(255,50,50,0.2);
            color: #ff6666;
        }

        @media (max-width: 480px) {
            .cyber-card { width: 95%; padding: 40px 25px; }
            .cyber-title { font-size: 2rem; }
        }
    </style>
</head>
<body>
    <!-- Background -->
    <div class="matrix-bg">
        <div class="hologrid"></div>
        <div class="particles" id="particles"></div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="cyberModal" class="cyber-modal">
        <div class="cyber-modal-card">
            <button class="close-btn" onclick="closeCyberModal()">&times;</button>
            <h2 style="color: #00f5ff; font-family: 'Orbitron', monospace; margin-bottom: 20px;">🔐 Forgot Password?</h2>
            <p style="color: rgba(255,255,255,0.8); margin-bottom: 25px;">Enter your email and we'll send you a reset link.</p>
            
            <?php if ($success): ?>
                <div class="message success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error && isset($_POST['action'])): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="forgot_password">
                <div class="input-group">
                    <input type="email" class="input-field" name="email" required placeholder=" ">
                    <label class="input-label">Email Address</label>
                </div>
                <button type="submit" class="cyber-btn">Send Reset Link</button>
            </form>

            <p style="text-align: center; margin-top: 25px;">
                <a href="#" class="cyber-link" onclick="closeCyberModal()">← Back to Login</a>
            </p>
        </div>
    </div>

    <!-- Main Login Card -->
    <div class="cyber-container">
        <div class="cyber-card">
            <div class="cyber-header">
                <h1 class="cyber-title">TICKETING SYSTEM</h1>
                <p class="cyber-subtitle">FOR THINCR EMPLOYEE ONLY</p>
            </div>

            <?php if ($error && !isset($_POST['action'])): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <input type="text" class="input-field" name="username" required placeholder=" ">
                    <label class="input-label">Username</label>
                </div>

                <div class="input-group">
                    <input type="password" class="input-field" name="password" required placeholder=" ">
                    <label class="input-label">Password</label>
                </div>

                <button type="submit" class="cyber-btn">LOGIN</button>
            </form>

            <div style="text-align: center; margin-top: 25px;">
                <a href="#" class="cyber-link" onclick="openCyberModal()" style="font-size: 0.95rem;">🔓 Forgot Password?</a>
            </div>

            <p style="text-align: center; margin-top: 30px; color: rgba(255,255,255,0.6); font-size: 0.9rem;">
                Don't have access? <a href="register.php" class="cyber-link">Create Account</a>
            </p>
        </div>
    </div>

    <script>
        // Floating Particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            for (let i = 0; i < 50; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.width = Math.random() * 6 + 2 + 'px';
                particle.style.height = particle.style.width;
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 4) + 's';
                particle.style.background = `linear-gradient(45deg, hsl(${Math.random()*360}, 100%, 50%), hsl(${Math.random()*360}, 100%, 70%))`;
                particlesContainer.appendChild(particle);
            }
        }

        // Modal Functions
        function openCyberModal() {
            document.getElementById('cyberModal').style.display = 'block';
        }

        function closeCyberModal() {
            document.getElementById('cyberModal').style.display = 'none';
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('cyberModal');
            if (event.target == modal) {
                closeCyberModal();
            }
        }

        // Initialize
        createParticles();
    </script>
</body>
</html>