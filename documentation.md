# Auditing Management System Documentation

## Project Overview

The Auditing Management System is a web-based application designed to streamline and manage the auditing process. It provides a centralized platform for auditors, reviewers, and administrators to collaborate on engagements, manage clients, assess risks, and generate reports. The system is built using PHP, MySQL, Bootstrap, and JavaScript, offering a robust and user-friendly interface.

## System Architecture

The application follows a traditional client-server architecture, primarily using PHP for server-side logic and MySQL for data persistence.

-   **Frontend**: HTML, CSS (Bootstrap), and JavaScript provide the user interface and interactive elements.
-   **Backend**: PHP handles user authentication, data processing, business logic, and interaction with the database.
-   **Database**: MySQL stores all application data, including user information, client details, engagement data, audit findings, and reports.
-   **Libraries**:
    -   **FPDF**: Used for generating PDF documents, such as audit reports.
    -   **PHPMailer**: Used for sending email notifications, e.g., for password resets or query updates.

The `index.php` file serves as the main entry point, redirecting users to `login.php` or `dashboard.php` based on their session status. The `dashboard.php` then dynamically renders content and navigation options according to the authenticated user's role, providing a personalized experience.

## File Structure

-   **/css/**: Contains CSS stylesheets for the application.
    -   `style.css`: Main stylesheet for the application.
-   **/database/**: Contains files related to database management.
    -   `db_connection.php`: Establishes a connection to the database.
    -   `database_schema.php`: Defines the database schema and creates tables.
-   **/fpdf/**: Contains the FPDF library for generating PDF documents.
    -   `fpdf.css`: CSS for FPDF (though FPDF is primarily PHP-based for PDF generation).
    -   `fpdf.php`: Core FPDF library file.
    -   `font/`: Contains font definition files for FPDF.
-   **/includes/**: Contains reusable files such as header and footer.
    -   `header.php`: Contains the header section of the application, including navigation.
    -   `footer.php`: Contains the footer section of the application.
-   **/js/**: Contains JavaScript files for the application.
    -   `script.js`: Main JavaScript file for client-side interactions.
-   **/phpmailer/**: Contains the PHPMailer library for sending emails.
    -   `.editorconfig`: Editor configuration.
    -   `composer.json`: PHPMailer's dependency management file.
    -   `get_oauth_token.php`: Script for OAuth token retrieval.
    -   `VERSION`: PHPMailer version file.
    -   `language/`: Contains language files for PHPMailer.
    -   `src/`: Contains the core PHPMailer source files.
        -   `DSNConfigurator.php`
        -   `Exception.php`
        -   `OAuth.php`
        -   `OAuthTokenProvider.php`
        -   `PHPMailer.php`
        -   `POP3.php`
        -   `SMTP.php`
-   **/templates/**: Contains templates for various functionalities.
    -   `trial_balance_template.csv`: Template for trial balance uploads.

### Root Level Files

-   `anomaly_detection.php`: Manages anomaly detection functionalities.
-   `approve_reports.php`: Allows reviewers to approve reports.
-   `audit_adjustments.php`: Allows auditors to post audit adjustments.
-   `audit_sampling.php`: Provides tools for audit sampling.
-   `client_engagements.php`: Displays a list of engagements for the client.
-   `client_my_engagements.php`: Displays a list of engagements for the client. (Duplicate entry, will be clarified)
-   `compliance_checklists.php`: Manages compliance checklists.
-   `composer.json`: Project dependency management file.
-   `create_admin.php`: Script to create an admin user.
-   `create_engagement.php`: Allows administrators to create new engagements.
-   `dashboard.php`: Main dashboard for users after login, displaying role-specific information.
-   `documentation.md`: This documentation file.
-   `engagement_details.php`: Provides detailed information about a specific engagement.
-   `engagements_for_review.php`: Displays a list of engagements assigned to the reviewer for review.
-   `exception_reports.php`: Generates exception reports.
-   `generate_reports.php`: Generates audit reports.
-   `index.php`: Main entry point of the application, handles redirection based on session.
-   `internal_controls.php`: Manages internal controls assessment.
-   `kpi_dashboard.php`: Displays Key Performance Indicator (KPI) data for the dashboard.
-   `login.php`: Login page for users.
-   `logout.php`: Logout script.
-   `manage_clients.php`: Allows administrators to manage client information.
-   `manage_engagements.php`: Allows administrators to manage engagement details.
-   `manage_users.php`: Allows administrators to manage user accounts and roles.
-   `materiality_calculator.php`: Calculates materiality for an engagement.
-   `my_engagements.php`: Displays a list of engagements assigned to the auditor.
-   `open_queries.php`: Manages open queries.
-   `profile.php`: Page for users to manage their profile.
-   `queries.php`: Manages queries and communication related to an engagement.
-   `reconciliations.php`: Manages reconciliations for an engagement.
-   `register.php`: Registration page for new users.
-   `respond_to_queries.php`: Allows users to respond to queries.
-   `review_audit_adjustments.php`: Allows reviewers to review audit adjustments.
-   `review_compliance_checklists.php`: Allows reviewers to review compliance checklists.
-   `review_engagement_details.php`: Provides detailed information about an engagement for review.
-   `review_exception_reports.php`: Allows reviewers to review exception reports.
-   `review_internal_controls.php`: Allows reviewers to review internal controls.
-   `review_materiality_calculations.php`: Allows reviewers to review materiality calculations.
-   `review_queries.php`: Allows reviewers to review queries.
-   `review_reconciliations.php`: Allows reviewers to review reconciliations.
-   `review_risk_assessment.php`: Allows reviewers to review risk assessments.
-   `review_working_papers.php`: Allows reviewers to review working papers.
-   `risk_assessment.php`: Enables auditors to assess risks associated with an engagement.
-   `todo.md`: A markdown file for tracking tasks and notes.
-   `upload_documents.php`: Handles general document uploads.
-   `upload_trial_balance.php`: Allows auditors to upload trial balance data.
-   `view_balance_sheet.php`: Displays the balance sheet.
-   `view_my_engagements.php`: Displays a list of engagements for viewing.
-   `view_reports.php`: Allows users to view generated reports.
-   `view_trial_balance.php`: Displays the trial balance.
-   `working_papers.php`: Manages working papers for an engagement.

## Core Functionalities

The Auditing Management System provides a comprehensive set of features categorized by user roles and audit process stages:

### User Management & Authentication
-   **Registration (`register.php`)**: Allows new users to sign up.
-   **Login (`login.php`)**: Authenticates users and manages sessions.
-   **Logout (`logout.php`)**: Terminates user sessions.
-   **User Profile (`profile.php`)**: Enables users to manage their personal information.
-   **Manage Users (`manage_users.php`)**: (Admin) Allows administrators to create, update, and delete user accounts and assign roles.
-   **Create Admin (`create_admin.php`)**: A utility script to set up an initial administrator account.

### Client & Engagement Management
-   **Manage Clients (`manage_clients.php`)**: (Admin) Manages client information.
-   **Create Engagement (`create_engagement.php`)**: (Admin) Initiates new audit engagements.
-   **Manage Engagements (`manage_engagements.php`)**: (Admin) Oversees all audit engagements.
-   **My Engagements (`my_engagements.php`, `view_my_engagements.php`)**: (Auditor) Displays engagements assigned to the auditor.
-   **Client Engagements (`client_engagements.php`, `client_my_engagements.php`)**: (Client) Allows clients to view their assigned engagements.
-   **Engagement Details (`engagement_details.php`, `review_engagement_details.php`)**: Provides a detailed view of a specific engagement, serving as a hub for all related modules.

### Audit Process Modules (Accessible via Engagement Details)
-   **View Trial Balance (`view_trial_balance.php`)**: Displays uploaded trial balance data.
-   **Upload Trial Balance (`upload_trial_balance.php`)**: Facilitates uploading trial balance data, typically in CSV format.
-   **View Balance Sheet (`view_balance_sheet.php`)**: Generates and displays a basic balance sheet.
-   **Risk Assessment (`risk_assessment.php`, `review_risk_assessment.php`)**: Identifies, assesses, and documents engagement risks.
-   **Materiality Calculator (`materiality_calculator.php`, `review_materiality_calculations.php`)**: Calculates and documents materiality levels.
-   **Audit Sampling (`audit_sampling.php`)**: Defines and documents audit sampling strategies.
-   **Queries & Responses (`queries.php`, `open_queries.php`, `respond_to_queries.php`, `review_queries.php`)**: Manages communication between audit teams and clients regarding data queries.
-   **Working Paper Attachments (`working_papers.php`, `review_working_papers.php`)**: Manages and attaches audit working papers.
-   **Audit Adjustments (`audit_adjustments.php`, `review_audit_adjustments.php`)**: Records and manages proposed adjustments to financial statements.
-   **Reconciliations & Testing (`reconciliations.php`, `review_reconciliations.php`)**: Supports account reconciliation and detailed testing.
-   **Exception Reports (`exception_reports.php`, `review_exception_reports.php`)**: Generates and manages reports on unusual transactions or discrepancies.
-   **Anomaly Detection (`anomaly_detection.php`)**: Identifies unusual patterns or outliers in financial data.
-   **Generate Reports (`generate_reports.php`, `view_reports.php`, `approve_reports.php`)**: Creates various audit reports (e.g., final audit report, management letters).
-   **Compliance & Standards (`compliance_checklists.php`, `review_compliance_checklists.php`)**: Manages compliance checklists against auditing standards.
-   **Internal Controls (`internal_controls.php`, `review_internal_controls.php`)**: Assesses and documents the client's internal control systems.
-   **KPI Dashboard (`kpi_dashboard.php`)**: Displays key performance indicators for quick insights.

### Reviewer Specific Functionalities
-   **Engagements for Review (`engagements_for_review.php`)**: Lists engagements awaiting review by the assigned reviewer.
-   **Review Modules**: Dedicated review pages for various audit modules (e.g., `review_audit_adjustments.php`, `review_compliance_checklists.php`, etc.) allowing reviewers to examine and approve auditor's work.

### Other Utilities
-   **Document Management (`upload_documents.php`)**: General document upload functionality.
-   **Audit Logs**: Comprehensive logging of user actions for security and accountability.
-   **Session Logs**: Tracks user session activities.

## Database Schema

The database schema is designed to support the various functionalities of the Auditing Management System. Below is a detailed description of each table:

### `clients`
-   **Purpose**: Stores information about clients.
-   **Columns**:
    -   `client_id` (INT, PK, AUTO_INCREMENT): Unique identifier for the client.
    -   `client_name` (VARCHAR(255), NOT NULL, UNIQUE): Name of the client.
    -   `contact_person` (VARCHAR(255)): Primary contact person for the client.
    -   `contact_email` (VARCHAR(255), UNIQUE): Email of the contact person.
    -   `contact_phone` (VARCHAR(100)): Phone number of the contact person.
    -   `address` (TEXT): Client's address.
    -   `created_at` (TIMESTAMP): Timestamp of client creation.
    -   `updated_at` (TIMESTAMP): Timestamp of last update.

### `users`
-   **Purpose**: Stores user account information, including roles and permissions.
-   **Columns**:
    -   `user_id` (INT, PK, AUTO_INCREMENT): Unique identifier for the user.
    -   `username` (VARCHAR(100), NOT NULL, UNIQUE): User's login username.
    -   `password` (VARCHAR(255), NOT NULL): Hashed password.
    -   `email` (VARCHAR(255), NOT NULL, UNIQUE): User's email address.
    -   `role` (ENUM('Admin', 'Auditor', 'Reviewer', 'Client'), NOT NULL): User's role in the system.
    -   `status` (ENUM('active', 'inactive'), NOT NULL, DEFAULT 'active'): User account status.
    -   `two_factor_secret` (VARCHAR(255)): Secret for two-factor authentication.
    -   `reset_token` (VARCHAR(255)): Token for password reset.
    -   `reset_token_expiry` (DATETIME): Expiry time for the reset token.
    -   `last_login` (TIMESTAMP): Timestamp of the last login.
    -   `client_id` (INT, NULL): Foreign key to `clients` table, for users with 'Client' role.
    -   `created_at` (TIMESTAMP): Timestamp of user creation.
    -   `updated_at` (TIMESTAMP): Timestamp of last update.

### `engagements`
-   **Purpose**: Stores information about audit engagements.
-   **Columns**:
    -   `engagement_id` (INT, PK, AUTO_INCREMENT): Unique identifier for the engagement.
    -   `engagement_name` (VARCHAR(255), NOT NULL): Name of the engagement.
    -   `created_by_user_id` (VARCHAR(255), NOT NULL): User ID of the creator.
    -   `status` (VARCHAR(255), NOT NULL): Current status of the engagement (e.g., 'Planning', 'Fieldwork', 'Review', 'Completed').
    -   `engagement_type` (VARCHAR(255), NOT NULL): Type of audit engagement.
    -   `period` (VARCHAR(255), NOT NULL): Audit period.
    -   `engagement_year` (VARCHAR(255), NOT NULL): Year of the engagement.
    -   `assigned_auditor_id` (VARCHAR(255), NOT NULL): User ID of the assigned auditor.
    -   `assigned_reviewer_id` (VARCHAR(255), NOT NULL): User ID of the assigned reviewer.
    -   `client_id` (INT, NOT NULL, FK): Foreign key to `clients` table.
    -   `start_date` (DATE, NOT NULL): Start date of the engagement.
    -   `end_date` (DATE, NOT NULL): End date of the engagement.
    -   `created_at` (TIMESTAMP): Timestamp of engagement creation.
    -   `updated_at` (TIMESTAMP): Timestamp of last update.

### `user_engagements`
-   **Purpose**: Links users to specific engagements with their roles.
-   **Columns**:
    -   `user_engagement_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `user_id` (INT, NOT NULL, FK): Foreign key to `users` table.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `role` (ENUM('Auditor', 'Reviewer', 'Client'), NOT NULL): Role of the user within this specific engagement.
    -   `assigned_at` (TIMESTAMP): Timestamp of assignment.

### `engagement_letters`
-   **Purpose**: Stores engagement letters.
-   **Columns**:
    -   `letter_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `file_path` (VARCHAR(255), NOT NULL): Path to the stored engagement letter file.
    -   `file_name` (VARCHAR(255), NOT NULL): Original file name.
    -   `uploaded_by_user_id` (INT, NOT NULL, FK): User ID of the uploader.
    -   `uploaded_at` (TIMESTAMP): Timestamp of upload.

### `trial_balance`
-   **Purpose**: Stores trial balance data for an engagement.
-   **Columns**:
    -   `id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `account_code` (VARCHAR(50), NOT NULL): Account code.
    -   `account_name` (VARCHAR(255), NOT NULL): Account name.
    -   `debit` (DECIMAL(15,2), DEFAULT 0): Debit amount.
    -   `credit` (DECIMAL(15,2), DEFAULT 0): Credit amount.
    -   `adjusted_debit` (DECIMAL(15,2), DEFAULT 0): Adjusted debit amount.
    -   `adjusted_credit` (DECIMAL(15,2), DEFAULT 0): Adjusted credit amount.
    -   `created_at` (TIMESTAMP): Timestamp of creation.
    -   `updated_at` (TIMESTAMP): Timestamp of last update.

### `ledger_entries`
-   **Purpose**: Stores detailed ledger entries for an engagement.
-   **Columns**:
    -   `entry_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `account_code` (VARCHAR(50), NOT NULL): Account code.
    -   `entry_date` (DATE, NOT NULL): Date of the entry.
    -   `description` (TEXT): Description of the entry.
    -   `reference` (VARCHAR(255)): Reference number or detail.
    -   `debit` (DECIMAL(15,2), DEFAULT 0): Debit amount.
    -   `credit` (DECIMAL(15,2), DEFAULT 0): Credit amount.
    -   `created_at` (TIMESTAMP): Timestamp of creation.

### `risk_assessment`
-   **Purpose**: Stores risk assessment data for an engagement.
-   **Columns**:
    -   `risk_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `risk_area` (VARCHAR(255), NOT NULL): Area of risk (e.g., "Revenue Recognition").
    -   `inherent_risk` (ENUM('Low', 'Medium', 'High', 'Significant'), NOT NULL): Inherent risk level.
    -   `control_risk` (ENUM('Low', 'Medium', 'High'), NOT NULL): Control risk level.
    -   `detection_risk` (ENUM('Low', 'Medium', 'High'), NOT NULL): Detection risk level.
    -   `overall_risk` (ENUM('Low', 'Medium', 'High', 'Significant'), NOT NULL): Overall risk level.
    -   `mitigation_strategy` (TEXT): Strategy to mitigate the risk.
    -   `assessed_by_user_id` (INT, NOT NULL, FK): User ID of the assessor.
    -   `assessed_at` (TIMESTAMP): Timestamp of assessment.
    -   `updated_at` (TIMESTAMP): Timestamp of last update.

### `materiality_calculations`
-   **Purpose**: Stores materiality calculations for an engagement.
-   **Columns**:
    -   `materiality_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `basis` (VARCHAR(255), NOT NULL): Basis for calculation (e.g., "Revenue", "Total Assets").
    -   `percentage` (DECIMAL(5,2), NOT NULL): Percentage applied to the basis.
    -   `calculated_amount` (DECIMAL(15,2), NOT NULL): Resulting materiality amount.
    -   `performance_materiality` (DECIMAL(15,2), NOT NULL): Performance materiality amount.
    -   `trivial_amount` (DECIMAL(15,2), NOT NULL): Trivial amount.
    -   `calculated_by_user_id` (INT, NOT NULL, FK): User ID of the calculator.
    -   `calculated_at` (TIMESTAMP): Timestamp of calculation.
    -   `updated_at` (TIMESTAMP): Timestamp of last update.

### `audit_samples`
-   **Purpose**: Stores audit sampling information.
-   **Columns**:
    -   `sample_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `sample_type` (ENUM('Random', 'Systematic', 'Stratified', 'Monetary Unit'), NOT NULL): Type of sampling method.
    -   `population_size` (INT, NOT NULL): Total population size.
    -   `sample_size` (INT, NOT NULL): Size of the sample.
    -   `sampling_method_details` (TEXT): Details about the sampling method.
    -   `created_by_user_id` (INT, NOT NULL, FK): User ID of the creator.
    -   `created_at` (TIMESTAMP): Timestamp of creation.

### `queries`
-   **Purpose**: Stores queries raised during the audit process.
-   **Columns**:
    -   `query_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `raised_by_user_id` (INT, NOT NULL, FK): User ID who raised the query.
    -   `raised_to_user_id` (INT, NULL, FK): User ID to whom the query was raised (can be null).
    -   `query_text` (TEXT, NOT NULL): Content of the query.
    -   `response_text` (TEXT): Response to the query.
    -   `status` (ENUM('Open', 'Responded', 'Closed'), NOT NULL, DEFAULT 'Open'): Current status of the query.
    -   `raised_at` (TIMESTAMP): Timestamp when the query was raised.
    -   `responded_at` (TIMESTAMP): Timestamp when the query was responded to.
    -   `closed_at` (TIMESTAMP): Timestamp when the query was closed.

### `working_papers`
-   **Purpose**: Stores information about working papers.
-   **Columns**:
    -   `paper_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `title` (VARCHAR(255), NOT NULL): Title of the working paper.
    -   `description` (TEXT): Description of the working paper.
    -   `file_path` (VARCHAR(255), NOT NULL): Path to the stored file.
    -   `file_name` (VARCHAR(255), NOT NULL): Original file name.
    -   `uploaded_by_user_id` (INT, NOT NULL, FK): User ID of the uploader.
    -   `uploaded_at` (TIMESTAMP): Timestamp of upload.
    -   `updated_at` (TIMESTAMP): Timestamp of last update.

### `audit_adjustments`
-   **Purpose**: Stores audit adjustments.
-   **Columns**:
    -   `adjustment_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `account_code` (VARCHAR(50), NOT NULL): Account code affected.
    -   `description` (TEXT): Description of the adjustment.
    -   `debit` (DECIMAL(15,2), DEFAULT 0): Debit amount of the adjustment.
    -   `credit` (DECIMAL(15,2), DEFAULT 0): Credit amount of the adjustment.
    -   `posted_by_user_id` (INT, NOT NULL, FK): User ID who posted the adjustment.
    -   `posted_at` (TIMESTAMP): Timestamp when the adjustment was posted.

### `reconciliations`
-   **Purpose**: Stores reconciliation data.
-   **Columns**:
    -   `reconciliation_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `reconciliation_type` (ENUM('Bank', 'Debtor', 'Creditor', 'Inventory', 'Other'), NOT NULL): Type of reconciliation.
    -   `description` (TEXT): Description of the reconciliation.
    -   `reconciled_amount` (DECIMAL(15,2), NOT NULL): Amount reconciled.
    -   `difference` (DECIMAL(15,2), NOT NULL): Difference identified.
    -   `status` (ENUM('Pending', 'Reconciled', 'Discrepancy'), NOT NULL, DEFAULT 'Pending'): Status of the reconciliation.
    -   `reconciled_by_user_id` (INT, NOT NULL, FK): User ID who performed the reconciliation.
    -   `reconciled_at` (TIMESTAMP): Timestamp of reconciliation.
    -   `updated_at` (TIMESTAMP): Timestamp of last update.

### `exception_reports`
-   **Purpose**: Stores exception reports.
-   **Columns**:
    -   `report_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `title` (VARCHAR(255), NOT NULL): Title of the report.
    -   `description` (TEXT, NOT NULL): Description of the exception.
    -   `severity` (ENUM('Low', 'Medium', 'High', 'Critical'), NOT NULL): Severity level of the exception.
    -   `status` (ENUM('Open', 'Resolved', 'Acknowledged'), NOT NULL, DEFAULT 'Open'): Status of the exception.
    -   `raised_by_user_id` (INT, NOT NULL, FK): User ID who raised the report.
    -   `raised_at` (TIMESTAMP): Timestamp when the report was raised.
    -   `resolved_at` (TIMESTAMP): Timestamp when the report was resolved.

### `audit_reports`
-   **Purpose**: Stores audit reports.
-   **Columns**:
    -   `report_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `report_type` (ENUM('Draft', 'Final'), NOT NULL): Type of report.
    -   `title` (VARCHAR(255), NOT NULL): Title of the report.
    -   `content` (LONGTEXT): Full content of the report.
    -   `file_path` (VARCHAR(255)): Path to the generated PDF file.
    -   `generated_by_user_id` (INT, NOT NULL, FK): User ID who generated the report.
    -   `generated_at` (TIMESTAMP): Timestamp of generation.
    -   `approved_by_user_id` (INT, NULL, FK): User ID who approved the report.
    -   `approved_at` (TIMESTAMP): Timestamp of approval.

### `management_letters`
-   **Purpose**: Stores management letters.
-   **Columns**:
    -   `letter_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `report_type` (ENUM('Draft', 'Final'), NOT NULL): Type of letter.
    -   `title` (VARCHAR(255), NOT NULL): Title of the letter.
    -   `content` (LONGTEXT): Full content of the letter.
    -   `file_path` (VARCHAR(255)): Path to the generated PDF file.
    -   `generated_by_user_id` (INT, NOT NULL, FK): User ID who generated the letter.
    -   `generated_at` (TIMESTAMP): Timestamp of generation.
    -   `approved_by_user_id` (INT, NULL, FK): User ID who approved the letter.
    -   `approved_at` (TIMESTAMP): Timestamp of approval.

### `report_versions`
-   **Purpose**: Stores versions of reports.
-   **Columns**:
    -   `version_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `report_id` (INT, NOT NULL, FK): Foreign key to `audit_reports` or `management_letters` (implicitly).
    -   `report_type` (ENUM('Audit Report', 'Management Letter'), NOT NULL): Type of the report.
    -   `version_number` (VARCHAR(50), NOT NULL): Version string (e.g., "1.0", "Draft A").
    -   `file_path` (VARCHAR(255), NOT NULL): Path to the versioned report file.
    -   `created_by_user_id` (INT, NOT NULL, FK): User ID who created this version.
    -   `created_at` (TIMESTAMP): Timestamp of version creation.

### `kpi_dashboard`
-   **Purpose**: Stores Key Performance Indicator (KPI) data for the dashboard.
-   **Columns**:
    -   `kpi_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `user_id` (INT, NOT NULL, FK): Foreign key to `users` table.
    -   `total_engagements` (INT, DEFAULT 0): Total engagements for the user.
    -   `open_queries` (INT, DEFAULT 0): Number of open queries for the user.
    -   `unresolved_exceptions` (INT, DEFAULT 0): Number of unresolved exceptions for the user.
    -   `last_updated` (TIMESTAMP): Timestamp of last update.

### `ratio_analysis`
-   **Purpose**: Stores ratio analysis data.
-   **Columns**:
    -   `analysis_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `ratio_name` (VARCHAR(255), NOT NULL): Name of the ratio (e.g., "Current Ratio").
    -   `ratio_value` (DECIMAL(15,4), NOT NULL): Calculated value of the ratio.
    -   `analysis_date` (TIMESTAMP): Date of analysis.
    -   `notes` (TEXT): Any notes related to the analysis.

### `trend_charts`
-   **Purpose**: Stores data for trend charts.
-   **Columns**:
    -   `chart_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `metric_name` (VARCHAR(255), NOT NULL): Name of the metric (e.g., "Revenue", "Net Income").
    -   `year` (INT, NOT NULL): Year for the metric.
    -   `value` (DECIMAL(15,2), NOT NULL): Value of the metric for the year.
    -   `created_at` (TIMESTAMP): Timestamp of creation.

### `compliance_checklists`
-   **Purpose**: Stores compliance checklist data.
-   **Columns**:
    -   `checklist_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `standard_type` (ENUM('IFRS', 'GAAP', 'Internal Controls', 'Custom'), NOT NULL): Type of standard.
    -   `item_description` (TEXT, NOT NULL): Description of the checklist item.
    -   `is_compliant` (BOOLEAN, DEFAULT FALSE): Indicates if the item is compliant.
    -   `notes` (TEXT): Any notes related to the item.
    -   `reviewed_by_user_id` (INT, NULL, FK): User ID of the reviewer.
    -   `reviewed_at` (TIMESTAMP): Timestamp of review.
    -   `created_at` (TIMESTAMP): Timestamp of creation.
    -   `updated_at` (TIMESTAMP): Timestamp of last update.

### `internal_controls`
-   **Purpose**: Stores internal controls assessment data.
-   **Columns**:
    -   `control_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `control_name` (VARCHAR(255), NOT NULL): Name of the control.
    -   `description` (TEXT): Description of the control.
    -   `design_effectiveness` (ENUM('Effective', 'Ineffective'), NULL): Assessment of design effectiveness.
    -   `operating_effectiveness` (ENUM('Effective', 'Ineffective'), NULL): Assessment of operating effectiveness.
    -   `tested_by_user_id` (INT, NULL, FK): User ID who tested the control.
    -   `tested_at` (TIMESTAMP): Timestamp of testing.
    -   `created_at` (TIMESTAMP): Timestamp of creation.
    -   `updated_at` (TIMESTAMP): Timestamp of last update.

### `document_management`
-   **Purpose**: Stores information about uploaded documents.
-   **Columns**:
    -   `document_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NULL, FK): Foreign key to `engagements` table (can be null for general documents).
    -   `file_name` (VARCHAR(255), NOT NULL): Original file name.
    -   `file_path` (VARCHAR(255), NOT NULL): Path to the stored document file.
    -   `document_type` (VARCHAR(100)): Type of document (e.g., "Invoice", "Contract").
    -   `uploaded_by_user_id` (INT, NOT NULL, FK): User ID of the uploader.
    -   `uploaded_at` (TIMESTAMP): Timestamp of upload.
    -   `updated_at` (TIMESTAMP): Timestamp of last update.

### `secure_backups`
-   **Purpose**: Stores information about secure backups.
-   **Columns**:
    -   `backup_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `backup_name` (VARCHAR(255), NOT NULL): Name of the backup.
    -   `backup_location` (VARCHAR(255), NOT NULL): Location of the backup ('local', 'cloud').
    -   `file_path` (VARCHAR(255), NOT NULL): Path to the backup file.
    -   `backup_date` (TIMESTAMP): Date of the backup.
    -   `created_by_user_id` (INT, NOT NULL, FK): User ID who created the backup.

### `digital_signatures`
-   **Purpose**: Stores information about digital signatures.
-   **Columns**:
    -   `signature_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `document_id` (INT, NOT NULL, FK): Foreign key to `document_management` table.
    -   `signed_by_user_id` (INT, NOT NULL, FK): User ID who signed the document.
    -   `signature_image_path` (VARCHAR(255), NULL): Path to the signature image.
    -   `signed_at` (TIMESTAMP): Timestamp of signing.
    -   `ip_address` (VARCHAR(45)): IP address from which the document was signed.

### `audit_logs`
-   **Purpose**: Stores audit logs, including user actions.
-   **Columns**:
    -   `log_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `user_id` (INT, NULL, FK): User ID who performed the action (can be null for system actions).
    -   `action` (VARCHAR(255), NOT NULL): Description of the action performed.
    -   `details` (TEXT): Additional details about the action.
    -   `timestamp` (TIMESTAMP): Timestamp of the action.
    -   `ip_address` (VARCHAR(45), NULL): IP address from which the action was performed.
    -   `user_agent` (TEXT, NULL): User agent string.
    -   `record_id` (INT, NULL): ID of the record affected.
    -   `record_type` (VARCHAR(255), NULL): Type of record affected.

### `session_logs`
-   **Purpose**: Stores session logs.
-   **Columns**:
    -   `id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `user_id` (INT, NULL, FK): User ID associated with the session.
    -   `event_type` (VARCHAR(50), NOT NULL): Type of session event ('login', 'logout', 'timeout', 'hijack').
    -   `ip_address` (VARCHAR(45), NOT NULL): IP address of the session.
    -   `user_agent` (TEXT, NOT NULL): User agent string.
    -   `created_at` (TIMESTAMP): Timestamp of the session event.

### `anomaly_detection_results`
-   **Purpose**: Stores results from anomaly detection processes.
-   **Columns**:
    -   `result_id` (INT, PK, AUTO_INCREMENT): Unique identifier.
    -   `engagement_id` (INT, NOT NULL, FK): Foreign key to `engagements` table.
    -   `account_code` (VARCHAR(50), NOT NULL): Account code where anomaly was detected.
    -   `anomaly_score` (DECIMAL(10,2), NOT NULL): Score indicating the severity of the anomaly.
    -   `description` (TEXT): Description of the detected anomaly.
    -   `created_at` (TIMESTAMP): Timestamp of detection.

## User Roles and Permissions

The system defines the following user roles, each with specific access levels and functionalities:

-   **Admin**: Has full access to the system. Can manage users, clients, and engagements. Responsible for system configuration and oversight.
-   **Auditor**: Can perform audit tasks related to assigned engagements. This includes uploading data, conducting assessments, raising queries, and generating reports.
-   **Reviewer**: Can review engagements and provide feedback. Responsible for approving audit work, reports, and ensuring compliance.
-   **Client**: Can view their engagements and related information. Limited access to their specific engagement data and communication with auditors.

## Operational Flow

The following outlines the typical operational flow of an audit engagement within the system:

1.  **Engagement Creation**: An administrator creates a new engagement, assigning a client, engagement year, period, and type.
2.  **Auditor Assignment**: An administrator assigns an auditor to the engagement.
3.  **Engagement Letter**: The auditor uploads the engagement letter.
4.  **Trial Balance Upload**: The auditor uploads the trial balance data.
5.  **Risk Assessment**: The auditor performs a risk assessment, identifying inherent, control, and detection risks.
6.  **Materiality Calculation**: The auditor calculates materiality for the engagement.
7.  **Audit Sampling**: The auditor determines the audit sampling strategy.
8.  **Fieldwork**: The auditor performs fieldwork, gathering evidence and documenting findings in working papers.
9.  **Queries**: The auditor raises queries to the client or other team members.
10. **Audit Adjustments**: The auditor posts audit adjustments.
11. **Reconciliations**: The auditor performs reconciliations.
12. **Exception Reporting**: The auditor generates exception reports.
13. **Review**: A reviewer reviews the engagement, including working papers, queries, and adjustments.
14. **Report Generation**: The auditor generates the audit report.
15. **Report Approval**: The reviewer approves the audit report.
16. **Finalization**: The engagement is finalized and closed.

## Setup and Installation Instructions

1.  Clone the repository to your local machine.
2.  Ensure you have a web server (e.g., Apache, Nginx) with PHP (version 7.4 or higher recommended) and MySQL installed.
3.  Create a MySQL database for the application.
4.  Import the database schema by running `database/database_schema.php` (e.g., navigate to `http://localhost/auditing/database/database_schema.php` in your browser or execute it via CLI).
5.  Configure the database connection in `database/db_connection.php` with your database credentials.
6.  Set up the PHPMailer library for sending emails (refer to PHPMailer documentation for specific SMTP configurations if needed).
7.  Deploy the application to your web server's document root (e.g., `htdocs` for Apache).
8.  Create an admin user using the `create_admin.php` script (e.g., navigate to `http://localhost/auditing/create_admin.php` in your browser and follow instructions).
