<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class AdminProfessionalChatModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getMessages(int $professionalId, int $viewerId): array
    {
        $professional = $this->db->fetch(
            "SELECT user_id FROM professional_profiles WHERE id = :pid",
            ['pid' => $professionalId]
        );
        if (!$professional) {
            return [];
        }

        $viewer = $this->db->fetch("SELECT role FROM users WHERE id = :uid", ['uid' => $viewerId]);
        if (!$viewer) {
            return [];
        }
        if ($viewer['role'] === 'professional' && (int)$professional['user_id'] !== $viewerId) {
            return [];
        }
        if (!in_array($viewer['role'], ['professional', 'admin', 'staff'], true)) {
            return [];
        }

        $messages = $this->db->fetchAll(
            "SELECT m.id, m.sender_user_id, m.recipient_user_id, m.message, m.created_at,
                    sender.name AS sender_name, sender.role AS sender_role
             FROM admin_professional_messages m
             JOIN users sender ON sender.id = m.sender_user_id
             WHERE m.professional_id = :pid
             ORDER BY m.id ASC
             LIMIT 200",
            ['pid' => $professionalId]
        );

        $this->db->run(
            "UPDATE admin_professional_messages
             SET is_read = 1
             WHERE professional_id = :pid AND recipient_user_id = :uid AND is_read = 0",
            ['pid' => $professionalId, 'uid' => $viewerId]
        );
        return $messages;
    }

    public function sendMessage(int $professionalId, int $senderId, string $message): bool
    {
        $message = trim($message);
        if ($professionalId <= 0 || $senderId <= 0 || $message === '' || mb_strlen($message) > 4000) {
            return false;
        }

        $professional = $this->db->fetch(
            "SELECT pp.user_id, u.name FROM professional_profiles pp
             JOIN users u ON u.id = pp.user_id WHERE pp.id = :pid",
            ['pid' => $professionalId]
        );
        $sender = $this->db->fetch("SELECT role FROM users WHERE id = :uid", ['uid' => $senderId]);
        if (!$professional || !$sender) {
            return false;
        }

        if ($sender['role'] === 'professional') {
            if ((int)$professional['user_id'] !== $senderId) {
                return false;
            }
            $recipient = $this->db->fetch(
                "SELECT id FROM users WHERE role IN ('admin', 'staff') AND status = 'active'
                 ORDER BY CASE WHEN role = 'admin' THEN 0 ELSE 1 END, id ASC LIMIT 1"
            );
        } elseif (in_array($sender['role'], ['admin', 'staff'], true)) {
            $recipient = ['id' => (int)$professional['user_id']];
        } else {
            return false;
        }

        if (!$recipient) {
            return false;
        }

        $this->db->run(
            "INSERT INTO admin_professional_messages
                (professional_id, sender_user_id, recipient_user_id, message)
             VALUES (:pid, :sender, :recipient, :message)",
            [
                'pid' => $professionalId,
                'sender' => $senderId,
                'recipient' => (int)$recipient['id'],
                'message' => $message,
            ]
        );
        return true;
    }

    public function getUnreadCount(int $professionalId, int $userId): int
    {
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS unread_count FROM admin_professional_messages
             WHERE professional_id = :pid AND recipient_user_id = :uid AND is_read = 0",
            ['pid' => $professionalId, 'uid' => $userId]
        );
        return (int)($row['unread_count'] ?? 0);
    }
}
