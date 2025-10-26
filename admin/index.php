<?php
session_start();
require_once '../config/database.php';
require_once '../includes/Auth.php';
require_once '../includes/Group.php';
require_once '../includes/Schedule.php';
require_once '../includes/ScheduleGenerator.php';

$database = new Database();
$db = $database->connect();
$auth = new Auth($db);

// Require admin authentication
$auth->requireAdmin();

$groupModel = new Group($db);
$scheduleGenerator = new ScheduleGenerator($db);

// Handle form submissions
$message = '';
$error = '';

// Add new group
if (isset($_POST['add_group'])) {
    $groupModel->group_name = $_POST['group_name'];
    $groupModel->student_count = rand(20, 30); // Random count between 20-30
    $groupModel->stream = $_POST['stream'];
    
    if ($groupModel->create()) {
        $message = 'Группа успешно добавлена';
    } else {
        $error = 'Ошибка при добавлении группы';
    }
}

// Delete group
if (isset($_POST['delete_group'])) {
    $groupModel->id = $_POST['group_id'];
    if ($groupModel->delete()) {
        $message = 'Группа удалена';
    } else {
        $error = 'Ошибка при удалении группы';
    }
}

// Generate schedule
if (isset($_POST['generate_schedule'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    if ($scheduleGenerator->generateWeekSchedule($start_date, $end_date)) {
        $message = 'График успешно сформирован';
    } else {
        $error = 'Ошибка при формировании графика';
    }
}

// Get all groups
$stmt = $groupModel->getAll();
$groups = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - Система управления столовой</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>
<body>
    <div class="container">
        <header class="admin-header">
            <h1>Панель администратора</h1>
            <div class="admin-info">
                <span>Пользователь: <?php echo $_SESSION['admin_username']; ?></span>
                <a href="logout.php" class="btn btn-small">Выйти</a>
            </div>
        </header>
        
        <?php if ($message): ?>
            <div class="success-message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="admin-grid">
            <!-- Add Group Section -->
            <div class="admin-card">
                <h2>Добавить группу</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="group_name">Название группы:</label>
                        <input type="text" id="group_name" name="group_name" required 
                               placeholder="Например: СИП-113/25">
                    </div>
                    
                    <div class="form-group">
                        <label for="stream">Поток:</label>
                        <select id="stream" name="stream" required>
                            <option value="1">1 поток (11:35-12:15)</option>
                            <option value="2">2 поток (13:00-13:30)</option>
                            <option value="3">3 поток (14:15-14:30)</option>
                        </select>
                    </div>
                    
                    <p class="info-text">* Количество студентов будет назначено случайно от 20 до 30</p>
                    
                    <button type="submit" name="add_group" class="btn btn-primary">Добавить группу</button>
                </form>
            </div>
            
            <!-- Generate Schedule Section -->
            <div class="admin-card">
                <h2>Сформировать график</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="start_date">Начальная дата (Понедельник):</label>
                        <input type="date" id="start_date" name="start_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">Конечная дата (Пятница):</label>
                        <input type="date" id="end_date" name="end_date" required>
                    </div>
                    
                    <button type="submit" name="generate_schedule" class="btn btn-success">СФОРМИРОВАТЬ ГРАФИК</button>
                </form>
                
                <div class="stream-info">
                    <h3>Расписание потоков:</h3>
                    <ul>
                        <li><strong>1 поток:</strong> 11:35 - 12:15</li>
                        <li><strong>2 поток:</strong> 13:00 - 13:30</li>
                        <li><strong>3 поток:</strong> 14:15 - 14:30</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Groups List -->
        <div class="admin-card">
            <h2>Список групп (<?php echo count($groups); ?>)</h2>
            <div class="groups-list">
                <?php 
                $streams = [1 => [], 2 => [], 3 => []];
                foreach ($groups as $group) {
                    $streams[$group['stream']][] = $group;
                }
                
                foreach ([1, 2, 3] as $stream_num):
                ?>
                    <div class="stream-section">
                        <h3>Поток <?php echo $stream_num; ?> (<?php echo count($streams[$stream_num]); ?> групп)</h3>
                        <table class="groups-table">
                            <thead>
                                <tr>
                                    <th>Название группы</th>
                                    <th>Количество студентов</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($streams[$stream_num] as $group): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($group['group_name']); ?></td>
                                    <td><?php echo $group['student_count']; ?></td>
                                    <td>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                                            <button type="submit" name="delete_group" class="btn btn-danger btn-small"
                                                    onclick="return confirm('Удалить группу?')">Удалить</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="admin-links">
            <a href="../public/index.php" class="btn btn-secondary">Просмотреть публичный график</a>
            <a href="view_schedule.php" class="btn btn-secondary">Просмотреть график (Админ)</a>
        </div>
    </div>
</body>
</html>
