<?php
session_start();
// Einbinden der Datenbankkonfiguration
require 'inc/db.php';

// Fehler- und Erfolgsmeldungen initialisieren
$error_message = "";
$success_message = "";

// Prüfen, ob Formulardaten per POST-Methode empfangen wurden
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Erfassen der Benutzereingaben
    $user = $_POST['Benutzername'];
    $pass = $_POST['password'];

    // Abfrage, um zu prüfen, ob Benutzername und Passwort in der Datenbank vorhanden sind
    $sql = "SELECT * FROM user_table WHERE name = '$user' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if ($pass === $row['password']) {
         
            $success_message = "Anmeldung erfolgreich!";
            
        } else {
            // Passwort ist inkorrekt
            $error_message = "Ungültiger Benutzername oder Passwort.";
        }
    } else {
        // Benutzer nicht gefunden
        $error_message = "Ungültiger Benutzername oder Passwort.";
    }
}
// Fetch to-do items for the logged-in user
$stmt = $conn->prepare("SELECT todo, Datum FROM todo_table WHERE UserId = ? ORDER BY Datum DESC");
$stmt->bind_param("i", $user);
$stmt->execute();
$result = $stmt->get_result();
$todos = $result->fetch_all(MYSQLI_ASSOC);

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

    // Fehlermeldung anzeigen, wenn die Anmeldung fehlschlägt
    if (isset($error_message)) {
    echo "<p style='color: red;'>$error_message</p>";
    }
 ?>

        <!-- Login form -->
        <form method="POST" action="">
            <label for="Benutzername">Benutzername:</label>
            <input type="text" name="Benutzername" placeholder="name" required>
            <label for="password">Password:</label>
            <input type="password" name="password" placeholder="" required>
            <button type="submit">-></button>
        </form>
    </div>

    <?php if (!empty($todos)) : ?>
        <ul>
            <?php foreach ($todos as $todo) : ?>
                <li>
                    <?php echo htmlspecialchars($todo['todo'], ENT_QUOTES, 'UTF-8'); ?> 
                    - <small><?php echo htmlspecialchars($todo['Datum'], ENT_QUOTES, 'UTF-8'); ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p>Keine Daten vorhanden.</p>
    <?php endif; ?>

    <h3>Neue Aufgabe hinzufügen</h3>
    <form method="POST" action="">
        <label for="todo">Aufgabe:</label>
        <input type="text" name="todo" id="todo" placeholder="Neue Aufgabe eingeben" required>
        <button type="submit">Hinzufügen</button>
    </form>

    <button id="logout-button">Abmelden</button>

    <script>
        document.getElementById('logout-button').addEventListener('click', () => {
            // Mock logout for SPA
            <?php session_destroy(); ?>
            loginForm.classList.remove('hidden');
            todoSection.classList.add('hidden');
        });
    </script>

</body>
</html>
