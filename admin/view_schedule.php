<?php
session_start();
require_once '../config/database.php';
require_once '../includes/Auth.php';
require_once '../includes/Schedule.php';

$database = new Database();
$db = $database->connect();
$auth = new Auth($db);

// Require admin authentication
$auth->requireAdmin();

$scheduleModel = new Schedule($db);

// Admin can view any date range
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d', strtotime('+7 days'));

// Get schedule for date range
$stmt = $scheduleModel->getByDateRange($start_date, $end_date);
$schedule_data = $stmt->fetchAll();

// Group by date
$dates_data = [];
foreach ($schedule_data as $item) {
    $date = $item['schedule_date'];
    if (!isset($dates_data[$date])) {
        $dates_data[$date] = [1 => [], 2 => [], 3 => []];
    }
    $dates_data[$date][$item['stream']][] = $item;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр графика (Админ)</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>
<body>
    <div class="container">
        <header class="admin-header">
            <h1>Просмотр графика (Администратор)</h1>
            <div class="admin-info">
                <a href="index.php" class="btn btn-small">← К панели управления</a>
                <a href="logout.php" class="btn btn-small">Выйти</a>
            </div>
        </header>
        
        <!-- Date Range Selector -->
        <div class="date-selector">
            <form method="GET" action="">
                <label for="start_date">С:</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                
                <label for="end_date">По:</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                
                <button type="submit" class="btn btn-primary">Показать</button>
                <a href="export_excel.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                   class="btn btn-success">Экспорт в Excel</a>
            </form>
        </div>
        
        <!-- Schedule Display -->
        <?php if (!empty($dates_data)): ?>
            <?php foreach ($dates_data as $date => $streams): ?>
            <div class="schedule-container">
                <h2>Распределение групп на обед <?php echo date('d.m.Y (l)', strtotime($date)); ?></h2>
                
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
                            <?php foreach ([1, 2, 3] as $stream_num): ?>
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
                                        $stream_grouped = [];
                                        foreach ($streams[$stream_num] as $item) {
                                            $time = substr($item['arrival_time'], 0, 5);
                                            $stream_grouped[$time][] = $item['group_name'];
                                        }
                                        
                                        foreach ($stream_grouped as $time => $groups):
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
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
        <div class="no-schedule">
            <p>График на выбранный период не сформирован.</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
