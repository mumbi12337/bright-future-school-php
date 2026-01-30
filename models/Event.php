<?php
require_once 'BaseModel.php';

class Event extends BaseModel {
    protected $table = 'events';
    
    /**
     * Get upcoming events
     */
    public function getUpcomingEvents($limit = 10) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE date >= NOW()
            ORDER BY date ASC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get events by date range
     */
    public function getEventsByDateRange($startDate, $endDate) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE date BETWEEN ? AND ?
            ORDER BY date ASC
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get events for the current month
     */
    public function getCurrentMonthEvents() {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE EXTRACT(MONTH FROM date) = EXTRACT(MONTH FROM CURRENT_DATE)
              AND EXTRACT(YEAR FROM date) = EXTRACT(YEAR FROM CURRENT_DATE)
            ORDER BY date ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Create a new event
     */
    public function createEvent($title, $description, $date) {
        $data = [
            'title' => $title,
            'description' => $description,
            'date' => $date
        ];
        
        return $this->create($data);
    }
}
?>