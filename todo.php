<?php

require 'inc/db.php';
session_start();

// Verarbeitung von Anmeldeformularen
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $sql = "SELECT id, name FROM user_table WHERE name = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $user_name);
        $stmt->fetch();
        $_SESSION['userid'] = $user_id;
        $_SESSION['username'] = $user_name;
        } else {
        $message = "Üngultiger Benutzername oder Passwort!";
    }
}

//Abmelden
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    $message = "Erfolgreich abgemeldet!";
}

// Neue Eintrag in die todo_table einfügen
if (isset($_POST['add_todo']) && isset($_SESSION['userid'])) {
    $todo = $_POST['todo'];
    $userid = $_SESSION['userid'];
    $todo_date = date('Y-m-d');      
    $sql = "INSERT INTO todo_table (UserId, todo, Datum) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $userid, $todo, $todo_date);
    
    if ($stmt->execute()) {
        $message = "To-do erfolgreich hinzugefügt!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $message = "Fehler beim hinfügen!";
    }
}

// Alle Einträge löschen
if (isset($_POST['DelAll']) && isset($_SESSION['userid'])) {
    $userid = $_SESSION['userid'];
    $sql = "DELETE FROM todo_table WHERE UserId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userid);
    
    if ($stmt->execute()) {
        $message = "Alle To-Dos gelöscht!";
    } else {
        $message = "Fehler beim löschen!";
    }
}

// Löschen von einzelne Eintrag durch Klicken auf
if (isset($_POST['id']) && isset($_SESSION['userid'])) {
    $id = $_POST['id'];
    $userid = $_SESSION['userid'];

    $sql = "DELETE FROM todo_table WHERE id = ? AND UserId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $userid);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
    exit;
}

// Aufgaben abrufen, wenn angemeldet
$todos_result = [];
if (isset($_SESSION['userid'])) {
    $userid = $_SESSION['userid'];
    $sql = "SELECT id, todo, Datum FROM todo_table WHERE UserId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $todos_result[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do WebApp</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
     
    <?php if (isset($message)) { echo "<p>$message</p>"; } ?>

    <?php if (!isset($_SESSION['userid'])) { ?>
        <!-- Login Form -->
        <form action="todo.php" method="POST">
            <label for="username">Benutzername:</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit" name="login">&#10144</button>
        </form> 
        <?php } ?>
    
        <!-- Formular zum Hinzufügen eines neuen Todos -->
        <?php if (isset($_SESSION['userid'])) { ?>
        <form action="todo.php" method="POST">
            <label for="todo"></label>
            <input type="text" placeholder="To-Do eintragen..." name="todo" id="todo" required>

            <button type="submit" name="add_todo">&#10144</button>
        </form>

        <?php if (count($todos_result) > 0) { ?>
            <ul>
                <?php foreach ($todos_result as $todo_data) { ?>
                <ul>
                    <a href="#" class="delete-btn" data-id="<?php echo $todo_data['id']; ?>">×</a>  
                    <?php echo htmlspecialchars($todo_data['Datum']) . " - " . htmlspecialchars($todo_data['todo']); ?>
                </ul>  
                <?php } ?>
            </ul>
        <?php } else {
            echo "<p>Keine Einträge</p>";
        }
        ?>
        <div style="display: flex; gap: 10px;">
        <!-- Alle To-Dos Löschen Button -->
        <form action="todo.php" method="POST">
            <button type="submit" name="DelAll">alle TO-DOs löschen</button>
        </form>

             
        <!-- Logout Button -->
        
        <form action="todo.php" method="POST">
            <button type="submit" name="logout">Logout</button>
        </form> </div>
        <?php } ?>

    <script>
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            const todoId = $(this).data('id');

            if (confirm('Möchten Sie diese Eintrag wirklich löschen?')) {
                $.ajax({
                    url: 'todo.php',
                    type: 'POST',
                    data: { id: todoId },
                    success: function(response) {
                        if (response.trim() === "success") {
                            alert('Ausgewählter Eintrag erfolgreich gelöscht!');
                            location.reload();
                        } else {
                            alert('Fehler beim löschen! Bitte erneut versuchen!');
                        }
                    }
                });
            }
        });
    </script>   

</body>
</html>

<?php
// Datenbankverbindung schließen
$conn->close();
?>

