ALTER TABLE booking_proofs
    ADD COLUMN IF NOT EXISTS review_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS reviewed_by INT UNSIGNED NULL,
    ADD COLUMN IF NOT EXISTS reviewed_at DATETIME NULL;

ALTER TABLE bookings
    ADD COLUMN IF NOT EXISTS pro_earning_credited TINYINT(1) NOT NULL DEFAULT 0;

-- Legacy completed jobs were credited immediately by the old technician status handler.
-- Keep them flagged so migration cannot cause duplicate wallet credits.
UPDATE bookings SET pro_earning_credited = 1 WHERE status = 'completed';
