<?php
// FILE: /app/helpers/Notification.php

class Notification {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function send($recipientEmail, $subject, $body, $type = 'general') {
        $sql = "INSERT INTO notifications (recipient_email, subject, body, type, status, created_at)
                VALUES (:email, :subject, :body, :type, 'pending', :created_at)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':email' => $recipientEmail,
            ':subject' => $subject,
            ':body' => $body,
            ':type' => $type,
            ':created_at' => date('Y-m-d H:i:s')
        ]);

        // Log notification
        $this->log("Email queued: $recipientEmail - $subject");

        return $result;
    }

    public function sendBulk($recipients, $subject, $body, $type = 'general') {
        $count = 0;
        foreach ($recipients as $email) {
            if ($this->send($email, $subject, $body, $type)) {
                $count++;
            }
        }
        return $count;
    }

    protected function log($message) {
        $logFile = LOGS . '/notifications.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
