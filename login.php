<?php
session_start();

// Use Medoo to connect to the database
require 'vendor/autoload.php';
use Medoo\Medoo;

// Function to verify login
function verifyLogin($username, $password) {
    
    $stmt = $db->prepare("SELECT id, password_hash FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        return $user['id'];
    }
    return false;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

// Login handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $password = $_POST['password'];
        
        if ($user_id = verifyLogin($username, $password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['last_activity'] = time();
            header('Location: manage.php');
            exit;
        } else {
            $error = "Invalid credentials";
        }
    }
}

// Protect manage.php
if (basename($_SERVER['PHP_SELF']) === 'manage.php') {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    
    // Session timeout after 30 minutes of inactivity
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        session_unset();
        session_destroy();
        header('Location: login.php?msg=timeout');
        exit;
    }
    $_SESSION['last_activity'] = time();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
</body>
</html>