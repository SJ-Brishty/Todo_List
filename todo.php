<?php

require 'inc/db.php';
session_start();

// Handle login form submission
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query to find the user by username and password
    $sql = "SELECT id, name FROM user_table WHERE name = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $stmt->store_result(); // Store result so we can check the number of rows

    if ($stmt->num_rows > 0) {
        // Login successful, store the user id in session
        $stmt->bind_result($user_id, $user_name); // Bind the result variables
        $stmt->fetch(); // Fetch the data
        $_SESSION['userid'] = $user_id;
        $_SESSION['username'] = $user_name;
        //$message = "Login successful";
    } else {
        $message = "Invalid username or password";
    }
}

// Handle logout
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    $message = "You have logged out successfully";
}

// Handle adding a new todo
if (isset($_POST['add_todo']) && isset($_SESSION['userid'])) {
    $todo = $_POST['todo'];
    $userid = $_SESSION['userid'];
    $todo_date = date('Y-m-d');  // Get today's date in YYYY-MM-DD format

    // Insert the new todo into the todo_table
    $sql = "INSERT INTO todo_table (UserId, todo, Datum) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $userid, $todo, $todo_date);
    
    if ($stmt->execute()) {
        $message = "To-do added successfully!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $message = "Error adding to-do.";
    }
}

// Delete all todo entries
if (isset($_POST['DelAll']) && isset($_SESSION['userid'])) {
    //$todo = $_POST['todo'];
    $userid = $_SESSION['userid'];
    //$todo_date = date('Y-m-d');  // Get today's date in YYYY-MM-DD format

    // Insert the new todo into the todo_table
    $sql = "DELETE FROM todo_table WHERE UserId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userid);
    
    if ($stmt->execute()) {
        $message = "Alle To-Dos gelöscht!";
    } else {
        $message = "Fehler beim löschen!";
    }
}

// Delete single to by clicking
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

// Retrieve todos if logged in
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do WebApp</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
     <!--<h1>Todo Application</h1>-->

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
    

       
        <!-- Form to Add a New Todo -->
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

            if (confirm('Are you sure you want to delete this todo?')) {
                $.ajax({
                    url: 'todo.php',
                    type: 'POST',
                    data: { id: todoId },
                    success: function(response) {
                        if (response.trim() === "success") {
                            alert('To-Do deleted successfully!');
                            location.reload();
                        } else {
                            alert('Error deleting To-Do. Please try again.');
                        }
                    }
                });
            }
        });
    </script>   

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>

