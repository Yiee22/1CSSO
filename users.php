<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "csso";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// ✅ Add status column dynamically if it doesn't exist
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS status TINYINT(1) DEFAULT 0");

// ===================== DELETE FUNCTION =====================
if (isset($_GET['delete_id'])) {
    $delete_id = $conn->real_escape_string($_GET['delete_id']);
    $conn->query("DELETE FROM users WHERE user_id = '$delete_id'");

    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        window.onload = function() {
            Swal.fire({
                title: 'Deleted!',
                text: 'User record deleted successfully!',
                icon: 'success',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'OK',
                position: 'center'
            }).then(() => {
                window.location = 'users.php';
            });
        };
    </script>";
    exit;
}

// ===================== STATUS TOGGLE FUNCTION =====================
if (isset($_GET['toggle_id'])) {
    $toggle_id = $conn->real_escape_string($_GET['toggle_id']);

    // ✅ Get user info before toggle
    $getUser = $conn->query("SELECT usertype, status FROM users WHERE user_id = '$toggle_id'");
    if ($getUser->num_rows > 0) {
        $user = $getUser->fetch_assoc();
        $usertype = strtolower($user['usertype']);
        $status = $user['status'];

        // Governor is always ON (cannot toggle)
        if ($usertype == 'governor') {
            // Force governor always ON
            $conn->query("UPDATE users SET status = 1 WHERE user_id = '$toggle_id'");
        } else {
            // Vice Governor and others can be toggled
            // Toggle ON/OFF
            $conn->query("UPDATE users SET status = IF(status = 1, 0, 1) WHERE user_id = '$toggle_id'");
        }
    }

    header("Location: users.php");
    exit;
}

// ✅ Force Governor always ON every load
$conn->query("UPDATE users SET status = 1 WHERE LOWER(usertype) = 'governor'");

// ===================== FETCH DATA =====================
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT * FROM users WHERE 
        first_name LIKE '%$search%' 
        OR last_name LIKE '%$search%' 
        OR username LIKE '%$search%' 
        ORDER BY user_id DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Users List | CSSO</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body {
  background-color: #f9fafb;
  font-family: 'Poppins', sans-serif;
  color: #333;
  padding: 30px;
}
.container {
  background: #fff;
  padding: 30px;
  border-radius: 14px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.08);
  max-width: 1200px;
  margin: auto;
}
.header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  margin-bottom: 25px;
}
.header-bar h2 {
  color: #2563eb;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
}
.header-bar h2 i {
  background: #2563eb;
  color: #fff;
  padding: 8px;
  border-radius: 8px;
  font-size: 18px;
}
.search-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}
.search-bar input {
  border-radius: 6px;
  border: 1px solid #ccc;
  padding: 8px 10px;
}
.search-btn {
  background: #2563eb;
  color: #fff;
  border: none;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
}
.clear-btn {
  background: #6b7280;
  color: #fff;
  border: none;
  padding: 8px 12px;
  border-radius: 6px;
  text-decoration: none;
}
.add-btn {
  background: #16a34a;
  color: #fff;
  border: none;
  padding: 10px 18px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 15px;
  transition: 0.3s;
}
.add-btn:hover {
  background: #15803d;
}
.table th {
  background: #2563eb;
  color: #fff;
  text-align: center;
  vertical-align: middle;
}
.table td {
  text-align: center;
  vertical-align: middle;
}
.toggle-btn {
  border: none;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  color: #fff;
  font-weight: 600;
}
.toggle-on { background: #16a34a; }
.toggle-off { background: #dc2626; }
.action-btn {
  border: none;
  padding: 6px 10px;
  border-radius: 6px;
  cursor: pointer;
  color: #fff;
  margin: 2px;
}
.view-btn { background: #2563eb; }
.view-btn:hover { background: #1d4ed8; }
.delete-btn { background: #ef4444; }
.delete-btn:hover { background: #dc2626; }
</style>
</head>
<body>

<div class="container">
  <div class="header-bar">
    <h2><i class="fa-solid fa-user-gear"></i> Users List</h2>
    <button class="add-btn" onclick="window.location.href='user_add.php'">
      <i class="fa-solid fa-plus"></i> Add User
    </button>
  </div>

  <form method="GET" class="search-bar">
    <input type="text" name="search" placeholder="Search user..." value="<?= htmlspecialchars($search) ?>">
    <button class="search-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
    <a href="users.php" class="clear-btn"><i class="fa-solid fa-rotate"></i> Clear</a>
  </form>

  <table class="table table-bordered table-hover">
    <thead>
      <tr>
        <th>#</th>
        <th>FIRST NAME</th>
        <th>LAST NAME</th>
        <th>USERNAME</th>
        <th>USER TYPE</th>
        <th>DATE IN</th>
        <th>TIME IN</th>
        <th>STATUS</th>
        <th>ACTION</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $usertype = strtolower($row['usertype']);
          $status = isset($row['status']) ? (int)$row['status'] : 0;

          // Governor always ON and cannot be toggled
          if ($usertype == 'governor') {
            $status = 1;
            $status_label = "ON";
            $status_class = "toggle-on";
            $toggle_button = "<button class='toggle-btn {$status_class}' disabled>{$status_label}</button>";
          } else {
            $status_label = $status ? "ON" : "OFF";
            $status_class = $status ? "toggle-on" : "toggle-off";
            $toggle_button = "<button class='toggle-btn {$status_class}' 
                                onclick=\"window.location.href='users.php?toggle_id={$row['user_id']}'\">
                                {$status_label}
                              </button>";
          }

          echo "<tr>
                  <td>{$row['user_id']}</td>
                  <td>{$row['first_name']}</td>
                  <td>{$row['last_name']}</td>
                  <td>{$row['username']}</td>
                  <td>{$row['usertype']}</td>
                  <td>{$row['date_in']}</td>
                  <td>{$row['time_in']}</td>
                  <td>{$toggle_button}</td>
                  <td>
                    <button class='action-btn view-btn' onclick='viewUser(" . json_encode($row) . ")'>
                      <i class=\"fa-solid fa-eye\"></i>
                    </button>
                    <button class='action-btn delete-btn' onclick=\"confirmDelete('{$row['user_id']}')\">
                      <i class='fa-solid fa-trash'></i>
                    </button>
                  </td>
                </tr>";
        }
      } else {
        echo "<tr><td colspan='9'>No users found.</td></tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">User Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalBody"></div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmDelete(userId) {
  Swal.fire({
      title: 'Are you sure?',
      text: "This user will be permanently deleted.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel',
      reverseButtons: true,
      position: 'center'
  }).then((result) => {
      if (result.isConfirmed) {
          window.location.href = 'users.php?delete_id=' + userId;
      }
  });
}

function viewUser(user) {
  let html = `
    <p><strong>First Name:</strong> ${user.first_name}</p>
    <p><strong>Last Name:</strong> ${user.last_name}</p>
    <p><strong>Username:</strong> ${user.username}</p>
    <p><strong>User Type:</strong> ${user.usertype}</p>
    <p><strong>Date In:</strong> ${user.date_in}</p>
    <p><strong>Time In:</strong> ${user.time_in}</p>
  `;
  document.getElementById('modalBody').innerHTML = html;
  new bootstrap.Modal(document.getElementById('viewModal')).show();
}
</script>

</body>
</html>


