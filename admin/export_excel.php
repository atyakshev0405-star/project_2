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

// Get date range from query parameters
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d', strtotime('+7 days'));

// Get schedule data
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

// Set headers for Excel download
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=schedule_" . $start_date . "_to_" . $end_date . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Output BOM for UTF-8
echo "\xEF\xBB\xBF";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; font-weight: bold; }
        .stream-header { background-color: #2196F3; color: white; font-weight: bold; }
        .time-cell { font-weight: bold; }
    </style>
</head>
<body>
    <h1>График посещения столовой</h1>
    <p>Период: <?php echo date('d.m.Y', strtotime($start_date)); ?> - <?php echo date('d.m.Y', strtotime($end_date)); ?></p>
    
    <?php foreach ($dates_data as $date => $streams): ?>
    <h2>Распределение групп на обед <?php echo date('d.m.Y (l)', strtotime($date)); ?></h2>
    
    <table>
        <thead>
            <tr>
                <th class="stream-header">1 поток групп<br>с 11:35 до 12:15</th>
                <th class="stream-header">2 поток групп<br>с 13:00 до 13:30</th>
                <th class="stream-header">3 поток групп<br>с 14:15 до 14:30</th>
            </tr>
            <tr>
                <th>Время прихода | Группы</th>
                <th>Время прихода | Группы</th>
                <th>Время прихода | Группы</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Prepare data for all streams
            $max_rows = 0;
            $stream_rows = [];
            
            foreach ([1, 2, 3] as $stream_num) {
                $stream_grouped = [];
                foreach ($streams[$stream_num] as $item) {
                    $time = substr($item['arrival_time'], 0, 5);
                    if (!isset($stream_grouped[$time])) {
                        $stream_grouped[$time] = [];
                    }
                    $stream_grouped[$time][] = $item['group_name'];
                }
                
                $stream_rows[$stream_num] = [];
                foreach ($stream_grouped as $time => $groups) {
                    $stream_rows[$stream_num][] = [
                        'time' => $time,
                        'groups' => $groups
                    ];
                }
                
                $max_rows = max($max_rows, count($stream_rows[$stream_num]));
            }
            
            // Output rows
            for ($i = 0; $i < $max_rows; $i++):
            ?>
            <tr>
                <?php foreach ([1, 2, 3] as $stream_num): ?>
                <td>
                    <?php if (isset($stream_rows[$stream_num][$i])): ?>
                        <strong><?php echo $stream_rows[$stream_num][$i]['time']; ?></strong> | 
                        <?php echo implode(', ', array_map('htmlspecialchars', $stream_rows[$stream_num][$i]['groups'])); ?>
                    <?php endif; ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>
    <br><br>
    <?php endforeach; ?>
</body>
</html>
