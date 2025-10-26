<?php
// Schedule Generator with rotation algorithm

class ScheduleGenerator {
    private $conn;
    
    // Stream time configurations
    private $stream_config = [
        1 => [
            'start_time' => '11:35',
            'end_time' => '12:15',
            'interval' => 5,
            'max_groups_per_slot' => 3
        ],
        2 => [
            'start_time' => '13:00',
            'end_time' => '13:30',
            'interval' => 5,
            'max_groups_per_slot' => 3
        ],
        3 => [
            'start_time' => '14:15',
            'end_time' => '14:30',
            'interval' => 5,
            'max_groups_per_slot' => 3
        ]
    ];
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Generate schedule for a week with rotation
     * @param string $start_date Start date (Monday)
     * @param string $end_date End date (Friday)
     * @return bool Success status
     */
    public function generateWeekSchedule($start_date, $end_date) {
        try {
            // Get all groups grouped by stream
            $groupModel = new Group($this->conn);
            $scheduleModel = new Schedule($this->conn);
            
            // Delete existing schedules for this date range
            $scheduleModel->deleteByDateRange($start_date, $end_date);
            
            // Generate schedule for each stream
            foreach ([1, 2, 3] as $stream) {
                $stmt = $groupModel->getByStream($stream);
                $groups = $stmt->fetchAll();
                
                if (empty($groups)) {
                    continue;
                }
                
                // Get time slots for this stream
                $time_slots = $this->generateTimeSlots($stream);
                
                // Generate working days (Monday to Friday)
                $dates = $this->getWorkingDays($start_date, $end_date);
                
                // Apply rotation algorithm
                $this->assignGroupsWithRotation($groups, $time_slots, $dates, $stream);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Schedule generation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate time slots for a stream
     * @param int $stream Stream number (1, 2, or 3)
     * @return array Array of time slots
     */
    private function generateTimeSlots($stream) {
        $config = $this->stream_config[$stream];
        $slots = [];
        
        $current_time = strtotime($config['start_time']);
        $end_time = strtotime($config['end_time']);
        
        while ($current_time <= $end_time) {
            $slots[] = date('H:i', $current_time);
            $current_time += $config['interval'] * 60; // Add interval in seconds
        }
        
        return $slots;
    }
    
    /**
     * Get working days between start and end date (Mon-Fri)
     * @param string $start_date
     * @param string $end_date
     * @return array Array of dates
     */
    private function getWorkingDays($start_date, $end_date) {
        $dates = [];
        $current = strtotime($start_date);
        $end = strtotime($end_date);
        
        while ($current <= $end) {
            $day_of_week = date('N', $current); // 1 = Monday, 7 = Sunday
            
            if ($day_of_week >= 1 && $day_of_week <= 5) { // Monday to Friday
                $dates[] = date('Y-m-d', $current);
            }
            
            $current = strtotime('+1 day', $current);
        }
        
        return $dates;
    }
    
    /**
     * Assign groups to time slots with rotation algorithm
     * Ensures each group appears in different time slots throughout the week
     */
    private function assignGroupsWithRotation($groups, $time_slots, $dates, $stream) {
        $scheduleModel = new Schedule($this->conn);
        $num_groups = count($groups);
        $num_slots = count($time_slots);
        $num_days = count($dates);
        $max_groups_per_slot = $this->stream_config[$stream]['max_groups_per_slot'];
        
        // Create rotation pattern
        // Each group should rotate through different time slots across the week
        $rotation_offset = 0;
        
        foreach ($dates as $day_index => $date) {
            // Calculate rotation offset for this day
            $day_rotation = ($day_index * ceil($num_groups / $num_days)) % $num_slots;
            
            $slot_index = 0;
            $groups_in_current_slot = 0;
            
            foreach ($groups as $group_index => $group) {
                // Calculate which slot this group should be in with rotation
                $rotated_index = ($group_index + $day_rotation) % $num_groups;
                $target_slot = floor($rotated_index / $max_groups_per_slot) % $num_slots;
                
                // Assign to schedule
                $scheduleModel->group_id = $group['id'];
                $scheduleModel->schedule_date = $date;
                $scheduleModel->arrival_time = $time_slots[$target_slot];
                $scheduleModel->stream = $stream;
                $scheduleModel->create();
            }
        }
    }
    
    /**
     * Get today's schedule
     * @return array Schedule data
     */
    public function getTodaySchedule() {
        $scheduleModel = new Schedule($this->conn);
        $today = date('Y-m-d');
        $stmt = $scheduleModel->getByDate($today);
        return $stmt->fetchAll();
    }
    
    /**
     * Get next week schedule
     * @return array Schedule data
     */
    public function getNextWeekSchedule() {
        $scheduleModel = new Schedule($this->conn);
        $today = date('Y-m-d');
        $next_week = date('Y-m-d', strtotime('+7 days'));
        $stmt = $scheduleModel->getByDateRange($today, $next_week);
        return $stmt->fetchAll();
    }
}
?>
