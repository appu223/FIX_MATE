CREATE TABLE IF NOT EXISTS admin_professional_messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    professional_id INT UNSIGNED NOT NULL,
    sender_user_id INT UNSIGNED NOT NULL,
    recipient_user_id INT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_admin_professional_conversation (professional_id, id),
    KEY idx_admin_professional_unread (recipient_user_id, is_read),
    CONSTRAINT fk_admin_pro_message_professional FOREIGN KEY (professional_id) REFERENCES professional_profiles (id) ON DELETE CASCADE,
    CONSTRAINT fk_admin_pro_message_sender FOREIGN KEY (sender_user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_admin_pro_message_recipient FOREIGN KEY (recipient_user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
