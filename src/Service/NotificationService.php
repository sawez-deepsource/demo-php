<?php

declare(strict_types=1);

namespace App\Service;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class NotificationService
{
    private Logger $logger;
    private array $sentNotifications = [];

    public function __construct(?string $logPath = null)
    {
        $this->logger = new Logger('notifications');
        $logPath = $logPath ?? dirname(__DIR__, 2) . '/var/log/notifications.log';
        $this->logger->pushHandler(new StreamHandler($logPath, Logger::INFO));
    }

    public function sendEmail(string $to, string $subject, string $body): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->logger->error("Invalid email address: {$to}");
            return false;
        }

        if (empty($subject) || empty($body)) {
            $this->logger->error('Subject and body cannot be empty');
            return false;
        }

        $notification = [
            'type' => 'email',
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'sent_at' => date('Y-m-d H:i:s'),
        ];

        $this->sentNotifications[] = $notification;
        $this->logger->info("Email sent to {$to}: {$subject}");

        return true;
    }

    public function sendBulkEmail(array $recipients, string $subject, string $body): array
    {
        $results = [];

        foreach ($recipients as $email) {
            $results[$email] = $this->sendEmail($email, $subject, $body);
        }

        $successCount = count(array_filter($results));
        $this->logger->info("Bulk email sent: {$successCount}/" . count($recipients) . " successful");

        return $results;
    }

    public function getNotificationHistory(): array
    {
        return $this->sentNotifications;
    }

    public function getNotificationCount(): int
    {
        return count($this->sentNotifications);
    }
}
