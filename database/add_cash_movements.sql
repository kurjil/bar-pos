-- Add shift_cash_movements table for tracking float ins and cash drops
CREATE TABLE IF NOT EXISTS shift_cash_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    shift_id INT NOT NULL,
    user_id INT NOT NULL,
    movement_type ENUM('FLOAT_IN', 'CASH_DROP') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (shift_id) REFERENCES shifts(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_shift (shift_id),
    INDEX idx_movement_type (movement_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
