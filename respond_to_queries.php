<?php
session_start();
require_once 'database/db_connection.php';

// Only Auditor, Admin, or Client can access this page
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Auditor', 'Admin', 'Client', 'Reviewer', 'Superuser'])) {
  header("Location: login.php");
  exit();
}

include 'component/head.php';
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
            <h2>Respond to Queries</h2>
            <?php
            // db_connection.php is already included at the top

            $query_id = $query_text = $engagement_name = $status = "";
            $response_text = "";
            $response_text_err = "";
            $success_message = "";

            if (isset($_GET["query_id"]) && !empty(trim($_GET["query_id"]))) {
              $query_id = trim($_GET["query_id"]);

              $sql = "SELECT q.query_id, q.query_text, q.status, e.engagement_name
                FROM queries q
                JOIN engagements e ON q.engagement_id = e.engagement_id
                WHERE q.query_id = ?";

              if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $param_query_id);
                $param_query_id = $query_id;

                if ($stmt->execute()) {
                  $result = $stmt->get_result();
                  if ($result->num_rows == 1) {
                    $row = $result->fetch_assoc();
                    $query_text = $row["query_text"];
                    $engagement_name = $row["engagement_name"];
                    $status = $row["status"];
                  } else {
                    echo "<p>Query not found.</p>";
                    exit();
                  }
                } else {
                  echo "Something went wrong. Please try again later.";
                  exit();
                }
                $stmt->close();
              }
            } else {
              echo "<p>Invalid query ID.</p>";
              exit();
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
              if (empty(trim($_POST["response_text"]))) {
                $response_text_err = "Please enter your response.";
              } else {
                $response_text = trim($_POST["response_text"]);
              }

              if (empty($response_text_err)) {
                $update_sql = "UPDATE queries SET response_text = ?, status = 'Responded', responded_at = NOW() WHERE query_id = ?";
                if ($update_stmt = $conn->prepare($update_sql)) {
                  $update_stmt->bind_param("si", $param_response_text, $param_query_id);
                  $param_response_text = $response_text;
                  $param_query_id = $query_id;

                  if ($update_stmt->execute()) {
                    $success_message = "Response submitted successfully. Query status updated to 'Responded'.";
                    $status = "Responded"; // Update status displayed on page
                  } else {
                    echo "Error updating record: " . $conn->error;
                  }
                  $update_stmt->close();
                }
              }
            }
            ?>

            <?php if (!empty($success_message)): ?>
              <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <div class="card mb-3">
              <div class="card-header">
                Query ID: <?php echo $query_id; ?>
              </div>
              <div class="card-body">
                <h5 class="card-title">Engagement: <?php echo $engagement_name; ?></h5>
                <p class="card-text"><strong>Query:</strong> <?php echo $query_text; ?></p>
                <p class="card-text"><strong>Status:</strong> <?php echo $status; ?></p>
              </div>
            </div>

            <?php if ($status == 'Open' || $status == 'opened'): ?>
              <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#respondQueryModal"
                data-query-id="<?php echo $query_id; ?>"
                data-response-text="<?php echo htmlspecialchars($response_text); ?>">
                Respond to Query
              </button>
            <?php elseif ($status == 'Responded'): ?>
              <div class="alert alert-info">This query has already been responded to.</div>
            <?php endif; ?>

            <?php $conn->close(); ?>
          </div>
        </div>
      </div>

      <?php include('component/footer.php'); ?>
    </div>
  </div>
  <!-- Respond Query Modal -->
  <div class="modal fade" id="respondQueryModal" tabindex="-1" aria-labelledby="respondQueryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="respondQueryModalLabel">Respond to Query</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?query_id=<?php echo $query_id; ?>" method="POST">
          <div class="modal-body">
            <input type="hidden" name="action" value="respond_query">
            <input type="hidden" name="query_id" id="modal_query_id">
            <div class="mb-3">
              <label for="modal_response_text" class="form-label">Your Response</label>
              <textarea class="form-control" id="modal_response_text" name="response_text" rows="5" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i></button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php include('component/script.php'); ?>
  <script>
    $('#respondQueryModal').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget);
      var queryId = button.data('query-id');
      var responseText = button.data('response-text');

      var modal = $(this);
      modal.find('#modal_query_id').val(queryId);
      modal.find('#modal_response_text').val(responseText);
    });
  </script>
</body>

</html>