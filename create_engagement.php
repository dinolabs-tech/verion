<?php

/**
 * File: create_engagement.php
 * Purpose: This file allows Auditors and Admins to create new engagements.
 * It displays a form to collect engagement details, validates the input, and saves the engagement to the database.
 */
include('components/head.php');
?>

<!DOCTYPE html>
<html lang="en">


<!-- Mirrored from www.urbanui.com/melody/template/pages/samples/blank-page.html by HTTrack Website Copier/3.x [XR&CO'2014], Sat, 15 Sep 2018 06:08:54 GMT -->
<?php include('components/head.php'); ?>

<body>
  <div class="container-scroller">
    <!-- partial:../../partials/_navbar.html -->
    <?php include('components/navbar.php'); ?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:../../partials/_settings-panel.html -->
      <div class="theme-setting-wrapper">
        <div id="settings-trigger"><i class="fas fa-fill-drip"></i></div>
        <div id="theme-settings" class="settings-panel">
          <i class="settings-close fa fa-times"></i>
          <p class="settings-heading">SIDEBAR SKINS</p>
          <div class="sidebar-bg-options selected" id="sidebar-light-theme">
            <div class="img-ss rounded-circle bg-light border mr-3"></div>Light
          </div>
          <div class="sidebar-bg-options" id="sidebar-dark-theme">
            <div class="img-ss rounded-circle bg-dark border mr-3"></div>Dark
          </div>
          <p class="settings-heading mt-2">HEADER SKINS</p>
          <div class="color-tiles mx-0 px-4">
            <div class="tiles primary"></div>
            <div class="tiles success"></div>
            <div class="tiles warning"></div>
            <div class="tiles danger"></div>
            <div class="tiles info"></div>
            <div class="tiles dark"></div>
            <div class="tiles default"></div>
          </div>
        </div>
      </div>

      <!-- partial -->
      <!-- partial:../../partials/_sidebar.html -->
      <?php include('components/sidebar.php'); ?>
      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="container">
            <h2>Create Engagement</h2>
            <?php
            // Include the database connection file.
            include 'database/db_connection.php';

            // Initialize variables to store form data and error messages.
            $engagement_name = $client_id = $start_date = $end_date = "";
            $engagement_name_err = $client_id_err = $start_date_err = $end_date_err = "";
            $success_message = "";

            // Check if the form has been submitted.
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
              // Validate engagement name
              /**
               * Validate the engagement name.
               * Check if the engagement name field is empty. If so, set an error message.
               * Otherwise, trim the input and store it in the $engagement_name variable.
               */
              if (empty(trim($_POST["engagement_name"]))) {
                $engagement_name_err = "Please enter an engagement name.";
              } else {
                $engagement_name = trim($_POST["engagement_name"]);
              }

              // Validate client ID
              /**
               * Validate the client ID.
               * Check if the client ID field is empty. If so, set an error message.
               * Otherwise, trim the input and store it in the $client_id variable.
               */
              if (empty(trim($_POST["client_id"]))) {
                $client_id_err = "Please select a client.";
              } else {
                $client_id = trim($_POST["client_id"]);
              }

              // Validate start date
              /**
               * Validate the start date.
               * Check if the start date field is empty. If so, set an error message.
               * Otherwise, trim the input and store it in the $start_date variable.
               */
              $start_date = trim($_POST["start_date"]);
              if (empty($start_date)) {
                $start_date_err = "Please enter a start date.";
              }

              // Validate end date
              /**
               * Validate the end date.
               * Check if the end date field is empty. If so, set an error message.
               * Otherwise, trim the input and store it in the $end_date variable.
               */
              $end_date = trim($_POST["end_date"]);
              if (empty($end_date)) {
                $end_date_err = "Please enter an end date.";
              }
              // Additional validation: Ensure end date is not before start date
              if (!empty($start_date) && !empty($end_date)) {
                if (strtotime($end_date) < strtotime($start_date)) {
                  $end_date_err = "End date cannot be before start date.";
                }
              }

              // Check input errors before inserting into database
              /**
               * Check for input errors before inserting the data into the database.
               * If there are no errors, proceed with the database insertion.
               */
              if (empty($engagement_name_err) && empty($client_id_err) && empty($start_date_err) && empty($end_date_err)) {
                // Prepare an SQL statement to insert a new engagement into the database.
                $sql = "INSERT INTO engagements (engagement_name, client_id, start_date, end_date) VALUES (?, ?, ?, ?)";

                // Prepare the SQL statement.
                if ($stmt = $conn->prepare($sql)) {
                  // Bind the parameters to the prepared statement.
                  $stmt->bind_param("siss", $param_engagement_name, $param_client_id, $param_start_date, $param_end_date);

                  // Set the parameters.
                  $param_engagement_name = $engagement_name;
                  $param_client_id = $client_id;
                  $param_start_date = $start_date;
                  $param_end_date = $end_date;

                  // Execute the prepared statement.
                  if ($stmt->execute()) {
                    // If the engagement was created successfully, set a success message and clear the form fields.
                    $success_message = "Engagement created successfully.";
                    // Clear form fields
                    $engagement_name = $client_id = $start_date = $end_date = "";
                  } else {
                    // If there was an error, display an error message.
                    echo "Something went wrong. Please try again later.";
                  }
                  // Close the prepared statement.
                  $stmt->close();
                }
              }
            }

            // Fetch clients for dropdown
            /**
             * Prepare a SQL statement to fetch all clients from the database.
             */
            $clients_sql = "SELECT client_id, client_name FROM clients";
            // Execute the query.
            $clients_result = $conn->query($clients_sql);
            ?>

            <?php if (!empty($success_message)): ?>
              <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
              <div class="form-group">
                <label>Engagement Name</label>
                <input type="text" name="engagement_name" class="form-control <?php echo (!empty($engagement_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $engagement_name; ?>">
                <span class="invalid-feedback"><?php echo $engagement_name_err; ?></span>
              </div>
              <div class="form-group">
                <label>Client</label>
                <select name="client_id" class="form-control <?php echo (!empty($client_id_err)) ? 'is-invalid' : ''; ?>">
                  <option value="">Select Client</option>
                  <?php
                  // Populate the client dropdown with data from the database.
                  if ($clients_result->num_rows > 0) {
                    while ($row = $clients_result->fetch_assoc()) {
                      echo "<option value='" . $row["client_id"] . "'" . ($client_id == $row["client_id"] ? "selected" : "") . ">" . $row["client_name"] . "</option>";
                    }
                  }
                  ?>
                </select>
                <span class="invalid-feedback"><?php echo $client_id_err; ?></span>
              </div>
              <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control <?php echo (!empty($start_date_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $start_date; ?>">
                <span class="invalid-feedback"><?php echo $start_date_err; ?></span>
              </div>
              <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" class="form-control <?php echo (!empty($end_date_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $end_date; ?>">
                <span class="invalid-feedback"><?php echo $end_date_err; ?></span>
              </div>
              <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Create Engagement">
              </div>
            </form>
            <?php
            // Close the database connection.
            $conn->close();
            ?>
          </div>
        </div>
        <!-- content-wrapper ends -->
        <!-- partial:../../partials/_footer.html -->
        <?php include('components/footer.php'); ?>
        <!-- partial -->
      </div>
      <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
  </div>
  <!-- container-scroller -->
  <!-- plugins:js -->
  <?php include('components/script.php'); ?>
  <!-- endinject -->
  <!-- Custom js for this page-->
  <!-- End custom js for this page-->
</body>


<!-- Mirrored from www.urbanui.com/melody/template/pages/samples/blank-page.html by HTTrack Website Copier/3.x [XR&CO'2014], Sat, 15 Sep 2018 06:08:54 GMT -->

</html>