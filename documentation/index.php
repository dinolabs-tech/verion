<?php
session_start();
?>
<!doctype html>
<html class="no-js " lang="en">


<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="description" content="Verion Documentation.">
    <title>Verion Documentation</title>
    <link rel="icon" href="logo-dark.ico" type="image/x-icon">
    <link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/jvectormap/jquery-jvectormap-2.0.3.min.css" />
    <link rel="stylesheet" href="assets/plugins/charts-c3/plugin.css" />

    <link rel="stylesheet" href="assets/plugins/morrisjs/morris.min.css" />
    <!-- Custom Css -->
    <link rel="stylesheet" href="assets/css/style.min.css">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">

    <style>
        html {
            scroll-behavior: smooth;
        }

        /* Back to Top Button Styles */
        #backToTopBtn {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Fixed position */
            bottom: 22px;
            /* Place the button at the bottom of the page */
            right: 30px;
            /* Place the button at the right of the page */
            z-index: 99;
            /* Make sure it does not overlap */
            border: none;
            /* Remove borders */
            outline: none;
            /* Remove outline */
            background-color: #007bff;
            /* Set a background color */
            color: white;
            /* Text color */
            cursor: pointer;
            /* Add a mouse pointer on hover */
            padding: 5px;
            /* Some padding */
            border-radius: 10px;
            /* Rounded corners */
            font-size: 14px;
            /* Increase font size */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            /* Add a subtle shadow */
            transition: background-color 0.3s, opacity 0.3s;
            /* Smooth transition for hover effects */
            height: 40px;
            /* Set a fixed height */
            width: 40px;
            /* Set a fixed width */
        }

        #backToTopBtn:hover {
            background-color: #0056b3;
            /* Darker background on hover */
            opacity: 0.9;
        }

        li.open a {
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>

<body class="theme-blush">

    <!-- Left Sidebar -->
    <aside id="leftsidebar" class="sidebar">
        <div class="navbar-brand">
            <button class="btn-menu ls-toggle-btn" type="button"><i class="zmdi zmdi-menu"></i></button>
        </div>
        <div class="menu">
            <ul class="list">
                <li class="open"><a href="../dashboard.php"><i class="zmdi zmdi-home"></i><span>Back to Dashboard</span></a></li>
                <li class="open"><a href="#authentication-user-management"><i class="zmdi zmdi-accounts"></i><span>Authentication & User Management</span></a></li>
                <li class="open"><a href="#client-management"><i class="zmdi zmdi-account-box-mail"></i><span>Client Management</span></a></li>
                <li class="open"><a href="#engagement-management"><i class="zmdi zmdi-assignment"></i><span>Engagement Management</span></a></li>
                <li class="open"><a href="#audit-modules"><i class="zmdi zmdi-folder-star-alt"></i><span>Audit Modules</span></a></li>
                <li class="open"><a href="#reviewer-modules"><i class="zmdi zmdi-check-circle"></i><span>Reviewer Modules</span></a></li>
                <li class="open"><a href="#client-specific-modules"><i class="zmdi zmdi-case"></i><span>Client Specific Modules</span></a></li>
            </ul>
        </div>
    </aside>


    <!-- Main Content -->

    <section class="content">
        <div class="">

            <!-- INTRODUCTION -->
            <div class="block-header">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <h2>Verion Documentation</h2>
                        <p class="lead mt-2">This document outlines the features and usage of the Verion Audit Management Software.</p>

                        <button class="btn btn-primary btn-icon mobile_menu d-lg-none d-md-none" type="button"><i class="zmdi zmdi-sort-amount-desc"></i></button>
                    </div>
                </div>
            </div>
            
            <!-- INTRODUCTION ENDS HERE -->
            <div class="container-fluid">
                <div class="row clearfix">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="header ms-4">
                                <h2><strong>Verion</strong> Documentation</h2>
                            </div>
                            <div class="body">
                                <div class="accordion" id="mainDocumentationAccordion">
                                    <!-- 1. Authentication & User Management -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingAuthenticationUserManagement">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#authentication-user-management" aria-expanded="true" aria-controls="authentication-user-management">
                                                1. Authentication & User Management
                                            </button>
                                        </h2>
                                        <div id="authentication-user-management" class="accordion-collapse collapse show" aria-labelledby="headingAuthenticationUserManagement" data-bs-parent="#mainDocumentationAccordion">
                                            <div class="accordion-body">
                                                <!-- 1.1. Login -->
                                                <h5 class="mt-4">1.1. Login</h5>
                                                <p><strong>Purpose:</strong> Allows users to securely log into the system.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Access the Login page in your browser.</li>
                                                    <li>Enter your <code>username</code> and <code>password</code>.</li>
                                                    <li>The system validates credentials and redirects to the <code>dashboard.php</code> upon successful login.</li>
                                                    <li>Failed login attempts are logged.</li>
                                                </ul>

                                                <!-- 1.2. Register User -->
                                                <h5 class="mt-4">1.2. Register User</h5>
                                                <p><strong>Purpose:</strong> Allows an Admin to create new user accounts with various roles and detailed demographic information.</p>
                                                <p><strong>Access:</strong> Only accessible by users with the 'Admin' role.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Navigate to the Register User page.</li>
                                                    <li>Fill in the user details:
                                                        <ul>
                                                            <li><code>Username</code></li>
                                                            <li><code>Email</code></li>
                                                            <li><code>Password</code> and <code>Confirm Password</code> (must match and meet complexity requirements)</li>
                                                            <li><code>First Name</code>, <code>Last Name</code>, <code>Date of Birth</code>, <code>Gender</code>, <code>Nationality</code>, <code>Marital Status</code>, <code>Phone Number</code></li>
                                                            <li><code>Street Address</code>, <code>City</code>, <code>State/Province</code>, <code>Zip Code</code>, <code>Country</code></li>
                                                            <li><code>Occupation</code>, <code>Company</code>, <code>Education Level</code>, <code>Time Zone</code>, <code>Preferred Language</code></li>
                                                            <li><code>Role</code> (Admin, Auditor, Reviewer, Client)</li>
                                                            <li>If 'Client' role is selected, an optional <code>Client</code> dropdown appears to assign the user to an existing client.</li>
                                                        </ul>
                                                    </li>
                                                    <li>Click the save button to register the user.</li>
                                                </ul>

                                                <!-- 1.3. Manage Users -->
                                                <h5 class="mt-4">1.3. Manage Users</h5>
                                                <p><strong>Purpose:</strong> Allows an Admin to view, edit, and delete existing user accounts.</p>
                                                <p><strong>Access:</strong> Only accessible by users with the 'Admin' role.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Navigate to the Manage Users page.</li>
                                                    <li>A table displays all registered users with their details.</li>
                                                    <li><strong>Edit User:</strong> Click the 'Edit' button next to a user to open a modal.
                                                        <ul>
                                                            <li>Modify user details including <code>Username</code>, <code>Email</code>, <code>Role</code>, <code>Status</code> (active/inactive), and demographic information.</li>
                                                            <li>If the role is changed to 'Client', the 'Assign Client' dropdown becomes visible.</li>
                                                            <li>Click 'Save changes' to update the user.</li>
                                                        </ul>
                                                    </li>
                                                    <li><strong>Delete User:</strong> Click the 'Delete' button next to a user to remove their account. A confirmation prompt will appear.</li>
                                                </ul>

                                                <!-- 1.4. Profile -->
                                                <h5 class="mt-4">1.4. Profile</h5>
                                                <p><strong>Purpose:</strong> Allows a logged-in user to view and update their own profile information, including personal details and password.</p>
                                                <p><strong>Access:</strong> Accessible by all authenticated users.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Navigate to your Profile page.</li>
                                                    <li>View your current <code>Username</code>, <code>Email</code>, <code>Role</code>, <code>Account Status</code>, <code>Member Since</code>, <code>Last Updated</code>, <code>Last Login</code>, and detailed demographic information.</li>
                                                    <li><strong>Edit Profile:</strong> Click the 'Edit' button to enter edit mode.
                                                        <ul>
                                                            <li>Update your <code>Email</code>, <code>New Password</code> (leave blank to keep current), <code>Confirm New Password</code>.</li>
                                                            <li>Update demographic information such as <code>First Name</code>, <code>Last Name</code>, <code>Date of Birth</code>, <code>Gender</code>, <code>Nationality</code>, <code>Marital Status</code>, <code>Phone Number</code>, <code>Address</code>, <code>Occupation</code>, <code>Company</code>, <code>Education Level</code>, <code>Time Zone</code>, and <code>Preferred Language</code>.</li>
                                                            <li>Click 'Update Profile' to save changes or 'Cancel' to discard.</li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 2. Client Management -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingClientManagement">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#client-management" aria-expanded="false" aria-controls="client-management">
                                                2. Client Management
                                            </button>
                                        </h2>
                                        <div id="client-management" class="accordion-collapse collapse" aria-labelledby="headingClientManagement" data-bs-parent="#mainDocumentationAccordion">
                                            <div class="accordion-body">
                                                <!-- 2.1. Manage Clients -->
                                                <h5 class="mt-4">2.1. Manage Clients</h5>
                                                <p><strong>Purpose:</strong> Allows an Admin to add, edit, and delete client records.</p>
                                                <p><strong>Access:</strong> Only accessible by users with the 'Admin' role.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Navigate to the Manage Clients page.</li>
                                                    <li><strong>Add New Client:</strong>
                                                        <ul>
                                                            <li>Fill in <code>Client Name</code>, <code>Contact Person</code>, <code>Contact Email</code>, <code>Contact Phone</code>, and <code>Address</code> in the "Add New Client" form.</li>
                                                            <li>Click the save button to add the client.</li>
                                                        </ul>
                                                    </li>
                                                    <li><strong>Existing Clients:</strong> A table lists all registered clients.
                                                        <ul>
                                                            <li><strong>Edit Client:</strong> Click the 'Edit' button next to a client to open a modal.
                                                                <ul>
                                                                    <li>Modify <code>Client Name</code>, <code>Contact Person</code>, <code>Contact Email</code>, <code>Contact Phone</code>, and <code>Address</code>.</li>
                                                                    <li>Click 'Save changes' to update the client.</li>
                                                                </ul>
                                                            </li>
                                                            <li><strong>Delete Client:</strong> Click the 'Delete' button next to a client to remove their record. A confirmation prompt will appear.</li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 3. Engagement Management -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingEngagementManagement">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#engagement-management" aria-expanded="false" aria-controls="engagement-management">
                                                3. Engagement Management
                                            </button>
                                        </h2>
                                        <div id="engagement-management" class="accordion-collapse collapse" aria-labelledby="headingEngagementManagement" data-bs-parent="#mainDocumentationAccordion">
                                            <div class="accordion-body">
                                                <!-- 3.1. Dashboard -->
                                                <h5 class="mt-4">3.1. Dashboard</h5>
                                                <p><strong>Purpose:</strong> Provides an overview of key metrics and quick links based on the user's role.</p>
                                                <p><strong>Access:</strong> Accessible by all authenticated users.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Upon login, users are redirected to the Dashboard.</li>
                                                    <li><strong>Admin:</strong> Displays total users, engagements, and clients. Provides quick links to <code>Register New User</code>, <code>Manage Clients</code>, <code>Manage Engagements</code>.</li>
                                                    <li><strong>Auditor:</strong> Displays assigned engagements and open queries. Provides quick links to <code>My Engagements</code>, <code>Open Queries</code>.</li>
                                                    <li><strong>Reviewer:</strong> Displays engagements assigned for review. Provides quick links to <code>Engagements for Review</code>.</li>
                                                    <li><strong>Client:</strong> Displays their assigned engagements. Provides quick links to <code>View My Engagements</code>.</li>
                                                    <li>All roles have a 'Logout' link.</li>
                                                </ul>

                                                <!-- 3.2. Create Engagement -->
                                                <h5 class="mt-4">3.2. Create Engagement</h5>
                                                <p><strong>Purpose:</strong> Allows Auditors and Admins to create new audit engagements.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Navigate to the Create Engagement page.</li>
                                                    <li>Fill in engagement details:
                                                        <ul>
                                                            <li><code>Engagement Name</code></li>
                                                            <li><code>Client</code> (select from dropdown of existing clients)</li>
                                                            <li><code>Engagement Year</code></li>
                                                            <li><code>Period</code> (e.g., FY2023, Q4 2023)</li>
                                                            <li><code>Engagement Type</code> (Internal/External)</li>
                                                            <li><code>Assigned Auditor</code> (optional, select from dropdown of Auditors)</li>
                                                            <li><code>Assigned Reviewer</code> (optional, select from dropdown of Reviewers)</li>
                                                        </ul>
                                                    </li>
                                                    <li>Click 'Create Engagement' to save the new engagement.</li>
                                                </ul>

                                                <!-- 3.3. Manage Engagements -->
                                                <h5 class="mt-4">3.3. Manage Engagements</h5>
                                                <p><strong>Purpose:</strong> Allows an Admin to add, edit, and delete audit engagements.</p>
                                                <p><strong>Access:</strong> Only accessible by users with the 'Admin' role.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Navigate to the Manage Engagements page.</li>
                                                    <li><strong>Add New Engagement:</strong>
                                                        <ul>
                                                            <li>Fill in <code>Engagement Name</code>, <code>Client</code>, <code>Engagement Year</code>, <code>Period</code>, <code>Engagement Type</code>, <code>Assigned Auditor</code>, <code>Assigned Reviewer</code>.</li>
                                                            <li>Click the save button to add the engagement.</li>
                                                        </ul>
                                                    </li>
                                                    <li><strong>Existing Engagements:</strong> A table lists all engagements.
                                                        <ul>
                                                            <li><strong>Edit Engagement:</strong> Click the 'Edit' button next to an engagement to open a modal.
                                                                <ul>
                                                                    <li>Modify <code>Engagement Name</code>, <code>Client</code>, <code>Year</code>, <code>Period</code>, <code>Type</code>, <code>Status</code>, <code>Auditor</code>, <code>Reviewer</code>.</li>
                                                                    <li>Click 'Save changes' to update the engagement.</li>
                                                                </ul>
                                                            </li>
                                                            <li><strong>Delete Engagement:</strong> Click the 'Delete' button next to an engagement to remove it. A confirmation prompt will appear.</li>
                                                        </ul>
                                                    </li>
                                                </ul>

                                                <!-- 3.4. My Engagements -->
                                                <h5 class="mt-4">3.4. My Engagements</h5>
                                                <p><strong>Purpose:</strong> Displays a list of engagements assigned to the logged-in Auditor or all engagements for an Admin.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Navigate to the My Engagements page.</li>
                                                    <li>A table lists engagements. Auditors see only their assigned engagements, while Admins see all.</li>
                                                    <li>Click the 'List' button next to an engagement to view its Engagement Details.</li>
                                                </ul>

                                                <!-- 3.5. Engagement Details -->
                                                <h5 class="mt-4">3.5. Engagement Details</h5>
                                                <p><strong>Purpose:</strong> Provides a detailed view of a specific audit engagement and links to various modules related to it.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles (Auditors can only view their assigned engagements).</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed by clicking on an engagement from the My Engagements page.</li>
                                                    <li>Displays <code>Client Name</code>, <code>Engagement Year</code>, <code>Period</code>, <code>Type</code>, <code>Status</code>, <code>Assigned Auditor</code>, and <code>Assigned Reviewer</code>.</li>
                                                    <li><strong>Edit Engagement:</strong> An 'Edit' button allows Admins and assigned Auditors to modify engagement details via a modal (similar to Manage Engagements edit functionality).</li>
                                                    <li><strong>Engagement Modules:</strong> Provides quick links to various audit modules:
                                                        <ul>
                                                            <li><code>View Trial Balance</code></li>
                                                            <li><code>Upload Trial Balance</code></li>
                                                            <li><code>View Balance Sheet</code></li>
                                                            <li><code>Risk Assessment</code></li>
                                                            <li><code>Materiality Calculator</code></li>
                                                            <li><code>Audit Sampling</code></li>
                                                            <li><code>Queries & Responses</code></li>
                                                            <li><code>Working Paper Attachments</code></li>
                                                            <li><code>Audit Adjustments</code></li>
                                                            <li><code>Reconciliations & Testing</code></li>
                                                            <li><code>Exception Reports</code></li>
                                                            <li><code>Anomaly Detection</code></li>
                                                            <li><code>Generate Reports</code></li>
                                                            <li><code>Compliance & Standards</code></li>
                                                            <li><code>Internal Controls</code></li>
                                                            <li><code>KPI Dashboard</code></li>
                                                        </ul>
                                                    </li>
                                                    <li>A 'Back to My Engagements' button returns to the previous page.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 4. Audit Modules (Auditor/Admin Access) -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingAuditModules">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#audit-modules" aria-expanded="false" aria-controls="audit-modules">
                                                4. Audit Modules (Auditor/Admin Access)
                                            </button>
                                        </h2>
                                        <div id="audit-modules" class="accordion-collapse collapse" aria-labelledby="headingAuditModules" data-bs-parent="#mainDocumentationAccordion">
                                            <div class="accordion-body">
                                                <!-- 4.1. View Trial Balance -->
                                                <h5 class="mt-4">4.1. View Trial Balance</h5>
                                                <p><strong>Purpose:</strong> Displays the uploaded trial balance for a specific engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Engagement Details page.</li>
                                                    <li>Shows a table of account codes, names, debits, and credits.</li>
                                                </ul>

                                                <!-- 4.2. Upload Trial Balance -->
                                                <h5 class="mt-4">4.2. Upload Trial Balance</h5>
                                                <p><strong>Purpose:</strong> Allows Auditors and Admins to upload a trial balance for an engagement, either manually or via a CSV file.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Engagement Details page.</li>
                                                    <li><strong>Manual Entry:</strong> Fill in <code>Account Code</code>, <code>Account Name</code>, <code>Debit</code>, <code>Credit</code> and click 'Add Account'.</li>
                                                    <li><strong>CSV Upload:</strong> Select a CSV file (using the provided template) and click 'Upload CSV'.</li>
                                                    <li>Existing trial balance entries are listed and can be edited or deleted.</li>
                                                </ul>

                                                <!-- 4.3. View Balance Sheet -->
                                                <h5 class="mt-4">4.3. View Balance Sheet</h5>
                                                <p><strong>Purpose:</strong> Displays a simplified balance sheet based on the uploaded trial balance data for an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Engagement Details page.</li>
                                                    <li>Presents assets, liabilities, and equity, with a check for balance.</li>
                                                </ul>

                                                <!-- 4.4. Risk Assessment -->
                                                <h5 class="mt-4">4.4. Risk Assessment</h5>
                                                <p><strong>Purpose:</strong> Allows Auditors and Admins to document and manage risk assessments for an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Engagement Details page.</li>
                                                    <li><strong>Add New Risk Assessment:</strong>
                                                        <ul>
                                                            <li>Fill in <code>Risk Area</code>, <code>Inherent Risk</code>, <code>Control Risk</code>, <code>Detection Risk</code>, <code>Overall Risk</code>, and <code>Mitigation Strategy</code>.</li>
                                                            <li>Click the add button to save.</li>
                                                        </ul>
                                                    </li>
                                                    <li>Existing risk assessments are listed and can be edited or deleted.</li>
                                                </ul>

                                                <!-- 4.5. Materiality Calculator -->
                                                <h5 class="mt-4">4.5. Materiality Calculator</h5>
                                                <p><strong>Purpose:</strong> Allows Auditors and Admins to calculate and document materiality levels for an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Engagement Details page.</li>
                                                    <li><strong>Add New Materiality Calculation:</strong>
                                                        <ul>
                                                            <li>Fill in <code>Basis</code> (e.g., Revenue, Total Assets), <code>Percentage</code>, <code>Calculated Amount</code>, <code>Performance Materiality</code>, and <code>Trivial Amount</code>.</li>
                                                            <li>Click the add button to save.</li>
                                                        </ul>
                                                    </li>
                                                    <li>Existing calculations are listed and can be edited or deleted.</li>
                                                </ul>

                                                <!-- 4.6. Audit Sampling -->
                                                <h5 class="mt-4">4.6. Audit Sampling</h5>
                                                <p><strong>Purpose:</strong> Provides tools for auditors to perform audit sampling. (Note: The provided code for Audit Sampling is a placeholder and does not contain functional sampling logic. It would typically involve selecting sample sizes, methods, and evaluating results.)</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Engagement Details page.</li>
                                                    <li>Currently displays a placeholder message.</li>
                                                </ul>

                                                <!-- 4.7. Queries & Responses -->
                                                <h5 class="mt-4">4.7. Queries & Responses</h5>
                                                <p><strong>Purpose:</strong> Facilitates communication between auditors, clients, and reviewers by allowing users to raise and respond to queries related to an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by all authenticated users. Permissions vary by role:
                                                    <ul>
                                                        <li><strong>Auditor/Admin:</strong> Can raise new queries, and edit/respond to any query.</li>
                                                        <li><strong>Client/Reviewer:</strong> Can respond to queries raised to them.</li>
                                                    </ul>
                                                </p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Engagement Details page (for Auditor/Admin) or the Open Queries page (for Client/Reviewer).</li>
                                                    <li><strong>Raise New Query:</strong>
                                                        <ul>
                                                            <li>Select <code>Engagement</code>, <code>Raise Query To</code> (another user), and enter <code>Query Text</code>.</li>
                                                            <li>Click the add button to send the query.</li>
                                                        </ul>
                                                    </li>
                                                    <li><strong>Existing Queries:</strong> A table lists all queries for the engagement.
                                                        <ul>
                                                            <li><strong>Respond/Edit Query:</strong> Click the 'List' button next to a query to open a modal.
                                                                <ul>
                                                                    <li>Auditors/Admins can edit <code>Query Text</code>, <code>Raised To</code> user, <code>Response</code>, and <code>Status</code>.</li>
                                                                    <li>Clients/Reviewers can only add/edit their <code>Response</code> and change the <code>Status</code> to 'Responded' for queries raised to them.</li>
                                                                    <li>Queries are automatically marked 'opened' when viewed by the recipient.</li>
                                                                    <li>Click the save button to update.</li>
                                                                </ul>
                                                            </li>
                                                            <li><strong>Delete Query:</strong> Auditors/Admins can delete queries.</li>
                                                        </ul>
                                                    </li>
                                                </ul>

                                                <!-- 4.8. Working Paper Attachments -->
                                                <h5 class="mt-4">4.8. Working Paper Attachments</h5>
                                                <p><strong>Purpose:</strong> Allows Auditors and Admins to upload and manage supporting documents (working papers) for an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Engagement Details page.</li>
                                                    <li><strong>Upload New Working Paper:</strong>
                                                        <ul>
                                                            <li>Enter <code>Title</code>, <code>Description</code>, and select a <code>File</code> to upload.</li>
                                                            <li>Click 'Upload Paper'.</li>
                                                        </ul>
                                                    </li>
                                                    <li>Existing working papers are listed. Files can be downloaded, edited (title/description), or deleted.</li>
                                                </ul>

                                                <!-- 4.9. Audit Adjustments -->
                                                <h5 class="mt-4">4.9. Audit Adjustments</h5>
                                                <p><strong>Purpose:</strong> Allows Auditors and Admins to record and manage audit adjustments for an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Engagement Details page.</li>
                                                    <li><strong>Add New Adjustment:</strong>
                                                        <ul>
                                                            <li>Enter <code>Account Code</code>, <code>Description</code>, <code>Debit</code> amount, and <code>Credit</code> amount.</li>
                                                            <li>Click the add button to save.</li>
                                                        </ul>
                                                    </li>
                                                    <li>Existing adjustments are listed and can be edited or deleted.</li>
                                                </ul>

                                                <!-- 4.10. Reconciliations & Testing -->
                                                <h5 class="mt-4">4.10. Reconciliations & Testing</h5>
                                                <p><strong>Purpose:</strong> Allows Auditors and Admins to document and manage various reconciliations and testing procedures for an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Engagement Details page.</li>
                                                    <li><strong>Add New Reconciliation:</strong>
                                                        <ul>
                                                            <li>Select <code>Reconciliation Type</code> (Bank, Debtor, Creditor, Inventory, Other).</li>
                                                            <li>Enter <code>Description</code>, <code>Reconciled Amount</code>, <code>Difference</code>, and <code>Status</code> (Pending, Reconciled, Discrepancy).</li>
                                                            <li>Click the add button to save.</li>
                                                        </ul>
                                                    </li>
                                                    <li>Existing reconciliations are listed and can be edited or deleted.</li>
                                                </ul>

                                                <!-- 4.11. Exception Reports -->
                                                <h5 class="mt-4">4.11. Exception Reports</h5>
                                                <p><strong>Purpose:</strong> Allows Auditors and Admins to raise and manage exception reports for an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Engagement Details page.</li>
                                                    <li><strong>Add New Exception Report:</strong>
                                                        <ul>
                                                            <li>Enter <code>Title</code>, <code>Description</code>, select <code>Severity</code> (Low, Medium, High, Critical), and <code>Status</code> (Open, Resolved, Acknowledged).</li>
                                                            <li>Click the add button to save.</li>
                                                        </ul>
                                                    </li>
                                                    <li>Existing reports are listed and can be edited or deleted.</li>
                                                </ul>

                                                <!-- 4.12. Anomaly Detection -->
                                                <h5 class="mt-4">4.12. Anomaly Detection</h5>
                                                <p><strong>Purpose:</strong> Provides a placeholder for advanced anomaly detection features. (Note: The provided code for Anomaly Detection is a placeholder and does not contain functional anomaly detection logic.)</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Engagement Details page.</li>
                                                    <li>Currently displays a placeholder message.</li>
                                                </ul>

                                                <!-- 4.13. Generate Reports -->
                                                <h5 class="mt-4">4.13. Generate Reports</h5>
                                                <p><strong>Purpose:</strong> Allows Auditors and Admins to view financial statements based on the adjusted trial balance and generate audit reports or management letters.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Engagement Details page.</li>
                                                    <li><strong>Financial Statements:</strong> Displays a simplified Balance Sheet and Income Statement derived from the adjusted trial balance. Includes a placeholder for Cash Flow Statement.</li>
                                                    <li><strong>Generate Audit Report / Management Letter:</strong>
                                                        <ul>
                                                            <li>Select <code>Report Type</code> (Audit Report, Management Letter) and <code>Version Type</code> (Draft, Final).</li>
                                                            <li>Enter <code>Report Title</code> and <code>Report Content</code>.</li>
                                                            <li>Click the save button to generate and save the report.</li>
                                                        </ul>
                                                    </li>
                                                </ul>

                                                <!-- 4.14. View Reports -->
                                                <h5 class="mt-4">4.14. View Reports</h5>
                                                <p><strong>Purpose:</strong> Allows Auditors and Admins to view all generated audit reports and management letters for an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Engagement Details page.</li>
                                                    <li>Displays a table of generated reports, including <code>Report Type</code>, <code>Version Type</code>, <code>Title</code>, <code>Generated By</code>, and <code>Generated At</code>.</li>
                                                    <li>Click the 'View' button next to a report to see its full content.</li>
                                                </ul>

                                                <!-- 4.15. Edit Report -->
                                                <h5 class="mt-4">4.15. Edit Report</h5>
                                                <p><strong>Purpose:</strong> Allows Auditors and Admins to edit existing audit reports and management letters.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the View Reports page by clicking the 'Edit' button next to a report.</li>
                                                    <li>Modify <code>Report Type</code>, <code>Version Type</code>, <code>Title</code>, and <code>Report Content</code>.</li>
                                                    <li>Click the save button to update the report.</li>
                                                </ul>

                                                <!-- 4.16. Download Report -->
                                                <h5 class="mt-4">4.16. Download Report</h5>
                                                <p><strong>Purpose:</strong> Allows Auditors and Admins to download generated audit reports and management letters as PDF files.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the View Reports page by clicking the 'Download' button next to a report.</li>
                                                    <li>The report will be downloaded as a PDF file to your local machine.</li>
                                                </ul>

                                                <!-- 4.17. Compliance & Standards -->
                                                <h5 class="mt-4">4.17. Compliance & Standards</h5>
                                                <p><strong>Purpose:</strong> Allows Auditors and Admins to manage compliance checklists for an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Engagement Details page.</li>
                                                    <li><strong>Add New Checklist Item:</strong>
                                                        <ul>
                                                            <li>Select <code>Standard Type</code> (e.g., IFRS, GAAP, Internal Policy).</li>
                                                            <li>Enter <code>Item Description</code>, indicate if <code>Is Compliant</code> (Yes/No), and add <code>Notes</code>.</li>
                                                            <li>Click the add button to save.</li>
                                                        </ul>
                                                    </li>
                                                    <li>Existing checklist items are listed and can be edited or deleted.</li>
                                                </ul>

                                                <!-- 4.18. Internal Controls -->
                                                <h5 class="mt-4">4.18. Internal Controls</h5>
                                                <p><strong>Purpose:</strong> Allows Auditors and Admins to document and assess internal controls for an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Engagement Details page.</li>
                                                    <li><strong>Add New Internal Control:</strong>
                                                        <ul>
                                                            <li>Enter <code>Control Name</code>, <code>Description</code>, select <code>Design Effectiveness</code> (Effective/Ineffective), and <code>Operating Effectiveness</code> (Effective/Ineffective).</li>
                                                            <li>Click the add button to save.</li>
                                                        </ul>
                                                    </li>
                                                    <li>Existing internal controls are listed and can be edited or deleted.</li>
                                                </ul>

                                                <!-- 4.19. KPI Dashboard -->
                                                <h5 class="mt-4">4.19. KPI Dashboard</h5>
                                                <p><strong>Purpose:</strong> Displays key performance indicators (KPIs) for an engagement, calculated from the trial balance data.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Auditor' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Engagement Details page.</li>
                                                    <li>Displays calculated KPIs such as <code>Current Ratio</code>, <code>Gross Profit Margin</code>, <code>Net Profit Margin</code>, along with their descriptions.</li>
                                                    <li>Requires trial balance data to be uploaded for calculations.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 5. Reviewer Modules (Reviewer/Admin Access) -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingReviewerModules">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#reviewer-modules" aria-expanded="false" aria-controls="reviewer-modules">
                                                5. Reviewer Modules (Reviewer/Admin Access)
                                            </button>
                                        </h2>
                                        <div id="reviewer-modules" class="accordion-collapse collapse" aria-labelledby="headingReviewerModules" data-bs-parent="#mainDocumentationAccordion">
                                            <div class="accordion-body">
                                                <!-- 5.1. Engagements for Review -->
                                                <h5 class="mt-4">5.1. Engagements for Review</h5>
                                                <p><strong>Purpose:</strong> Displays a list of engagements assigned to the logged-in Reviewer for review, or all engagements for an Admin.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Reviewer' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Navigate to the Engagements for Review page.</li>
                                                    <li>A table lists engagements. Reviewers see only their assigned engagements, while Admins see all.</li>
                                                    <li>Click the 'List' button next to an engagement to view its Review Engagement Details.</li>
                                                </ul>

                                                <!-- 5.2. Review Engagement Details -->
                                                <h5 class="mt-4">5.2. Review Engagement Details</h5>
                                                <p><strong>Purpose:</strong> Provides a detailed view of an engagement for review and links to specific review modules.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Reviewer' or 'Admin' roles (Reviewers can only view their assigned engagements).</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed by clicking on an engagement from the Engagements for Review page.</li>
                                                    <li>Displays <code>Client Name</code>, <code>Engagement Year</code>, <code>Period</code>, <code>Type</code>, <code>Status</code>, <code>Assigned Auditor</code>, and <code>Assigned Reviewer</code>.</li>
                                                    <li><strong>Review Modules:</strong> Provides quick links to various review-specific modules:
                                                        <ul>
                                                            <li><code>Review Working Papers</code></li>
                                                            <li><code>Review Audit Adjustments</code></li>
                                                            <li><code>Review Risk Assessment</code></li>
                                                            <li><code>Review Materiality Calculations</code></li>
                                                            <li><code>Review Reconciliations</code></li>
                                                            <li><code>Review Exception Reports</code></li>
                                                            <li><code>Approve Reports</code></li>
                                                            <li><code>Review Compliance Checklists</code></li>
                                                            <li><code>Review Internal Controls</code></li>
                                                        </ul>
                                                    </li>
                                                    <li>A 'Back to Engagements for Review' button returns to the previous page.</li>
                                                </ul>

                                                <!-- 5.3. Review Working Papers -->
                                                <h5 class="mt-4">5.3. Review Working Papers</h5>
                                                <p><strong>Purpose:</strong> Allows Reviewers and Admins to review uploaded working papers, add comments, and approve or reject them.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Reviewer' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Review Engagement Details page.</li>
                                                    <li>Lists all working papers for the engagement.</li>
                                                    <li><strong>Review Paper:</strong> Click the 'List' button next to a paper to open a modal.
                                                        <ul>
                                                            <li>View <code>Title</code>, <code>Description</code>, and download the <code>File</code>.</li>
                                                            <li>Add <code>Review Comments</code>.</li>
                                                            <li>Select <code>Review Status</code> (Approved/Rejected).</li>
                                                            <li>Click the save button to submit the review.</li>
                                                        </ul>
                                                    </li>
                                                </ul>

                                                <!-- 5.4. Review Audit Adjustments -->
                                                <h5 class="mt-4">5.4. Review Audit Adjustments</h5>
                                                <p><strong>Purpose:</strong> Allows Reviewers and Admins to view audit adjustments posted by auditors for an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Reviewer' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Review Engagement Details page.</li>
                                                    <li>Displays a table of audit adjustments, including <code>Account Code</code>, <code>Description</code>, <code>Debit</code>, <code>Credit</code>, <code>Posted By</code>, and <code>Posted At</code>.</li>
                                                </ul>

                                                <!-- 5.5. Review Risk Assessment -->
                                                <h5 class="mt-4">5.5. Review Risk Assessment</h5>
                                                <p><strong>Purpose:</strong> Allows Reviewers and Admins to view risk assessments documented by auditors for an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Reviewer' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Review Engagement Details page.</li>
                                                    <li>Displays a table of risk assessments, including <code>Risk Area</code>, <code>Inherent Risk</code>, <code>Control Risk</code>, <code>Detection Risk</code>, <code>Overall Risk</code>, <code>Mitigation Strategy</code>, <code>Assessed By</code>, and <code>Assessed At</code>.</li>
                                                </ul>

                                                <!-- 5.6. Review Materiality Calculations -->
                                                <h5 class="mt-4">5.6. Review Materiality Calculations</h5>
                                                <p><strong>Purpose:</strong> Allows Reviewers and Admins to view materiality calculations performed by auditors for an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Reviewer' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Review Engagement Details page.</li>
                                                    <li>Displays a table of materiality calculations, including <code>Basis</code>, <code>Percentage</code>, <code>Calculated Amount</code>, <code>Performance Materiality</code>, <code>Trivial Amount</code>, <code>Calculated By</code>, and <code>Calculated At</code>.</li>
                                                </ul>

                                                <!-- 5.7. Review Reconciliations -->
                                                <h5 class="mt-4">5.7. Review Reconciliations</h5>
                                                <p><strong>Purpose:</strong> Allows Reviewers and Admins to view reconciliations and testing performed by auditors for an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Reviewer' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Review Engagement Details page.</li>
                                                    <li>Displays a table of reconciliations, including <code>Type</code>, <code>Description</code>, <code>Reconciled Amount</code>, <code>Difference</code>, <code>Status</code>, <code>Reconciled By</code>, and <code>Reconciled At</code>.</li>
                                                </ul>

                                                <!-- 5.8. Review Exception Reports -->
                                                <h5 class="mt-4">5.8. Review Exception Reports</h5>
                                                <p><strong>Purpose:</strong> Allows Reviewers and Admins to view exception reports raised by auditors for an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Reviewer' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from the Review Engagement Details page.</li>
                                                    <li>Displays a table of exception reports, including <code>Title</code>, <code>Description</code>, <code>Severity</code>, <code>Status</code>, <code>Raised By</code>, <code>Raised At</code>, and <code>Resolved At</code>.</li>
                                                </ul>

                                                <!-- 5.9. Approve Reports -->
                                                <h5 class="mt-4">5.9. Approve Reports</h5>
                                                <p><strong>Purpose:</strong> Allows Reviewers and Admins to review and approve/reject generated audit reports and management letters.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Reviewer' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from <code>review_engagement_details.php</code>.</li>
                                                    <li>Lists generated reports (Audit Reports and Management Letters).</li>
                                                    <li><strong>Review Report:</strong> Click the 'Review' button next to a report to open a modal.
                                                        <ul>
                                                            <li>View <code>Report Type</code>, <code>Version Type</code>, <code>Title</code>, <code>Content</code>, <code>Generated By</code>, and <code>Generated At</code>.</li>
                                                            <li>Add <code>Review Comments</code>.</li>
                                                            <li>Select <code>Approval Status</code> (Approved/Rejected).</li>
                                                            <li>Click the save button to submit the approval.</li>
                                                        </ul>
                                                    </li>
                                                </ul>

                                                <!-- 5.10. Review Compliance Checklists -->
                                                <h5 class="mt-4">5.10. Review Compliance Checklists</h5>
                                                <p><strong>Purpose:</strong> Allows Reviewers and Admins to view compliance checklist items documented by auditors for an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Reviewer' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from <code>review_engagement_details.php</code>.</li>
                                                    <li>Displays a table of compliance checklist items, including <code>Standard Type</code>, <code>Description</code>, <code>Compliant?</code>, <code>Notes</code>, <code>Reviewed By</code>, and <code>Reviewed At</code>.</li>
                                                </ul>

                                                <!-- 5.11. Review Internal Controls -->
                                                <h5 class="mt-4">5.11. Review Internal Controls</h5>
                                                <p><strong>Purpose:</strong> Allows Reviewers and Admins to view internal controls documented and assessed by auditors for an engagement.</p>
                                                <p><strong>Access:</strong> Accessible by users with 'Reviewer' or 'Admin' roles.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Accessed from <code>review_engagement_details.php</code>.</li>
                                                    <li>Displays a table of internal controls, including <code>Control Name</code>, <code>Description</code>, <code>Design Effectiveness</code>, <code>Operating Effectiveness</code>, <code>Tested By</code>, and <code>Tested At</code>.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 6. Client Specific Modules -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingClientSpecificModules">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#client-specific-modules" aria-expanded="false" aria-controls="client-specific-modules">
                                                6. Client Specific Modules
                                            </button>
                                        </h2>
                                        <div id="client-specific-modules" class="accordion-collapse collapse" aria-labelledby="headingClientSpecificModules" data-bs-parent="#mainDocumentationAccordion">
                                            <div class="accordion-body">
                                                <!-- 6.1. Client Engagements -->
                                                <h5 class="mt-4">6.1. Client Engagements</h5>
                                                <p><strong>Purpose:</strong> Displays a list of engagements associated with the logged-in Client.</p>
                                                <p><strong>Access:</strong> Only accessible by users with the 'Client' role.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Navigate to <code>client_my_engagements.php</code>.</li>
                                                    <li>A table lists engagements where the client is involved.</li>
                                                    <li>Click the 'View' button next to an engagement to see its details.</li>
                                                </ul>

                                                <!-- 6.2. Respond to Queries -->
                                                <h5 class="mt-4">6.2. Respond to Queries</h5>
                                                <p><strong>Purpose:</strong> Allows Clients to view and respond to queries raised to them by auditors or reviewers.</p>
                                                <p><strong>Access:</strong> Only accessible by users with the 'Client' role.</p>
                                                <p><strong>Usage:</strong></p>
                                                <ul>
                                                    <li>Navigate to <code>respond_to_queries.php</code>.</li>
                                                    <li>Lists queries raised to the client.</li>
                                                    <li><strong>Respond:</strong> Click the 'Respond' button next to a query to open a modal.
                                                        <ul>
                                                            <li>View the <code>Query Text</code>.</li>
                                                            <li>Enter your <code>Response</code>.</li>
                                                            <li>Click 'Submit Response'.</li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Jquery Core Js -->
    <script src="assets/bundles/libscripts.bundle.js"></script> <!-- Lib Scripts Plugin Js ( jquery.v3.2.1, Bootstrap4 js) -->
    <script src="assets/bundles/vendorscripts.bundle.js"></script> <!-- slimscroll, waves Scripts Plugin Js -->

    <script src="assets/bundles/jvectormap.bundle.js"></script> <!-- JVectorMap Plugin Js -->
    <script src="assets/bundles/sparkline.bundle.js"></script> <!-- Sparkline Plugin Js -->
    <script src="assets/bundles/c3.bundle.js"></script>

    <script src="assets/bundles/mainscripts.bundle.js"></script>
    <script src="assets/js/pages/index.js"></script>
    <script src="assets/bootstrap/js/bootstrap.bundle.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('#leftsidebar .menu .list a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();

                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);

                    if (targetElement) {
                        // Find the parent accordion-collapse element
                        const accordionCollapse = targetElement.closest('.accordion-collapse');
                        if (accordionCollapse) {
                            const bsCollapse = new bootstrap.Collapse(accordionCollapse, {
                                toggle: false
                            });
                            bsCollapse.show();

                            // Scroll to the target element after a short delay to allow accordion to open
                            setTimeout(() => {
                                targetElement.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'start'
                                });
                            }, 300); // Adjust delay as needed
                        } else {
                            // If not inside an accordion, just scroll
                            targetElement.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    }
                });
            });

            // Back to Top Button functionality
            var mybutton = document.getElementById("backToTopBtn");

            // When the user scrolls down 20px from the top of the document, show the button
            window.onscroll = function() {
                scrollFunction()
            };

            function scrollFunction() {
                if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                    mybutton.style.display = "block";
                } else {
                    mybutton.style.display = "none";
                }
            }

            // When the user clicks on the button, scroll to the top of the document
            mybutton.addEventListener('click', function() {
                document.body.scrollTop = 0; // For Safari
                document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
            });
        });
    </script>
    <button onclick="topFunction()" id="backToTopBtn" title="Go to top" class="btn btn-primary btn-sm btn-circle"><i class="zmdi zmdi-chevron-up"></i></button>
</body>


</html>
