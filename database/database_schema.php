<?php
// Include the database connection file
require_once 'db_connection.php';


function createTable($conn, $sql, $tableName)
{
    if ($conn->query($sql) === TRUE) {
        // echo "Table '$tableName' created successfully or already exists.<br>";
        // return true;
    } else {
        echo "Error creating table '$tableName': " . $conn->error . "<br>";
        error_log("Error creating table '$tableName': " . $conn->error);
        return false;
    }
}

// Array of table schemas for the Auditing Management System
$tables = [
    [
        'name' => 'clients',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `clients` (
                `client_id` INT AUTO_INCREMENT PRIMARY KEY,
                `client_name` VARCHAR(255) NOT NULL UNIQUE,
                `contact_person` VARCHAR(255),
                `contact_email` VARCHAR(255) UNIQUE,
                `contact_phone` VARCHAR(100),
                `address` TEXT,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'users',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `users` (
                `user_id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(100) NOT NULL UNIQUE,
                `password` VARCHAR(255) NOT NULL,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `role` ENUM('Admin', 'Auditor', 'Reviewer', 'Client') NOT NULL,
                `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
                `two_factor_secret` VARCHAR(255) DEFAULT NULL,
                `reset_token` VARCHAR(255) DEFAULT NULL,
                `reset_token_expiry` DATETIME DEFAULT NULL,
                `last_login` TIMESTAMP NULL,
                `client_id` INT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `first_name` VARCHAR(255) NULL,
                `last_name` VARCHAR(255) NULL,
                `date_of_birth` DATE NULL,
                `gender` ENUM('Male', 'Female', 'Non-binary', 'Prefer not to say') NULL,
                `nationality` VARCHAR(255) NULL,
                `marital_status` ENUM('Single', 'Married', 'Divorced', 'Widowed', 'Prefer not to say') NULL,
                `phone_number` VARCHAR(100) NULL,
                `address_street` VARCHAR(255) NULL,
                `address_city` VARCHAR(255) NULL,
                `address_state` VARCHAR(255) NULL,
                `address_zip_code` VARCHAR(20) NULL,
                `address_country` VARCHAR(255) NULL,
                `occupation` VARCHAR(255) NULL,
                `company` VARCHAR(255) NULL,
                `education_level` ENUM('High School', 'Associate Degree', 'Bachelor\'s Degree', 'Master\'s Degree', 'Doctorate', 'Other') NULL,
                `time_zone` VARCHAR(100) NULL,
                `preferred_language` VARCHAR(100) NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'engagements',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `engagements` (
                `engagement_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_name` VARCHAR(255) NOT NULL,
                `created_by_user_id` VARCHAR(255) NOT NULL,
                `status` VARCHAR(255) NOT NULL,
                `engagement_type` VARCHAR(255) NOT NULL,
                `period` VARCHAR(255) NOT NULL,
                `engagement_year` VARCHAR(255) NOT NULL,
                `assigned_auditor_id` VARCHAR(255) NOT NULL,
                `assigned_reviewer_id` VARCHAR(255) NOT NULL,
                `client_id` INT NOT NULL,
                `start_date` DATE NOT NULL,
                `end_date` DATE NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`client_id`) REFERENCES `clients`(`client_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'user_engagements',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `user_engagements` (
                `user_engagement_id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `engagement_id` INT NOT NULL,
                `role` ENUM('Auditor', 'Reviewer', 'Client') NOT NULL,
                `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE,
                UNIQUE (`user_id`, `engagement_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'engagement_letters',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `engagement_letters` (
                `letter_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `file_path` VARCHAR(255) NOT NULL,
                `file_name` VARCHAR(255) NOT NULL,
                `uploaded_by_user_id` INT NOT NULL,
                `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE,
                FOREIGN KEY (`uploaded_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'trial_balance',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `trial_balance` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `account_code` VARCHAR(50) NOT NULL,
                `account_name` VARCHAR(255) NOT NULL,
                `debit` DECIMAL(15,2) DEFAULT 0,
                `credit` DECIMAL(15,2) DEFAULT 0,
                `adjusted_debit` DECIMAL(15,2) DEFAULT 0,
                `adjusted_credit` DECIMAL(15,2) DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'ledger_entries',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `ledger_entries` (
                `entry_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `account_code` VARCHAR(50) NOT NULL,
                `entry_date` DATE NOT NULL,
                `description` TEXT,
                `reference` VARCHAR(255),
                `debit` DECIMAL(15,2) DEFAULT 0,
                `credit` DECIMAL(15,2) DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'risk_assessment',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `risk_assessment` (
                `risk_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `risk_area` VARCHAR(255) NOT NULL,
                `inherent_risk` ENUM('Low', 'Medium', 'High', 'Significant') NOT NULL,
                `control_risk` ENUM('Low', 'Medium', 'High') NOT NULL,
                `detection_risk` ENUM('Low', 'Medium', 'High') NOT NULL,
                `overall_risk` ENUM('Low', 'Medium', 'High', 'Significant') NOT NULL,
                `mitigation_strategy` TEXT,
                `assessed_by_user_id` INT NOT NULL,
                `assessed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE,
                FOREIGN KEY (`assessed_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'materiality_calculations',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `materiality_calculations` (
                `materiality_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `basis` VARCHAR(255) NOT NULL,
                `percentage` DECIMAL(5,2) NOT NULL,
                `calculated_amount` DECIMAL(15,2) NOT NULL,
                `performance_materiality` DECIMAL(15,2) NOT NULL,
                `trivial_amount` DECIMAL(15,2) NOT NULL,
                `calculated_by_user_id` INT NOT NULL,
                `calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE,
                FOREIGN KEY (`calculated_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'audit_samples',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `audit_samples` (
                `sample_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `sample_type` ENUM('Random', 'Systematic', 'Stratified', 'Monetary Unit') NOT NULL,
                `population_size` INT NOT NULL,
                `sample_size` INT NOT NULL,
                `sampling_method_details` TEXT,
                `created_by_user_id` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE,
                FOREIGN KEY (`created_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'queries',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `queries` (
                `query_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `raised_by_user_id` INT NOT NULL,
                `raised_to_user_id` INT NULL, -- Can be client or another auditor/reviewer
                `query_text` TEXT NOT NULL,
                `response_text` TEXT,
                `status` ENUM('Sent', 'Open', 'Responded') NOT NULL DEFAULT 'Sent',
                `raised_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `responded_at` TIMESTAMP NULL,
                `closed_at` TIMESTAMP NULL,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE,
                FOREIGN KEY (`raised_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
                FOREIGN KEY (`raised_to_user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'working_papers',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `working_papers` (
                `paper_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT,
                `file_path` VARCHAR(255) NOT NULL,
                `file_name` VARCHAR(255) NOT NULL,
                `uploaded_by_user_id` INT NOT NULL,
                `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `review_status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
                `review_comments` TEXT,
                `reviewed_by_user_id` INT NULL,
                `reviewed_at` TIMESTAMP NULL,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE,
                FOREIGN KEY (`uploaded_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
                FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'audit_adjustments',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `audit_adjustments` (
                `adjustment_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `account_code` VARCHAR(50) NOT NULL,
                `description` TEXT,
                `debit` DECIMAL(15,2) DEFAULT 0,
                `credit` DECIMAL(15,2) DEFAULT 0,
                `posted_by_user_id` INT NOT NULL,
                `posted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE,
                FOREIGN KEY (`posted_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'reconciliations',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `reconciliations` (
                `reconciliation_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `reconciliation_type` ENUM('Bank', 'Debtor', 'Creditor', 'Inventory', 'Other') NOT NULL,
                `description` TEXT,
                `reconciled_amount` DECIMAL(15,2) NOT NULL,
                `difference` DECIMAL(15,2) NOT NULL,
                `status` ENUM('Pending', 'Reconciled', 'Discrepancy') NOT NULL DEFAULT 'Pending',
                `reconciled_by_user_id` INT NOT NULL,
                `reconciled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE,
                FOREIGN KEY (`reconciled_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'exception_reports',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `exception_reports` (
                `report_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT NOT NULL,
                `severity` ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL,
                `status` ENUM('Open', 'Resolved', 'Acknowledged') NOT NULL DEFAULT 'Open',
                `raised_by_user_id` INT NOT NULL,
                `raised_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `resolved_at` TIMESTAMP NULL,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE,
                FOREIGN KEY (`raised_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'audit_reports',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `audit_reports` (
                `report_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `report_type` ENUM('Draft', 'Final') NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `content` LONGTEXT,
                `file_path` VARCHAR(255),
                `generated_by_user_id` INT NOT NULL,
                `generated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `approved_by_user_id` INT NULL,
                `approved_at` TIMESTAMP NULL,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE,
                FOREIGN KEY (`generated_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
                FOREIGN KEY (`approved_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'management_letters',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `management_letters` (
                `letter_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `report_type` ENUM('Draft', 'Final') NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `content` LONGTEXT,
                `file_path` VARCHAR(255),
                `generated_by_user_id` INT NOT NULL,
                `generated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `approved_by_user_id` INT NULL,
                `approved_at` TIMESTAMP NULL,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE,
                FOREIGN KEY (`generated_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
                FOREIGN KEY (`approved_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'report_versions',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `report_versions` (
                `version_id` INT AUTO_INCREMENT PRIMARY KEY,
                `report_id` INT NOT NULL,
                `report_type` ENUM('Audit Report', 'Management Letter') NOT NULL,
                `version_number` VARCHAR(50) NOT NULL,
                `file_path` VARCHAR(255) NOT NULL,
                `created_by_user_id` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`created_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'kpi_dashboard',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `kpi_dashboard` (
                `kpi_id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `total_engagements` INT DEFAULT 0,
                `open_queries` INT DEFAULT 0,
                `unresolved_exceptions` INT DEFAULT 0,
                `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'ratio_analysis',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `ratio_analysis` (
                `analysis_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `ratio_name` VARCHAR(255) NOT NULL,
                `ratio_value` DECIMAL(15,4) NOT NULL,
                `analysis_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `notes` TEXT,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'trend_charts',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `trend_charts` (
                `chart_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `metric_name` VARCHAR(255) NOT NULL,
                `year` INT NOT NULL,
                `value` DECIMAL(15,2) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'compliance_checklists',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `compliance_checklists` (
                `checklist_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `standard_type` ENUM('IFRS', 'GAAP', 'Internal Controls', 'Custom') NOT NULL,
                `item_description` TEXT NOT NULL,
                `is_compliant` BOOLEAN DEFAULT FALSE,
                `notes` TEXT,
                `reviewed_by_user_id` INT NULL,
                `reviewed_at` TIMESTAMP NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE,
                FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'internal_controls',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `internal_controls` (
                `control_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `control_name` VARCHAR(255) NOT NULL,
                `description` TEXT,
                `design_effectiveness` ENUM('Effective', 'Ineffective') NULL,
                `operating_effectiveness` ENUM('Effective', 'Ineffective') NULL,
                `tested_by_user_id` INT NULL,
                `tested_at` TIMESTAMP NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE,
                FOREIGN KEY (`tested_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'document_management',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `document_management` (
                `document_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NULL,
                `file_name` VARCHAR(255) NOT NULL,
                `file_path` VARCHAR(255) NOT NULL,
                `document_type` VARCHAR(100),
                `uploaded_by_user_id` INT NOT NULL,
                `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE SET NULL,
                FOREIGN KEY (`uploaded_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'secure_backups',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `secure_backups` (
                `backup_id` INT AUTO_INCREMENT PRIMARY KEY,
                `backup_name` VARCHAR(255) NOT NULL,
                `backup_location` VARCHAR(255) NOT NULL, -- 'local', 'cloud'
                `file_path` VARCHAR(255) NOT NULL,
                `backup_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `created_by_user_id` INT NOT NULL,
                FOREIGN KEY (`created_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'digital_signatures',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `digital_signatures` (
                `signature_id` INT AUTO_INCREMENT PRIMARY KEY,
                `document_id` INT NOT NULL,
                `signed_by_user_id` INT NOT NULL,
                `signature_image_path` VARCHAR(255) NULL,
                `signed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `ip_address` VARCHAR(45),
                FOREIGN KEY (`document_id`) REFERENCES `document_management`(`document_id`) ON DELETE CASCADE,
                FOREIGN KEY (`signed_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'audit_logs',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `audit_logs` (
                `log_id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NULL,
                `action` VARCHAR(255) NOT NULL,
                `details` TEXT,
                `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `ip_address` VARCHAR(45) NULL,
                `user_agent` TEXT NULL,
                `record_id` INT NULL,
                `record_type` VARCHAR(255) NULL,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'session_logs',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `session_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NULL,
                `event_type` VARCHAR(50) NOT NULL,  -- 'login', 'logout', 'timeout', 'hijack'
                `ip_address` VARCHAR(45) NOT NULL,
                `user_agent` TEXT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ],
    [
        'name' => 'anomaly_detection_results',
        'sql' => "
            CREATE TABLE IF NOT EXISTS `anomaly_detection_results` (
                `result_id` INT AUTO_INCREMENT PRIMARY KEY,
                `engagement_id` INT NOT NULL,
                `account_code` VARCHAR(50) NOT NULL,
                `anomaly_score` DECIMAL(10,2) NOT NULL,
                `description` TEXT,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`engagement_id`) REFERENCES `engagements`(`engagement_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"
    ]
];

// Execute table creation
foreach ($tables as $table) {
    createTable($conn, $table['sql'], $table['name']);
}

