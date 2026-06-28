<?php
namespace App\Services;

use App\Config\Database;
use Exception;

class QueueService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Push a new job onto the queue
     */
    public function push($jobClass, $data = [], $queue = 'default', $delay = 0) {
        $payload = json_encode([
            'job' => $jobClass,
            'data' => $data
        ]);
        
        $availableAt = date('Y-m-d H:i:s', time() + $delay);
        
        $query = "INSERT INTO jobs (queue, payload, available_at, created_at) 
                  VALUES (:queue, :payload, :available_at, NOW())";
        
        return $this->db->execute($query, [
            ':queue' => $queue,
            ':payload' => $payload,
            ':available_at' => $availableAt
        ]);
    }
    
    /**
     * Pop the next job from the queue
     */
    public function pop($queue = 'default') {
        $this->db->beginTransaction();
        
        // Find next available job
        // interacting with DB with FOR UPDATE to prevent race conditions 
        // if multiple workers are running
        $query = "SELECT * FROM jobs 
                  WHERE queue = :queue 
                  AND available_at <= NOW() 
                  ORDER BY available_at ASC 
                  LIMIT 1 
                  FOR UPDATE";
                  
        $job = $this->db->queryOne($query, [':queue' => $queue]);
        
        if ($job) {
            // Remove from queue (or you could move to failed_jobs if it fails later)
            // For simplicity in this system, we delete it when we pick it up
            // A more robust system would delete only after successful processing
            $deleteQuery = "DELETE FROM jobs WHERE id = :id";
            $this->db->execute($deleteQuery, [':id' => $job['id']]);
            
            $this->db->commit();
            return $job;
        }
        
        $this->db->commit();
        return null;
    }
    
    /**
     * Process the next job in the queue
     */
    public function work($queue = 'default') {
        $jobData = $this->pop($queue);
        
        if (!$jobData) {
            return false;
        }
        
        $payload = json_decode($jobData['payload'], true);
        $className = $payload['job'];
        $data = $payload['data'];
        
        if (class_exists($className)) {
            $job = new $className();
            if (method_exists($job, 'handle')) {
                try {
                    logMessage("Processing job: $className ID: {$jobData['id']}");
                    $job->handle($data);
                    return true;
                } catch (Exception $e) {
                    logMessage("Job failed: " . $e->getMessage(), 'ERROR');
                    // In a real system we would increment attempts and release back to queue
                }
            }
        }
        
        return false;
    }
}
