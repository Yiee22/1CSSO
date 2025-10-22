<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "csso";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$popup = ""; // holder for popup message

// ===== CREATE NEW EVENT =====
if (isset($_POST['create_event'])) {
  $event_Name = $_POST['event_Name'];
  $event_Date = $_POST['event_Date'];
  $location   = $_POST['location'];

  $stmt = $conn->prepare("INSERT INTO event (event_Name, event_Date, location) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $event_Name, $event_Date, $location);
  $stmt->execute();
  $stmt->close();

  $popup = "created";
}

// ===== UPDATE EVENT =====
if (isset($_POST['update_event'])) {
  $oldName    = $_POST['old_event_Name'];
  $event_Name = $_POST['event_Name'];
  $event_Date = $_POST['event_Date'];
  $location   = $_POST['location'];

  $stmt = $conn->prepare("UPDATE event SET event_Name=?, event_Date=?, location=? WHERE event_Name=?");
  $stmt->bind_param("ssss", $event_Name, $event_Date, $location, $oldName);
  $stmt->execute();
  $stmt->close();

  $popup = "updated";
}

// ===== DELETE EVENT =====
if (isset($_GET['delete'])) {
  $delete = $_GET['delete'];
  $stmt = $conn->prepare("DELETE FROM event WHERE event_Name=?");
  $stmt->bind_param("s", $delete);
  $stmt->execute();
  $stmt->close();

  $popup = "deleted";
}

// ===== SEARCH FEATURE =====
$search = "";
if (isset($_GET['search'])) {
  $search = trim($_GET['search']);
  $query = $conn->prepare("SELECT * FROM event WHERE event_Name LIKE ? OR location LIKE ?");
  $like = "%$search%";
  $query->bind_param("ss", $like, $like);
  $query->execute();
  $events = $query->get_result();
} else {
  $events = $conn->query("SELECT * FROM event ORDER BY event_Date DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Events Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body {
  background-color: #f5f7fa;
  font-family: 'Poppins', sans-serif;
}
.container {
  margin-top: 40px;
}
h4 {
  color: #2563eb;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 8px;
}
.search-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
}
.search-bar input {
  width: 300px;
  padding-left: 35px;
  border-radius: 8px;
  border: 1px solid #ccc;
}
.search-wrapper {
  position: relative;
}
.search-wrapper i {
  position: absolute;
  top: 10px;
  left: 10px;
  color: #2563eb;
}
.btn-success { background-color: #22c55e; border: none; }
.btn-success:hover { background-color: #16a34a; }
.table {
  background-color: white;
  border-radius: 8px;
  overflow: hidden;
}
.table thead {
  background-color: #2563eb;
  color: white;
  font-weight: 600;
  text-align: center;
}
.table tbody td {
  text-align: center;
  vertical-align: middle;
  font-size: 15px;
}
.action-buttons {
  display: flex;
  justify-content: center;
  gap: 8px;
}
.action-btn {
  border: none;
  padding: 8px;
  border-radius: 6px;
  cursor: pointer;
  transition: 0.2s;
}
.action-btn.edit { background-color: #facc15; color: white; }
.action-btn.delete { background-color: #ef4444; color: white; }
.action-btn:hover { opacity: 0.8; }
.modal-header {
  background-color: #2563eb;
  color: white;
}
</style>
</head>
<body>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-calendar-alt"></i> Events List</h4>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addEventModal">+ Add New Event</button>
  </div>

  <!-- Search Bar -->
  <form method="GET" class="search-bar">
    <div class="search-wrapper">
      <i class="fas fa-search"></i>
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search event..." class="form-control">
    </div>
    <a href="events.php" class="btn btn-secondary"><i class="fas fa-sync-alt"></i></a>
  </form>

  <!-- Events Table -->
  <div class="card shadow-sm">
    <table class="table table-bordered mb-0">
      <thead>
        <tr>
          <th>Event Name</th>
          <th>Event Date</th>
          <th>Location</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($events->num_rows > 0): ?>
          <?php while ($row = $events->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['event_Name']) ?></td>
              <td><?= htmlspecialchars($row['event_Date']) ?></td>
              <td><?= htmlspecialchars($row['location']) ?></td>
              <td>
                <div class="action-buttons">
                  <button class="action-btn edit" data-bs-toggle="modal" data-bs-target="#editModal"
                    data-name="<?= htmlspecialchars($row['event_Name']) ?>"
                    data-date="<?= htmlspecialchars($row['event_Date']) ?>"
                    data-location="<?= htmlspecialchars($row['location']) ?>">
                    <i class="fas fa-pen"></i>
                  </button>
                  <button class="action-btn delete" onclick="confirmDelete('<?= $row['event_Name'] ?>')">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="4">No events found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Add New Event</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Event Name</label>
            <input type="text" name="event_Name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Event Date</label>
            <input type="date" name="event_Date" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Location</label>
            <input type="text" name="location" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="create_event" class="btn btn-primary">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Edit Event</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="old_event_Name" id="editOldName">
          <div class="mb-3">
            <label>Event Name</label>
            <input type="text" name="event_Name" id="editName" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Event Date</label>
            <input type="date" name="event_Date" id="editDate" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Location</label>
            <input type="text" name="location" id="editLocation" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="update_event" class="btn btn-primary">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmDelete(name) {
  Swal.fire({
    title: 'Delete Event?',
    text: "This action cannot be undone!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#e11d48',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location = 'events.php?delete=' + encodeURIComponent(name);
    }
  });
}

const editModal = document.getElementById('editModal');
editModal.addEventListener('show.bs.modal', event => {
  const button = event.relatedTarget;
  document.getElementById('editOldName').value = button.getAttribute('data-name');
  document.getElementById('editName').value = button.getAttribute('data-name');
  document.getElementById('editDate').value = button.getAttribute('data-date');
  document.getElementById('editLocation').value = button.getAttribute('data-location');
});

// ==== SweetAlert popup (centered success) ====
<?php if ($popup == "created"): ?>
Swal.fire({
  icon: 'success',
  title: 'Created!',
  text: 'Event record added successfully!',
  confirmButtonText: 'OK',
  confirmButtonColor: '#2563eb'
}).then(() => {
  window.location = 'events.php';
});
<?php elseif ($popup == "updated"): ?>
Swal.fire({
  icon: 'success',
  title: 'Updated!',
  text: 'Event record updated successfully!',
  confirmButtonText: 'OK',
  confirmButtonColor: '#2563eb'
}).then(() => {
  window.location = 'events.php';
});
<?php elseif ($popup == "deleted"): ?>
Swal.fire({
  icon: 'success',
  title: 'Deleted!',
  text: 'Event record deleted successfully!',
  confirmButtonText: 'OK',
  confirmButtonColor: '#2563eb'
}).then(() => {
  window.location = 'events.php';
});
<?php endif; ?>
</script>

</body>
</html>
