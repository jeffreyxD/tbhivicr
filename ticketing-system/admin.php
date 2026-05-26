<?php
session_start();
include 'config.php';

// ✅ SECURE QUERY (Prepared Statement)
$stmt = $conn->prepare("SELECT tickets.*, users.username, users.email 
                       FROM tickets 
                       JOIN users ON tickets.user_id = users.id 
                       ORDER BY tickets.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Rajdhani', sans-serif;
            background: linear-gradient(135deg, #0c0c0c 0%, #1a0033 50%, #0f0f23 100%);
            color: #e0e0e0;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Animated Background */
        .matrix-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.1;
        }

        .matrix-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120,119,198,0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(120,119,198,0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120,119,198,0.2) 0%, transparent 50%);
            animation: pulse 20s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }

        /* Header */
        .header {
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(20px);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(0, 255, 255, 0.3);
            box-shadow: 0 0 30px rgba(0, 255, 255, 0.1);
        }

        .logo {
            font-family: 'Orbitron', monospace;
            font-size: 28px;
            font-weight: 900;
            background: linear-gradient(45deg, #00ffff, #ff00ff, #00ff00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 20px rgba(0, 255, 255, 0.5);
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from { filter: drop-shadow(0 0 10px #00ffff); }
            to { filter: drop-shadow(0 0 20px #ff00ff); }
        }

        .logout-btn {
            background: linear-gradient(45deg, #ff0040, #ff4080);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 0, 64, 0.3);
            position: relative;
            overflow: hidden;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 0, 64, 0.5);
        }

        /* Stats Cards */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(0, 255, 255, 0.2);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }

        .stat-card:hover::before {
            left: 100%;
        }

        .stat-card:hover {
            transform: translateY(-10px) scale(1.02);
            border-color: #00ffff;
            box-shadow: 0 20px 40px rgba(0, 255, 255, 0.2);
        }

        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #00ffff, #ff00ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Table Container */
        .table-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px 40px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .table-title {
            font-family: 'Orbitron', monospace;
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(45deg, #00ffff, #ffffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Futuristic Table */
        .tickets-table {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid rgba(0, 255, 255, 0.1);
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .table-head {
            background: linear-gradient(90deg, rgba(0, 255, 255, 0.1), rgba(255, 0, 255, 0.1));
            border-bottom: 1px solid rgba(0, 255, 255, 0.2);
        }

        .table-head th {
            padding: 20px 15px;
            text-align: left;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #00ffff;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table-row {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            animation: slideIn 0.6s ease forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        .table-row:nth-child(even) {
            background: rgba(0, 255, 255, 0.02);
        }

        .table-row:hover {
            background: rgba(0, 255, 255, 0.1);
            transform: scale(1.01);
            box-shadow: 0 10px 30px rgba(0, 255, 255, 0.1);
        }

        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .table-row td {
            padding: 20px 15px;
            vertical-align: middle;
        }

        /* Status Badge */
        .status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status.open { background: rgba(0, 255, 0, 0.2); color: #00ff00; border: 1px solid #00ff00; }
        .status.closed { background: rgba(255, 0, 0, 0.2); color: #ff4444; border: 1px solid #ff4444; }

        /* Action Buttons */
        .action-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
            font-size: 13px;
        }

        .btn-close {
            background: linear-gradient(45deg, #ff0040, #ff4080);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 0, 64, 0.3);
        }

        .btn-close:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 0, 64, 0.5);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header { padding: 15px 20px; flex-direction: column; gap: 15px; }
            .stats { grid-template-columns: 1fr; padding: 20px; }
            .table-container { padding: 0 20px 20px; }
            .tickets-table { font-size: 14px; }
            .table-head th, .table-row td { padding: 15px 10px; }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(0, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #00ffff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Matrix Background -->
    <canvas class="matrix-bg" id="matrix"></canvas>

    <!-- Header -->
    <div class="header">
        <div class="logo">
            <i class="fas fa-ticket-alt"></i> TICKET SYSTEM
        </div>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-power-off"></i> Logout
        </a>
    </div>

    <!-- Stats Cards -->
    <?php
    $open_tickets = $conn->query("SELECT COUNT(*) as count FROM tickets WHERE status='open'")->fetch_assoc()['count'];
    $total_tickets = $conn->query("SELECT COUNT(*) as count FROM tickets")->fetch_assoc()['count'];
    $users_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
    ?>

    <div class="stats">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-ticket-open"></i></div>
            <h3>Open Tickets</h3>
            <div style="font-size: 36px; font-weight: 900; color: #00ff00; margin-top: 10px;">
                <?php echo $open_tickets; ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
            <h3>Total Tickets</h3>
            <div style="font-size: 36px; font-weight: 900; color: #00ffff; margin-top: 10px;">
                <?php echo $total_tickets; ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <h3>Total Users</h3>
            <div style="font-size: 36px; font-weight: 900; color: #ff00ff; margin-top: 10px;">
                <?php echo $users_count; ?>
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="table-container">
        <div class="table-header">
            <h1 class="table-title">
                <i class="fas fa-list"></i> All Tickets
            </h1>
            <div style="font-size: 18px; color: #888;">
                <?php
                  date_default_timezone_set('Asia/Manila');
                  echo date('F j, Y \a\t g:i A');
                ?>
            </div>
        </div>

        <table class="tickets-table">
            <thead class="table-head">
                <tr>
                    <th><i class="fas fa-user"></i> User</th>
                    <th><i class="fas fa-heading"></i> Title</th>
                    <th><i class="fas fa-align-left"></i> Description</th>
                    <th><i class="fas fa-clock"></i> Created</th>
                    <th><i class="fas fa-tag"></i> Status</th>
                    <th><i class="fas fa-cogs"></i> Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr class="table-row">
                    <td>
                        <strong><?php echo htmlspecialchars($row['username']); ?></strong>
                        <br><small><?php echo htmlspecialchars($row['email']); ?></small>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                    </td>
                    <td><?php echo substr(htmlspecialchars($row['description']), 0, 100); ?>...</td>
                    <td><small><?php echo date('M j, Y', strtotime($row['created_at'])); ?></small></td>
                    <td><span class="status <?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                    <td>
                        <?php if($row['status'] == 'open'): ?>
                            <a href="?close=<?php echo $row['id']; ?>" 
                               class="action-btn btn-close" 
                               onclick="return confirm('Close this ticket?')">
                                <i class="fas fa-times"></i> Close
                            </a>
                        <?php else: ?>
                            <span style="color: #666; font-size: 12px;">Closed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Matrix Rain Effect
        const canvas = document.getElementById('matrix');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const chars = '01アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン';
        const fontSize = 14;
        const columns = canvas.width / fontSize;
        const drops = Array(Math.floor(columns)).fill(1);

        function drawMatrix() {
            ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            ctx.fillStyle = '#00ffff';
            ctx.font = `${fontSize}px monospace`;
            
            drops.forEach((y, i) => {
                const text = chars[Math.floor(Math.random() * chars.length)];
                ctx.fillText(text, i * fontSize, y * fontSize);
                
                if (y * fontSize > canvas.height && Math.random() > 0.975) {
                    drops[i] = 0;
                }
                drops[i]++;
            });
        }

        setInterval(drawMatrix, 50);

        // Animate table rows
        document.querySelectorAll('.table-row').forEach((row, index) => {
            row.style.animationDelay = `${index * 0.1}s`;
        });

        // Responsive canvas
        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });
    </script>
</body>
</html>

<?php
// Handle ticket closing (SECURE)
if (isset($_GET['close'])) {
    $id = (int)$_GET['close'];
    $stmt = $conn->prepare("UPDATE tickets SET status='closed' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin.php");
    exit();
}
?>