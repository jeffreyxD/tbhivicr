<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST['title'];
    $desc = $_POST['description'];
    $priority   = $_POST['priority']; // NEW
    $user_id = $_SESSION['user_id'];

    $conn->query("INSERT INTO tickets (user_id,title,description)
                  VALUES ($user_id,'$title','$desc')");

    header("Location: dashboard.php");
}
?>

<h2>Submit Ticket</h2>

<form method="POST">
  <input name="title" placeholder="Title" required>
  <textarea name="description" placeholder="Description"></textarea>
  <button type="submit">Submit</button>
  <select name="priority" required>
  <option value="">Select Priority</option>
  <option value="High">🔴 High</option>
  <option value="Medium">🟡 Medium</option>
  <option value="Low">🟢 Low</option>
</select>
</form>
<a href = dashboard.php><button>Back</button></a>