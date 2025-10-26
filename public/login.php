<?php
session_start();
require_once '../config/database.php';
require_once '../includes/Auth.php';

$database = new Database();
$db = $database->connect();
$auth = new Auth($db);

$error = '';

// If already logged in, redirect to admin panel
if ($auth->isLoggedIn()) {
    header('Location: ../admin/index.php');
    exit();
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->login($username, $password)) {
        header('Location: ../admin/index.php');
        exit();
    } else {
        $error = 'Неверный логин или пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Система управления столовой</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Вход в систему</h1>
            <p class="subtitle">Система управления графиком столовой</p>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Логин:</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Войти</button>
            </form>
            
            <div class="login-footer">
                <a href="index.php">← Вернуться к публичному графику</a>
            </div>
        </div>
    </div>
</body>
</html>
