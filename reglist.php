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

// =============== DELETE FUNCTION ===============
// Now delete based on registration_no, not students_id
if (isset($_GET['delete_id'])) {
    $delete_id = $conn->real_escape_string($_GET['delete_id']);
    $conn->query("DELETE FROM registration WHERE registration_no = '$delete_id'");

    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        window.onload = function() {
            Swal.fire({
                title: 'Deleted!',
                text: 'Registration record deleted successfully!',
                icon: 'success',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'OK',
                position: 'center'
            }).then(() => {
                window.location = 'reglist.php';
            });
        };
    </script>";
}

$course_filter = isset($_GET['course']) ? $_GET['course'] : 'BSIT';
$semester_filter = isset($_GET['semester']) ? $_GET['semester'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$courseTitle = ($course_filter === 'BSCS') 
  ? 'Bachelor of Science in Computer Studies' 
  : 'Bachelor of Science in Information Technology';

$sql = "SELECT r.registration_no, r.students_id, r.registration_date, r.semester, 
               r.amount, r.payment_type, r.payment_status,
               s.FirstName, s.LastName, s.MI, s.Suffix, s.Course
        FROM registration r
        LEFT JOIN student_profile s ON r.students_id = s.students_id
        WHERE s.Course LIKE '%$course_filter%'";

if (!empty($semester_filter)) {
  $sql .= " AND r.semester = '$semester_filter'";
}

if (!empty($search)) {
  $sql .= " AND (s.FirstName LIKE '%$search%' 
              OR s.LastName LIKE '%$search%' 
              OR r.students_id LIKE '%$search%' 
              OR r.registration_no LIKE '%$search%')";
}

$sql .= " ORDER BY r.registration_date DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Registration List | CSSO</title>
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
.filter-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}
.filter-bar select, 
.filter-bar input {
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
.course-title {
  text-align: center;
  font-weight: 600;
  color: #111827;
  margin-bottom: 15px;
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
.delete-btn {
  background: #ef4444;
  color: #fff;
  border: none;
  padding: 8px 10px;
  border-radius: 6px;
  cursor: pointer;
}
.delete-btn:hover {
  background: #dc2626;
}
.status-paid { color: green; font-weight: bold; }
.status-unpaid { color: red; font-weight: bold; }
.status-partial { color: orange; font-weight: bold; }
</style>
</head>
<body>

<div class="container">
  <div class="header-bar">
    <h2><i class="fa-solid fa-users"></i> Registration List</h2>
    <button class="add-btn" onclick="window.location.href='registration.php'">
      <i class="fa-solid fa-plus"></i> Add Registration
    </button>
  </div>

  <form method="GET" class="filter-bar" id="filterForm">
    <select name="course" id="course" onchange="filterData()">
      <option value="BSIT" <?= $course_filter === 'BSIT' ? 'selected' : '' ?>>BSIT</option>
      <option value="BSCS" <?= $course_filter === 'BSCS' ? 'selected' : '' ?>>BSCS</option>
    </select>

    <select name="semester" id="semester" onchange="filterData()">
      <option value="">All Semesters</option>
      <option value="First Semester" <?= $semester_filter === 'First Semester' ? 'selected' : '' ?>>First Semester</option>
      <option value="Second Semester" <?= $semester_filter === 'Second Semester' ? 'selected' : '' ?>>Second Semester</option>
    </select>

    <input type="text" name="search" id="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
    <a href="reglist.php" class="clear-btn"><i class="fa-solid fa-rotate"></i> Clear</a>
  </form>

  <h5 class="course-title"><?= $courseTitle ?></h5>

  <div id="tableData">
    <table class="table table-bordered table-hover">
      <thead>
        <tr>
          <th>REG. NO</th>
          <th>STUDENT NAME</th>
          <th>REGISTRATION DATE</th>
          <th>SEMESTER</th>
          <th>AMOUNT</th>
          <th>PAYMENT TYPE</th>
          <th>STATUS</th>
          <th>ACTION</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $fullname = trim($row['LastName'] . ', ' . $row['FirstName'] . ' ' . ($row['MI'] ? $row['MI'] . '.' : '') . ' ' . $row['Suffix']);
            $statusClass = strtolower(str_replace(' ', '', $row['payment_status']));
            echo "<tr>
                    <td>{$row['registration_no']}</td>
                    <td>{$fullname}</td>
                    <td>{$row['registration_date']}</td>
                    <td>{$row['semester']}</td>
                    <td>₱" . number_format($row['amount'], 2) . "</td>
                    <td>{$row['payment_type']}</td>
                    <td class='status-{$statusClass}'>{$row['payment_status']}</td>
                    <td>
                      <button class='delete-btn' onclick=\"confirmDelete('{$row['registration_no']}')\">
                        <i class='fa-solid fa-trash'></i>
                      </button>
                    </td>
                  </tr>";
          }
        } else {
          echo "<tr><td colspan='8'>No registration records found.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function confirmDelete(regNo) {
  Swal.fire({
      title: 'Are you sure?',
      text: "This registration record will be permanently deleted.",
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
          window.location.href = 'reglist.php?delete_id=' + regNo;
      }
  });
}
</script>

</body>
</html>









