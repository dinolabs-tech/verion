<?php
session_start();
require_once 'database/db_connection.php';

// Only Admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Superuser') {
  header("Location: login.php");
  exit();
}

$success_message = '';
$error_message = '';

// Handle Add/Edit Client
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $client_name = $_POST['client_name'] ?? '';
    $contact_person = $_POST['contact_person'] ?? '';
    $contact_email = $_POST['contact_email'] ?? '';
    $contact_phone = $_POST['contact_phone'] ?? '';
    $address = $_POST['address'] ?? '';

    if ($action === 'add') {
      $stmt = $conn->prepare("INSERT INTO clients (client_name, contact_person, contact_email, contact_phone, address) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("sssss", $client_name, $contact_person, $contact_email, $contact_phone, $address);
      if ($stmt->execute()) {
        $success_message = "Client added successfully!";
      } else {
        $error_message = "Error adding client: " . $conn->error;
      }
      $stmt->close();
    } elseif ($action === 'edit') {
      $client_id = $_POST['client_id'] ?? 0;
      $stmt = $conn->prepare("UPDATE clients SET client_name = ?, contact_person = ?, contact_email = ?, contact_phone = ?, address = ? WHERE client_id = ?");
      $stmt->bind_param("sssssi", $client_name, $contact_person, $contact_email, $contact_phone, $address, $client_id);
      if ($stmt->execute()) {
        $success_message = "Client updated successfully!";
      } else {
        $error_message = "Error updating client: " . $conn->error;
      }
      $stmt->close();
    }
  }
}

// Handle Delete Client
if (isset($_GET['delete_id'])) {
  $client_id = $_GET['delete_id'];
  $stmt = $conn->prepare("DELETE FROM clients WHERE client_id = ?");
  $stmt->bind_param("i", $client_id);
  if ($stmt->execute()) {
    $success_message = "Client deleted successfully!";
  } else {
    $error_message = "Error deleting client: " . $conn->error;
  }
  $stmt->close();
  header("Location: manage_clients.php?message=" . urlencode($success_message) . "&error=" . urlencode($error_message));
  exit();
}

// Fetch all clients
$clients = [];
$result = $conn->query("SELECT * FROM clients ORDER BY client_name");
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $clients[] = $row;
  }
}

$conn->close();
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

          <div class="row">
            <div class="col-12">
              <h1 class="mb-4">Manage Clients</h1>
              <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-success" role="alert">
                  <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
              <?php endif; ?>
              <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger" role="alert">
                  <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
              <?php endif; ?>
              <?php if ($success_message): ?>
                <div class="alert alert-success" role="alert">
                  <?php echo $success_message; ?>
                </div>
              <?php endif; ?>
              <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                  <?php echo $error_message; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Add New Client Form -->
          <div class="card mb-4">
            <div class="card-header">
              <h4>Add New Client</h4>
            </div>
            <div class="card-body">
              <form action="manage_clients.php" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="row">
                  <div class="mb-3 col-md-6">
                    <input type="text" class="form-control" id="client_name" name="client_name" placeholder="Client Name">
                  </div>
                  <div class="mb-3 col-md-6">
                    <input type="text" class="form-control" id="contact_person" name="contact_person" placeholder="Contact Person">
                  </div>
                  <div class="mb-3 col-md-6">
                    <input type="email" class="form-control" id="contact_email" name="contact_email" placeholder="Contact Email">
                  </div>
                  <div class="mb-3 col-md-6">
                    <input type="text" class="form-control" id="contact_phone" name="contact_phone" placeholder="Contact Phone">
                  </div>
                  <div class="mb-3 col-md-6">
                    <textarea class="form-control" id="address" placeholder="Address" name="address" rows="3"></textarea>
                  </div>
                  <div class="col-md-6">
                    <button type="submit" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i></button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <!-- List of Clients -->
          <div class="card">
            <div class="card-header">
              <h4>Existing Clients</h4>
            </div>
            <div class="card-body">
              <?php if (empty($clients)): ?>
                <p>No clients found. Add a new client above.</p>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-striped table-hover" id="basic-datatables" >
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Client Name</th>
                        <th>Contact Person</th>
                        <th>Contact Email</th>
                        <th>Contact Phone</th>
                        <th>Address</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($clients as $client): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($client['client_id']); ?></td>
                          <td><?php echo htmlspecialchars($client['client_name']); ?></td>
                          <td><?php echo htmlspecialchars($client['contact_person']); ?></td>
                          <td><?php echo htmlspecialchars($client['contact_email']); ?></td>
                          <td><?php echo htmlspecialchars($client['contact_phone']); ?></td>
                          <td><?php echo htmlspecialchars($client['address']); ?></td>
                          <td class="d-flex">
                            
                                <button type="button" class=" text-white btn-primary btn-icon btn-round mb-1 me-1 ps-1" data-bs-toggle="modal" data-bs-target="#editClientModal"
                                  data-id="<?php echo $client['client_id']; ?>"
                                  data-name="<?php echo htmlspecialchars($client['client_name']); ?>"
                                  data-person="<?php echo htmlspecialchars($client['contact_person']); ?>"
                                  data-email="<?php echo htmlspecialchars($client['contact_email']); ?>"
                                  data-phone="<?php echo htmlspecialchars($client['contact_phone']); ?>"
                                  data-address="<?php echo htmlspecialchars($client['address']); ?>">
                                  <i class="fas fa-edit"></i>
                                </button>
                              
                                <a href="manage_clients.php?delete_id=<?php echo $client['client_id']; ?>" class=" text-white btn-danger btn-icon btn-round  " onclick="return confirm('Are you sure you want to delete this client?');"><i class="fa fa-trash"></i> </a>
                              
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <!-- Edit Client Modal -->
      <div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="editClientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editClientModalLabel">Edit Client</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="manage_clients.php" method="POST">
              <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="client_id" id="edit_client_id">
                <div class="mb-3">
                  <input type="text" class="form-control" placeholder="Client Name" id="edit_client_name" name="client_name" required>
                </div>
                <div class="mb-3">
                  <input type="text" class="form-control" placeholder="Contact Person" id="edit_contact_person" name="contact_person">
                </div>
                <div class="mb-3">
                  <input type="email" class="form-control" placeholder="Contact Email" id="edit_contact_email" name="contact_email">
                </div>
                <div class="mb-3">
                  <input type="text" class="form-control" placeholder="Contact Phone" id="edit_contact_phone" name="contact_phone">
                </div>
                <div class="mb-3">
                  <textarea class="form-control" placeholder="Address" id="edit_address" name="address" rows="3"></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i></button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <?php include('component/footer.php'); ?>
    </div>
  </div>
  <?php include('component/script.php'); ?>
  <script>
    var editClientModal = document.getElementById('editClientModal');
    editClientModal.addEventListener('show.bs.modal', function(event) {
      var button = event.relatedTarget;
      var id = button.getAttribute('data-id');
      var name = button.getAttribute('data-name');
      var person = button.getAttribute('data-person');
      var email = button.getAttribute('data-email');
      var phone = button.getAttribute('data-phone');
      var address = button.getAttribute('data-address');

      var modalTitle = editClientModal.querySelector('.modal-title');
      var clientIdInput = editClientModal.querySelector('#edit_client_id');
      var clientNameInput = editClientModal.querySelector('#edit_client_name');
      var contactPersonInput = editClientModal.querySelector('#edit_contact_person');
      var contactEmailInput = editClientModal.querySelector('#edit_contact_email');
      var contactPhoneInput = editClientModal.querySelector('#edit_contact_phone');
      var addressInput = editClientModal.querySelector('#edit_address');

      modalTitle.textContent = 'Edit Client: ' + name;
      clientIdInput.value = id;
      clientNameInput.value = name;
      contactPersonInput.value = person;
      contactEmailInput.value = email;
      contactPhoneInput.value = phone;
      addressInput.value = address;
    });
  </script>
</body>

</html>