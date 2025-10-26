<?php
session_start();
require_once '../config/database.php';
require_once '../includes/Schedule.php';
require_once '../includes/Group.php';

$database = new Database();
$db = $database->connect();
$scheduleModel = new Schedule($db);
$groupModel = new Group($db);

$result = null;
$group_name = '';

if (isset($_GET['group_id']) && isset($_GET['date'])) {
    $group_id = $_GET['group_id'];
    $date = $_GET['date'];
    
    // Get group info
    $group = $groupModel->getById($group_id);
    if ($group) {
        $group_name = $group['group_name'];
        
        // Get schedule for this group and date
        $result = $scheduleModel->getByGroupAndDate($group_id, $date);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результат поиска - График столовой</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>
<body>
    <div class="container">
        <header class="public-header">
            <h1>Результат поиска</h1>
        </header>
        
        <div class="search-result">
            <?php if ($result): ?>
                <div class="result-card success">
                    <h2>Группа: <?php echo htmlspecialchars($group_name); ?></h2>
                    <p class="result-date">Дата: <?php echo date('d.m.Y', strtotime($_GET['date'])); ?></p>
                    
                    <div class="result-info">
                        <div class="info-item">
                            <span class="label">Время приема пищи:</span>
                            <span class="value"><?php echo substr($result['arrival_time'], 0, 5); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="label">Поток:</span>
                            <span class="value">
                                <?php 
                                $stream_times = [
                                    1 => '11:35 - 12:15',
                                    2 => '13:00 - 13:30',
                                    3 => '14:15 - 14:30'
                                ];
                                echo $result['stream'] . ' поток (' . $stream_times[$result['stream']] . ')';
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="result-card error">
                    <h2>График не найден</h2>
                    <p>Для группы "<?php echo htmlspecialchars($group_name); ?>" на дату <?php echo date('d.m.Y', strtotime($_GET['date'])); ?> график не сформирован.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="back-link">
            <a href="index.php" class="btn btn-secondary">← Вернуться к графику</a>
        </div>
    </div>
</body>
</html>
