<?php
session_start();
// Include the database configuration
require 'inc/db.php';

// Initialize messages
$error_message = "";
$success_message = "";

// Check if form data has been received via POST method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect user input
    $user = $_POST['Benutzername'];
    $pass = $_POST['password'];

    // Use prepared statement to prevent SQL injection
    $sql = "SELECT * FROM user_table WHERE name = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user); // Bind username as a string
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists and verify password
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // If password matches
        if ($pass === $row['password']) {
            $_SESSION['user_id'] = $row['id']; // Store user ID in session
            $success_message = "Anmeldung erfolgreich!";
        } else {
            $error_message = "Ungültiger Benutzername oder Passwort.";
        }
    } else {
        // User not found
        $error_message = "Ungültiger Benutzername oder Passwort.";
    }
}

// Fetch to-do items for the logged-in user
$todos = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT todo, Datum FROM todo_table WHERE UserId = ? ORDER BY Datum DESC");
    $stmt->bind_param("s", $user_id); // Bind user ID as an integer
    $stmt->execute();
    $result = $stmt->get_result();
    $todos = $result->fetch_all(MYSQLI_ASSOC);
}

//

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
</head>
<body>

    <div class="form-container">
        <?php 
        if (isset($success_message)) {
            echo "<p style='color: green;'>$success_message</p>";
        }

        if (isset($error_message)) {
            echo "<p style='color: red;'>$error_message</p>";
        }
        ?>

        <!-- Login form -->
        <form method="POST" action="">
            <label for="Benutzername">Benutzername:</label>
            <input type="text" name="Benutzername" placeholder="name" required>
            <label for="password">Passwort:</label>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">-></button>
        </form>
    </div>

    <!-- Display To-Do List for Logged-In User -->
    <?php
    
    if (isset($_SESSION['user_id'])): ?>
        <h2>Ihre To-Do Liste:</h2>
        <ul>
            <?php foreach ($todos as $todo): ?>
                <li><?= htmlspecialchars($todo['todo']) ?> (<?= htmlspecialchars($todo['Datum']) ?>)</li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    
       <!-- Logout Form -->
    <form method="POST" action="logout.php">
        <button type="submit">Abmelden</button>
    </form>
    
</body>
</html>
