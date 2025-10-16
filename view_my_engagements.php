<?php
session_start();
include('components/head.php');
?>

<!DOCTYPE html>
<html lang="en">
<?php include('component/head.php'); ?>

<body>
  <div class="wrapper">
    <?php include('component/sidebar.php'); ?>

    <div class="main-panel">
      <?php include('component/navbar.php'); ?>

      <div class="container">
        <div class="page-inner">
          <div class="container">
            <h2>View My Engagements</h2>
            <?php
            include 'database/db_connection.php';

            // Assuming user_id is stored in session after login
            if (!isset($_SESSION["user_id"])) {
              echo "<p>Please log in to view your engagements.</p>";
            } else {
              $user_id = $_SESSION["user_id"];

              $sql = "SELECT e.engagement_id, e.engagement_name, e.start_date, e.end_date, c.client_name
                FROM engagements e
                JOIN clients c ON e.client_id = c.client_id
                JOIN user_engagements ue ON e.engagement_id = ue.engagement_id
                WHERE ue.user_id = ?";

              if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                  echo "<table class='table table-bordered'>";
                  echo "<thead><tr><th>Engagement ID</th><th>Engagement Name</th><th>Client</th><th>Start Date</th><th>End Date</th><th>Action</th></tr></thead>";
                  echo "<tbody>";
                  while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["engagement_id"] . "</td>";
                    echo "<td>" . $row["engagement_name"] . "</td>";
                    echo "<td>" . $row["client_name"] . "</td>";
                    echo "<td>" . $row["start_date"] . "</td>";
                    echo "<td>" . $row["end_date"] . "</td>";
                    echo "<td><a href='engagement_details.php?engagement_id=" . $row["engagement_id"] . "' class='btn btn-info btn-sm'>View Details</a></td>";
                    echo "</tr>";
                  }
                  echo "</tbody>";
                  echo "</table>";
                } else {
                  echo "<p>No engagements assigned to you.</p>";
                }
                $stmt->close();
              } else {
                echo "Error preparing statement: " . $conn->error;
              }
            }
            $conn->close();
            ?>
          </div>
        </div>
      </div>

      <?php include('component/footer.php'); ?>
    </div>
  </div>
  <?php include('component/script.php'); ?>
</body>

</html>