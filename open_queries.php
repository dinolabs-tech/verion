<?php
session_start();
include('component/head.php');
include 'database/db_connection.php';

// Update status of queries from 'sent' to 'opened' when the receiver opens them
if (isset($_SESSION['user_id'])) {
  $current_user_id = $_SESSION['user_id'];
  $update_stmt = $conn->prepare("UPDATE queries SET status = 'opened' WHERE raised_to_user_id = ? AND status = 'sent'");
  $update_stmt->bind_param("i", $current_user_id);
  $update_stmt->execute();
  $update_stmt->close();
}
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
          <h2>Open Queries</h2>
          <?php
          include 'database/db_connection.php';

          $user_role = $_SESSION['role'];
          $user_id = $_SESSION['user_id'];

          // Handle new query submission
          if (isset($_POST['raise_query'])) {
            $engagement_id = $_POST['engagement_id'];
            $raised_to_user_id = $_POST['raised_to_user_id'];
            $query_text = $_POST['query_text'];
            $raised_by_user_id = $_SESSION['user_id']; // The current user is raising the query

            if (!empty($engagement_id) && !empty($raised_to_user_id) && !empty($query_text)) {
              $stmt = $conn->prepare("INSERT INTO queries (engagement_id, raised_by_user_id, raised_to_user_id, query_text, status, raised_at) VALUES (?, ?, ?, ?, 'Open', NOW())");
              $stmt->bind_param("iiis", $engagement_id, $raised_by_user_id, $raised_to_user_id, $query_text);

              if ($stmt->execute()) {
                $_SESSION['message'] = "Query raised successfully!";
                $_SESSION['message_type'] = "success";
              } else {
                $_SESSION['message'] = "Error raising query: " . $conn->error;
                $_SESSION['message_type'] = "danger";
              }
              $stmt->close();
              header("Location:open_queries.php");
              exit();
            } else {
              $_SESSION['message'] = "Please fill in all fields to raise a query.";
              $_SESSION['message_type'] = "warning";
              header("Location:open_queries.php");
              exit();
            }
          }

          // Display messages if any
          if (isset($_SESSION['message'])) {
            echo '<div class="alert alert-' . $_SESSION['message_type'] . ' alert-dismissible fade show" role="alert">';
            echo $_SESSION['message'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
          }

          // Fetch engagements for the current user to populate the "Raise New Query" form
          $engagements = [];
          if ($user_role === 'Client') {
            // First, get the client_id from the users table
            $stmt_client = $conn->prepare("SELECT client_id FROM users WHERE user_id = ?");
            $stmt_client->bind_param("i", $user_id);
            $stmt_client->execute();
            $result_client = $stmt_client->get_result();
            if ($client_row = $result_client->fetch_assoc()) {
              $client_id = $client_row['client_id'];
              // Now fetch engagements for that client_id
              $stmt_engagements = $conn->prepare("SELECT engagement_id, engagement_name FROM engagements WHERE client_id = ?");
              $stmt_engagements->bind_param("i", $client_id);
              $stmt_engagements->execute();
              $result_engagements = $stmt_engagements->get_result();
              while ($row = $result_engagements->fetch_assoc()) {
                $engagements[] = $row;
              }
              $stmt_engagements->close();
            }
            $stmt_client->close();
          } elseif ($user_role === 'Reviewer') {
            // Reviewers can see all engagements they are assigned to
            $stmt_engagements = $conn->prepare("SELECT engagement_id, engagement_name FROM engagements  WHERE assigned_reviewer_id = ?");
            $stmt_engagements->bind_param("i", $user_id);
            $stmt_engagements->execute();
            $result_engagements = $stmt_engagements->get_result();
            while ($row = $result_engagements->fetch_assoc()) {
              $engagements[] = $row;
            }
            $stmt_engagements->close();
          } elseif ($user_role === 'Auditor' || $user_role === 'Admin') {
            // Auditors and Admins can see all engagements
            $result_engagements = $conn->query("SELECT engagement_id, engagement_name FROM engagements");
            while ($row = $result_engagements->fetch_assoc()) {
              $engagements[] = $row;
            }
          }

          // Fetch users to whom queries can be raised (e.g., Auditors, Reviewers)
          $users_to_raise_to = [];
          $stmt_users = $conn->prepare("SELECT user_id, username, role FROM users WHERE role IN ('Auditor', 'Reviewer')");
          $stmt_users->execute();
          $result_users = $stmt_users->get_result();
          while ($row = $result_users->fetch_assoc()) {
            $users_to_raise_to[] = $row;
          }
          $stmt_users->close();


          $sql = "SELECT q.query_id, q.query_text, q.status, q.raised_at, e.engagement_name, q.engagement_id
            FROM queries q
            JOIN engagements e ON q.engagement_id = e.engagement_id
            WHERE q.status = 'Open'";
          if ($user_role === 'Client' || $user_role === 'Reviewer') {
            $sql .= " AND q.raised_to_user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
          } else {
            // Auditor or Admin sees all open queries
            $result = $conn->query($sql);
          }

          $conn->close();
          ?>

          <?php if ($user_role === 'Client' || $user_role === 'Reviewer'): ?>
            <div class="card mt-4">
              <div class="card-header">
                <h3>Raise New Query</h3>
              </div>
              <div class="card-body">
                <form action="" method="POST">

                  <div class="row">
                    <div class="mb-3 col-md-6">
                      <label for="engagement_id" class="form-label">Select Engagement:</label>
                      <select class="form-control form-select" id="engagement_id" name="engagement_id" required>
                        <option value="" selected disabled>Select Engagement</option>
                        <?php foreach ($engagements as $engagement): ?>
                          <option value="<?php echo $engagement['engagement_id']; ?>"><?php echo htmlspecialchars($engagement['engagement_name']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="mb-3 col-md-6">
                      <label for="raised_to_user_id" class="form-label">Raise Query To:</label>
                      <select class="form-control form-select" id="raised_to_user_id" name="raised_to_user_id" required>
                        <option value="" selected disabled>Select User</option>
                        <?php foreach ($users_to_raise_to as $user): ?>
                          <option value="<?php echo $user['user_id']; ?>"><?php echo htmlspecialchars($user['username']) . " (" . htmlspecialchars($user['role']) . ")"; ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="query_text" class="form-label">Query Text:</label>
                    <textarea class="form-control" id="query_text" name="query_text" rows="5" required></textarea>
                  </div>
                  <button type="submit" name="raise_query" class="btn btn-success btn-icon btn-round"><i class="fas fa-plus"></i></button>
                </form>
              </div>
            </div>
          <?php endif; ?>

          <?php
          // Re-include db_connection and re-fetch data for the table after the form
          include 'database/db_connection.php';

          $sql = "SELECT q.*, e.engagement_name, q.engagement_id
            FROM queries q
            JOIN engagements e ON q.engagement_id = e.engagement_id
            WHERE q.status = 'Sent' OR q.status = 'Open'";
          if ($user_role === 'Client' || $user_role === 'Reviewer') {
            $sql .= " AND (q.raised_to_user_id = ? OR q.raised_by_user_id = ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $user_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
          } else {
            // Auditor or Admin sees all open queries
            $result = $conn->query($sql);
          }

          if ($result->num_rows > 0) { ?>
            <h3 class='mt-4'>Existing Opened Queries</h3>
            <div class='table-responsive'>
              <table class='table table-hover table-striped table-bordered align-middle'>
                <thead class='table-dark'>
                  <tr>
                    <th scope='col'>Query ID</th>
                    <th scope='col'>Query Text</th>
                    <th scope='col'>Status</th>
                    <th scope='col'>Raised At</th>
                    <th scope='col'>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row["query_id"]); ?></td>
                      <td><?php echo htmlspecialchars($row["query_text"]); ?></td>
                      <td><span class='badge bg-info'><?php echo htmlspecialchars($row["status"]); ?></span></td>
                      <td><?php echo htmlspecialchars($row["raised_at"]); ?></td>
                      <td><a href='queries.php?engagement_id=<?php echo htmlspecialchars($row["engagement_id"]); ?>' class='btn btn-primary btn-sm'>Respond</a></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          <?php  } else { ?>
            <p class='mt-4'>No open queries found.</p>
          <?php }
          $conn->close();
          ?>

          <!-- List of Queries -->
          <!-- <div class="card">
            <div class="card-header">
                <h4>Existing Queries</h4>
            </div>
            <div class="card-body">
                <?php if (empty($queries)): ?>
                    <p>No queries found for this engagement.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Query</th>
                                    <th>Raised By</th>
                                    <th>Raised To</th>
                                    <th>Status</th>
                                    <th>Raised At</th>
                                    <th>Response</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($queries as $query): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($query['query_id']); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars($query['query_text'])); ?></td>
                                        <td><?php echo htmlspecialchars($query['raised_by_username']); ?></td>
                                        <td><?php echo htmlspecialchars($query['raised_to_username'] ?? 'N/A'); ?></td>
                                        <td><span class="badge bg-<?php echo ($query['status'] === 'Open' ? 'warning' : ($query['status'] === 'Responded' ? 'info' : 'success')); ?>"><?php echo htmlspecialchars($query['status']); ?></span></td>
                                        <td><?php echo htmlspecialchars($query['raised_at']); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars($query['response_text'] ?? 'N/A')); ?></td>
                                        <td>
                                            <?php
                                            $can_edit_respond = false;
                                            if (in_array($_SESSION['role'], ['Auditor', 'Admin'])) {
                                              $can_edit_respond = true;
                                            } elseif (in_array($_SESSION['role'], ['Client', 'Reviewer']) && $_SESSION['user_id'] == $query['raised_to_user_id']) {
                                              $can_edit_respond = true;
                                            }
                                            ?>
                                            <?php if ($can_edit_respond): ?>
                                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editQueryModal"
                                                    data-id="<?php echo $query['query_id']; ?>"
                                                    data-query-text="<?php echo htmlspecialchars($query['query_text']); ?>"
                                                    data-raised-to-id="<?php echo htmlspecialchars($query['raised_to_user_id']); ?>"
                                                    data-response-text="<?php echo htmlspecialchars($query['response_text']); ?>"
                                                    data-status="<?php echo htmlspecialchars($query['status']); ?>">
                                                    Edit/Respond
                                                </button>
                                            <?php endif; ?>
                                            <?php if (in_array($_SESSION['role'], ['Auditor', 'Admin'])): ?>
                                                <a href="queries.php?engagement_id=<?php echo $engagement_id; ?>&delete_id=<?php echo $query['query_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this query?');">Delete</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div> -->

        </div>
      </div>

      <?php include('component/footer.php'); ?>
    </div>
  </div>
  <?php include('component/script.php'); ?>
</body>

</html>