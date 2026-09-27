-- Refresh the advertised FIX20 promotion so it is valid in the current release.
UPDATE coupons
SET valid_from = '2026-01-01 00:00:00', valid_until = '2027-12-31 23:59:59', status = 'active'
WHERE code = 'FIX20';

-- Ensure the prior proof-review prerequisites exist even if the older migration was skipped.
ALTER TABLE booking_proofs
    ADD COLUMN IF NOT EXISTS review_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS reviewed_by INT UNSIGNED NULL,
    ADD COLUMN IF NOT EXISTS reviewed_at DATETIME NULL;

ALTER TABLE bookings
    ADD COLUMN IF NOT EXISTS pro_earning_credited TINYINT(1) NOT NULL DEFAULT 0;

UPDATE bookings SET pro_earning_credited = 1 WHERE status = 'completed';

-- Keep the provider order and payment IDs separately for signature checks and idempotency.
ALTER TABLE transactions
    ADD COLUMN IF NOT EXISTS gateway_order_id VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS gateway_payment_id VARCHAR(100) NULL;

-- One current completion code per booking; only the hash is stored here.
CREATE TABLE IF NOT EXISTS booking_completion_otps (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    verified_at DATETIME NULL,
    cod_received TINYINT(1) NOT NULL DEFAULT 0,
    KEY idx_completion_otp_booking (booking_id, id),
    CONSTRAINT fk_completion_otp_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;