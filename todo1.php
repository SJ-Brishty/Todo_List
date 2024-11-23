<?php
session_start();
require 'inc/db.php';

// Initialize response variables
$response = ["error" => false, "message" => "", "data" => null];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    try {
        $action = $_POST['action'] ?? '';

        // Login Action
        if ($action === 'login') {
            $user = $_POST['username'];
            $pass = $_POST['password'];

            $stmt = $conn->prepare("SELECT * FROM user_table WHERE name = ? LIMIT 1");
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($pass === $row['password']) {
                    $_SESSION['user_id'] = $row['id'];
                    $response['message'] = "Anmeldung erfolgreich!";
                } else {
                    $response['error'] = true;
                    $response['message'] = "Ungültiger Benutzername oder Passwort.";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Ungültiger Benutzername oder Passwort.";
            }
            echo json_encode($response);
            exit();
        }

        // Add To-Do Item
        if ($action === 'add_todo' && isset($_SESSION['user_id'])) {
            $todo = trim($_POST['todo']);
            if (empty($todo)) {
                $response['error'] = true;
                $response['message'] = "Die Aufgabe darf nicht leer sein.";
            } else {
                $stmt = $conn->prepare("INSERT INTO todo_table (todo, UserId, Datum) VALUES (?, ?, CURDATE())");
                $stmt->bind_param("si", $todo, $_SESSION['user_id']);
                if ($stmt->execute()) {
                    $response['message'] = "Aufgabe hinzugefügt!";
                } else {
                    $response['error'] = true;
                    $response['message'] = "Fehler beim Hinzufügen der Aufgabe.";
                }
            }
        }
        
        
    } catch (Exception $e) {
        $response['error'] = true;
        $response['message'] = "Serverfehler: " . $e->getMessage();
    }

    echo json_encode($response);
    exit();
}

// Fetch To-Do Items
$todos = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT todo, Datum FROM todo_table WHERE UserId = ? ORDER BY Datum DESC");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $todos = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & To-Do</title>
    <style>
        .hidden { display: none; }
    </style>
</head>
<body>
    <div id="app">
        <!-- Login Form -->
        <div id="login-form" <?php echo isset($_SESSION['user_id']) ? 'class="hidden"' : ''; ?>>
            <h3>Login</h3>
            <input type="text" id="username" placeholder="Benutzername" required>
            <input type="password" id="password" placeholder="Passwort" required>
            <button id="login-button">Anmelden</button>
            <p id="login-message"></p>
        </div>

        <!-- To-Do Section -->
        <div id="todo-section" <?php echo !isset($_SESSION['user_id']) ? 'class="hidden"' : ''; ?>>
            <h3>To-Do Liste</h3>
            <ul id="todo-list">
            <?php if (!empty($todos)) : ?>
                <ul>
                <?php foreach ($todos as $todo): ?>
                    <li>
                        <?php echo htmlspecialchars($todo['todo'], ENT_QUOTES, 'UTF-8'); ?>
                        - <small><?php echo htmlspecialchars($todo['Datum'], ENT_QUOTES, 'UTF-8'); ?></small>
                    </li>
                <?php endforeach; ?>

             </ul>
                <?php else : ?>
                    <p>Keine Daten vorhanden.</p>
                <?php endif; ?>
         </ul>
            <input type="text" id="new-todo" placeholder="Neue Aufgabe">
            <button id="add_todo_button">Hinzufügen</button>
            <button id="logout-button">Abmelden</button>
            <p id="todo-message"></p>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('login-form');
        const todoSection = document.getElementById('todo-section');
        const loginMessage = document.getElementById('login-message');
        const todoMessage = document.getElementById('todo-message');
        const todoList = document.getElementById('todo-list');

        document.getElementById('login-button').addEventListener('click', async () => {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            const response = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'login', username, password })
            });

            const result = await response.json();
            if (result.error) {
                loginMessage.textContent = result.message;
                loginMessage.style.color = 'red';
            } else {
                loginMessage.textContent = "";
                loginForm.classList.add('hidden');
                todoSection.classList.remove('hidden');
                fetchTodos();
            }
        });

        async function fetchTodos() {
            const response = await fetch('');
            const result = await response.json();

            todoList.innerHTML = "";
            result.data.forEach(item => {
                const li = document.createElement('li');
                li.textContent = ${item.todo} - ${item.Datum};
                todoList.appendChild(li);
            });
        }

        document.getElementById('add_todo_button').addEventListener('click', async () => {
            const todo = document.getElementById('new-todo').value;

            const response = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'add_todo', todo })
            });
            const result = await response.json();
            todoMessage.textContent = result.message;
            if (!result.error) {
                fetchTodos();
            }
            
            
        });
        

        document.getElementById('logout-button').addEventListener('click', () => {
            // Mock logout for SPA
            <?php session_destroy(); ?>
            loginForm.classList.remove('hidden');
            todoSection.classList.add('hidden');
        });
    </script>
</body>
</html>