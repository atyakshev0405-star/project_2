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
$scheduleGenerator = new ScheduleGenerator($db);
$scheduleModel = new Schedule($db);
$groupModel = new Group($db);

// Get today's date and next week
$today = date('Y-m-d');
$next_week_end = date('Y-m-d', strtotime('+7 days'));

// Default view: today's schedule
$view_date = $_GET['date'] ?? $today;
$schedule_data = [];

// Get schedule for the view date
$stmt = $scheduleModel->getByDate($view_date);
$schedule_data = $stmt->fetchAll();

// Group by stream
$streams = [1 => [], 2 => [], 3 => []];
foreach ($schedule_data as $item) {
    $streams[$item['stream']][] = $item;
}

// Get all groups for search
$stmt = $groupModel->getAll();
$all_groups = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>График посещения столовой</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>
<body>
    <div class="container">
        <header class="public-header">
            <h1>График посещения столовой</h1>
            <div class="header-actions">
                <?php if ($auth->isLoggedIn()): ?>
                    <a href="../admin/index.php" class="btn btn-small">Админ-панель</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-small">Вход для администратора</a>
                <?php endif; ?>
            </div>
        </header>
        
        <!-- Date Selector -->
        <div class="date-selector">
            <form method="GET" action="">
                <label for="date">Выберите дату:</label>
                <input type="date" id="date" name="date" value="<?php echo $view_date; ?>" 
                       min="<?php echo $today; ?>" max="<?php echo $next_week_end; ?>">
                <button type="submit" class="btn btn-primary">Показать</button>
            </form>
        </div>
        
        <!-- Schedule Table -->
        <?php if (!empty($schedule_data)): ?>
        <div class="schedule-container">
            <h2>Распределение групп на обед <?php echo date('d.m.Y', strtotime($view_date)); ?></h2>
            
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>1 поток групп<br><small>с 11:35 до 12:15</small></th>
                        <th>2 поток групп<br><small>с 13:00 до 13:30</small></th>
                        <th>3 поток групп<br><small>с 14:15 до 14:30</small></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <!-- Stream 1 -->
                        <td class="stream-column">
                            <table class="stream-table">
                                <thead>
                                    <tr>
                                        <th>Время прихода</th>
                                        <th>Группы</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stream1_grouped = [];
                                    foreach ($streams[1] as $item) {
                                        $time = substr($item['arrival_time'], 0, 5);
                                        $stream1_grouped[$time][] = $item['group_name'];
                                    }
                                    
                                    foreach ($stream1_grouped as $time => $groups):
                                    ?>
                                    <tr>
                                        <td class="time-cell"><?php echo $time; ?></td>
                                        <td class="group-cell">
                                            <?php foreach ($groups as $group): ?>
                                                <div><?php echo htmlspecialchars($group); ?></div>
                                            <?php endforeach; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </td>
                        
                        <!-- Stream 2 -->
                        <td class="stream-column">
                            <table class="stream-table">
                                <thead>
                                    <tr>
                                        <th>Время прихода</th>
                                        <th>Группы</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stream2_grouped = [];
                                    foreach ($streams[2] as $item) {
                                        $time = substr($item['arrival_time'], 0, 5);
                                        $stream2_grouped[$time][] = $item['group_name'];
                                    }
                                    
                                    foreach ($stream2_grouped as $time => $groups):
                                    ?>
                                    <tr>
                                        <td class="time-cell"><?php echo $time; ?></td>
                                        <td class="group-cell">
                                            <?php foreach ($groups as $group): ?>
                                                <div><?php echo htmlspecialchars($group); ?></div>
                                            <?php endforeach; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </td>
                        
                        <!-- Stream 3 -->
                        <td class="stream-column">
                            <table class="stream-table">
                                <thead>
                                    <tr>
                                        <th>Время прихода</th>
                                        <th>Группы</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stream3_grouped = [];
                                    foreach ($streams[3] as $item) {
                                        $time = substr($item['arrival_time'], 0, 5);
                                        $stream3_grouped[$time][] = $item['group_name'];
                                    }
                                    
                                    foreach ($stream3_grouped as $time => $groups):
                                    ?>
                                    <tr>
                                        <td class="time-cell"><?php echo $time; ?></td>
                                        <td class="group-cell">
                                            <?php foreach ($groups as $group): ?>
                                                <div><?php echo htmlspecialchars($group); ?></div>
                                            <?php endforeach; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="no-schedule">
            <p>График на выбранную дату не сформирован.</p>
            <p>Обратитесь к администратору для формирования графика.</p>
        </div>
        <?php endif; ?>
        
        <!-- Group Search -->
        <div class="search-section">
            <h2>Поиск времени приема пищи</h2>
            <form method="GET" action="search.php" class="search-form">
                <div class="form-group">
                    <label for="search_group">Выберите группу:</label>
                    <select id="search_group" name="group_id" required>
                        <option value="">-- Выберите группу --</option>
                        <?php foreach ($all_groups as $group): ?>
                        <option value="<?php echo $group['id']; ?>">
                            <?php echo htmlspecialchars($group['group_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="search_date">Дата:</label>
                    <input type="date" id="search_date" name="date" required
                           value="<?php echo $today; ?>"
                           min="<?php echo $today; ?>" max="<?php echo $next_week_end; ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">Найти</button>
            </form>
        </div>
    </div>
</body>
</html>
