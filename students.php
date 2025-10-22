<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['usertype'], ['Governor', 'Vice Governor'])) {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "csso");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// DELETE FUNCTION
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM student_profile WHERE students_id = $delete_id");

    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
        window.onload = function() {
            Swal.fire({
                title: 'Deleted!',
                text: 'Student record deleted successfully!',
                icon: 'success',
                confirmButtonColor: '#2563eb',
                confirmButtonText: 'OK',
                position: 'center',
                width: '320px',
                padding: '0.6em',
                customClass: {
                    popup: 'swal-small-popup'
                }
            }).then(() => {
                window.location = 'students.php';
            });
        };
    </script>";
}

$course = isset($_GET['course']) ? $_GET['course'] : 'BSIT';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

$sql = "SELECT * FROM student_profile WHERE Course = '$course'";
if (!empty($search)) {
    $sql .= " AND (students_id LIKE '%$search%' 
              OR LastName LIKE '%$search%' 
              OR FirstName LIKE '%$search%' 
              OR Section LIKE '%$search%')";
}
if (!empty($filter)) {
    $sql .= " AND YearLevel = '$filter'";
}
$sql .= " ORDER BY students_id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Students | CSSO</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body { font-family: 'Segoe UI', sans-serif; background: #f7f9fc; margin:0; padding:0;}
.container { padding: 20px; }

h2 { 
    display: flex; 
    align-items: center; 
    gap: 10px; 
    font-size: 24px; 
    color: #3167faff;
    margin-bottom: 15px;
}
h2 i {
    background: #3167faff;
    color: white;
    padding: 10px;
    border-radius: 10px;
    font-size: 20px;
}

.controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 12px;
    gap: 10px;
}
.filters {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
select, input[type="text"] {
    padding: 6px 8px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    font-size: 15px;
    outline: none;
}
button {
    cursor: pointer;
}
.search-btn {
    background:#2563eb;
    color:#fff;
    border:none;
    border-radius:5px;
    padding:6px 10px;
}
.search-btn:hover { background:#1d4ed8; }

.clear-btn {
    background: #64748b;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 6px 12px;
}
.clear-btn:hover {
    background: #475569;
}

.add-btn { 
    background:#16a34a; 
    color:#fff; 
    padding:8px 12px; 
    border:none; 
    border-radius:5px; 
    font-size:15px;
}
.add-btn:hover { background:#15803d; }

.table-container { 
    background:#fff; 
    padding:15px; 
    border-radius:8px; 
    box-shadow:0 2px 8px rgba(0,0,0,0.05);
}
table { width:100%; border-collapse: collapse; }
th, td { padding:12px; text-align:left; border-bottom:1px solid #e2e8f0; }
th { background:#2563eb; color:#fff; text-transform: uppercase; }
tr:hover { background:#f1f5f9; }

.action-btn { border:none; padding:6px 10px; border-radius:5px; cursor:pointer; }
.view-btn { background:#facc15; color:#1f2937; }
.delete-btn { background:#ef4444; color:#fff; }
h3 { text-align:center; color:#1e293b; margin-bottom:15px; font-size:18px; }

/* SweetAlert Compact Design */
.swal-small-popup {
    font-size: 14px !important;
    border-radius: 12px !important;
}
.swal2-title {
    font-size: 18px !important;
}
.swal2-html-container {
    font-size: 14px !important;
}
</style>
</head>
<body>
<div class="container">
    <h2><i class="fa-solid fa-users"></i> Students List</h2>

    <div class="controls">
        <form method="get" class="filters">
            <select name="course" onchange="this.form.submit()">
                <option value="BSIT" <?= $course==='BSIT'?'selected':'' ?>>BSIT</option>
                <option value="BSCS" <?= $course==='BSCS'?'selected':'' ?>>BSCS</option>
            </select>

            <select name="filter" onchange="this.form.submit()">
                <option value="">All Year Levels</option>
                <option value="1stYear" <?= $filter==='1stYear'?'selected':'' ?>>1st Year</option>
                <option value="2ndYear" <?= $filter==='2ndYear'?'selected':'' ?>>2nd Year</option>
                <option value="3rdYear" <?= $filter==='3rdYear'?'selected':'' ?>>3rd Year</option>
                <option value="4thYear" <?= $filter==='4thYear'?'selected':'' ?>>4th Year</option>
            </select>

            <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="search-btn"><i class="fa fa-search"></i></button>
            <button type="button" class="clear-btn" onclick="window.location='students.php'"><i class="fa fa-rotate"></i> Clear</button>
        </form>

        <button class="add-btn" onclick="window.location.href='addstudents.php'">
            <i class="fa fa-plus"></i> Add New Student
        </button>
    </div>

    <div class="table-container">
        <h3>
            <?= $course === 'BSIT' 
                ? 'Bachelor of Science in Information Technology' 
                : 'Bachelor of Science in Computer Studies'; ?>
        </h3>

        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>MI</th>
                    <th>Course</th>
                    <th>Section</th>
                    <th>Year Level</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['students_id']) ?></td>
                    <td><?= htmlspecialchars($row['LastName']) ?></td>
                    <td><?= htmlspecialchars($row['FirstName']) ?></td>
                    <td><?= htmlspecialchars($row['MI']) ?></td>
                    <td><?= htmlspecialchars($row['Course']) ?></td>
                    <td><?= htmlspecialchars($row['Section']) ?></td>
                    <td><?= htmlspecialchars($row['YearLevel']) ?></td>
                    <td>
                        <button class="action-btn view-btn"><i class="fa fa-pen"></i></button>
                        <button class="action-btn delete-btn" onclick="confirmDelete(<?= $row['students_id'] ?>)"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" style="text-align:center;">No students found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This student record will be permanently deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        position: 'center',
        width: '340px',
        padding: '0.6em',
        customClass: {
            popup: 'swal-small-popup'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'students.php?delete_id=' + id;
        }
    });
}
</script>
</body>
</html>







