# Auditing Management System - User Documentation

This document provides a detailed overview of the Auditing Management System, outlining the functionalities available to different user roles.

## 1. System Overview

The Auditing Management System is a web-based application designed to streamline and manage audit engagements. It facilitates collaboration between auditors, reviewers, and clients, providing tools for engagement creation, document management, risk assessment, reporting, and more.

## 2. User Roles and Permissions

The system supports the following user roles, each with specific access rights and functionalities:

*   **Admin:** Full administrative control over users, clients, and all engagements.
*   **Auditor:** Manages assigned audit engagements, performs audit tasks, and uploads relevant documents.
*   **Reviewer:** Reviews audit work performed by auditors and approves reports.
*   **Client:** Views the status and details of their assigned audit engagements.

---

## 3. Role-Specific Functionalities

### 3.1. Admin

The Admin role has comprehensive control over the system, including user and client management, and oversight of all audit engagements.

**Key Responsibilities:**

*   **User Management:**
    *   View all registered users.
    *   Edit user details (username, email, role, status).
    *   Assign clients to users (especially for Client roles).
    *   Delete existing users.
    *   Register new users.
*   **Client Management:**
    *   Add new client entities to the system.
    *   View a list of all registered clients.
    *   Edit client details (name, contact person, email, phone, address).
    *   Delete clients.
*   **Engagement Oversight:**
    *   View all audit engagements in the system.
    *   Create new audit engagements.
    *   Access all engagement modules for any engagement (similar to Auditor and Reviewer, but with full oversight).

### 3.2. Auditor

The Auditor role is responsible for executing audit engagements, performing detailed audit procedures, and documenting findings.

**Key Responsibilities:**

*   **Engagement Creation:**
    *   Create new audit engagements by providing engagement name, selecting a client, and specifying start and end dates.
*   **My Engagements:**
    *   View a list of all audit engagements assigned to them.
    *   Access detailed information for each assigned engagement.
*   **Engagement Modules (within an assigned engagement):**
    *   **View Trial Balance:** Access and review the client's trial balance.
    *   **Upload Trial Balance:** Upload the client's trial balance data.
    *   **View Balance Sheet:** View the generated balance sheet.
    *   **Risk Assessment:** Conduct and document risk assessments for the engagement.
    *   **Materiality Calculator:** Utilize tools to calculate materiality levels.
    *   **Audit Sampling:** Perform audit sampling procedures.
    *   **Queries & Responses:** Manage and respond to audit queries.
    *   **Working Paper Attachments:** Upload and manage working papers and supporting documents.
    *   **Audit Adjustments:** Record and manage proposed audit adjustments.
    *   **Reconciliations & Testing:** Perform and document reconciliation and testing procedures.
    *   **Exception Reports:** Generate and review reports on exceptions found during the audit.
    *   **Anomaly Detection:** Utilize tools for detecting anomalies in financial data.
    *   **Generate Reports:** Create various audit reports.
    *   **Compliance & Standards:** Work with compliance checklists and ensure adherence to auditing standards.
    *   **Internal Controls:** Document and assess the client's internal control environment.
    *   **KPI Dashboard:** Monitor key performance indicators related to the audit.

### 3.3. Reviewer

The Reviewer role is responsible for overseeing the work of auditors, ensuring quality, compliance, and accuracy of audit engagements.

**Key Responsibilities:**

*   **Engagements for Review:**
    *   View a list of all audit engagements assigned to them for review.
    *   Access detailed information for each assigned engagement.
*   **Review Modules (within an assigned engagement):**
    *   **Review Working Papers:** Examine working papers and supporting documentation.
    *   **Review Audit Adjustments:** Review proposed audit adjustments.
    *   **Review Risk Assessment:** Evaluate the auditor's risk assessment.
    *   **Review Materiality Calculations:** Verify materiality calculations.
    *   **Review Reconciliations:** Check reconciliation and testing procedures.
    *   **Review Exception Reports:** Analyze exception reports.
    *   **Approve Reports:** Approve final audit reports.
    *   **Review Compliance Checklists:** Verify completion and accuracy of compliance checklists.
    *   **Review Internal Controls:** Assess the documentation and evaluation of internal controls.

### 3.4. Client

The Client role provides a view into their ongoing audit engagements, allowing them to monitor progress and access relevant information.

**Key Responsibilities:**

*   **My Engagements:**
    *   View a list of all audit engagements associated with their client ID.
    *   Access key details for each engagement, including:
        *   Engagement ID, Year, Period, Type, Status
        *   Assigned Auditor
        *   Assigned Reviewer
    *   (Note: Clients typically have a read-only view of engagement progress and details, with no direct action capabilities within the engagement modules.)

---

## 4. Getting Started

### 4.1. Login

*   Navigate to the system's login page (`login.php`).
*   Enter your `username` and `password`.
*   Click `Login`.

### 4.2. Changing Default Passwords (Admin)

*   If you are logging in as the default admin user (`admin` / `password123`), it is highly recommended to change your password immediately after your first login for security reasons. This can typically be done via a profile management section (if available) or by an administrator.

---

## 5. Support

For any issues or further assistance, please contact your system administrator.
