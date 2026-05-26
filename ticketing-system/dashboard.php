<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'];

$result = $conn->query("SELECT * FROM tickets WHERE user_id=$user_id");
?>

<h2>Your Tickets</h2>
<a href="submit_ticket.php"><button>Create Ticket</button></a>
<a href="logout.php">Logout</a>

<table border="1">
<tr>
  <th>Title</th>
  <th>Urgency</th>
  <th>Status</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
  <td><?= $row['title'] ?></td>
  <td><?= $row['priority'] ?></td>
  <td><?= $row['status'] ?></td>
</tr>
<?php endwhile; ?>

</table>