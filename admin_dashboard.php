<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['usertype'], ['Governor', 'Vice Governor'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | CSSO</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
  font-family: 'Segoe UI', sans-serif;
}
body {
  background: #f7f9fc;
  color: #333;
  height: 100vh;
  display: flex;
  overflow: hidden;
}

/* Sidebar */
.sidebar {
  width: 250px;
  background: #fff;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  border-right: 1px solid #e2e8f0;
  overflow: hidden;
}
.sidebar.collapsed {
  width: 80px;
}
.sidebar-header {
  text-align: center;
  padding: 15px 10px;
  background: #022e74ff;
  position: relative;
}
.sidebar-header img {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  border: 3px solid #fff;
  transition: all 0.3s ease;
}
.sidebar.collapsed .sidebar-header img {
  width: 45px;
  height: 45px;
}
.sidebar-header button {
  position: absolute;
  right: 10px;
  top: 10px;
  background: none;
  border: none;
  color: #fff;
  font-size: 18px;
  cursor: pointer;
}

/* Menu Styling */
.menu {
  list-style: none;
  padding: 10px 0;
  margin: 0;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 3px;
}
.menu li {
  padding: 12px 18px;
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  border-radius: 8px;
  margin: 3px 10px;
  transition: all 0.25s ease;
}
.menu li:hover,
.menu li.active {
  background: #e2e8f0;
}
.menu li i {
  font-size: 18px;
  min-width: 25px;
  text-align: center;
  transition: all 0.3s ease;
}
.menu li span {
  font-weight: 500;
  color: #111;
  white-space: nowrap;
  transition: opacity 0.3s ease;
}

/* Collapsed mode */
.sidebar.collapsed .menu li {
  justify-content: center;
  padding: 12px 0;
}
.sidebar.collapsed .menu li i {
  font-size: 20px;
  margin-right: 0;
}
.sidebar.collapsed .menu li span {
  opacity: 0;
  pointer-events: none;
  width: 0;
}

/* Tooltip on hover (collapsed mode) */
.sidebar.collapsed .menu li:hover::after {
  content: attr(data-title);
  position: absolute;
  left: 85px;
  background: #0a1931;
  color: #fff;
  padding: 5px 10px;
  border-radius: 6px;
  font-size: 13px;
  white-space: nowrap;
  z-index: 999;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

/* Main Area */
.main {
  flex: 1;
  display: flex;
  flex-direction: column;
}
.topbar {
  background: #fff;
  padding: 15px 25px;
  border-bottom: 2px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #0a1931;
}
.topbar h2 {
  font-weight: 600;
  color: #0a1931;
}
.user-info {
  text-align: right;
  color: #0a1931;
  font-size: 14px;
  line-height: 1.4;
}
.user-info strong {
  font-size: 16px;
  color: #0a1931;
}

/* Iframe */
iframe {
  width: 100%;
  height: calc(100vh - 70px);
  border: none;
  background: #f7f9fc;
  transition: all 0.3s ease;
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <img src="../images/cssologo.png" alt="CSSO Logo">
    <button id="toggleSidebar"><i class="fa fa-bars"></i></button>
  </div>
  <ul class="menu">
    <li class="active" data-title="Dashboard" onclick="navigate('dashboard.php', this)"><i class="fa fa-gauge"></i><span>Dashboard</span></li>
    <li data-title="Students" onclick="navigate('students.php', this)"><i class="fa fa-users"></i><span>Students</span></li>
    <li data-title="Registration" onclick="navigate('registration.php', this)"><i class="fa fa-file-invoice-dollar"></i><span>Registration</span></li>
    <li data-title="Events" onclick="navigate('events.php', this)"><i class="fa fa-calendar"></i><span>Events</span></li>
    <li data-title="Attendance" onclick="navigate('attendance.php', this)"><i class="fa fa-clipboard-check"></i><span>Attendance</span></li>
    <li data-title="Fines" onclick="navigate('fines.php', this)"><i class="fa fa-gavel"></i><span>Fines</span></li>
    <li data-title="Payments" onclick="navigate('payments.php', this)"><i class="fa fa-wallet"></i><span>Payments</span></li>
    <li data-title="User Management" onclick="navigate('users.php', this)"><i class="fa fa-user-cog"></i><span>User Management</span></li>
    <li data-title="Reports" onclick="navigate('reports.php', this)"><i class="fa fa-chart-line"></i><span>Reports</span></li>
    <li data-title="Logout" onclick="logout()"><i class="fa fa-sign-out-alt"></i><span>Logout</span></li>
  </ul>
</div>

<!-- Main -->
<div class="main">
  <div class="topbar">
    <h2>Computer Studies Student Organization</h2>
    <div class="user-info">
      <strong>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></strong><br>
      <?= htmlspecialchars($_SESSION['usertype']) ?><br>
      <span id="datetime"></span>
    </div>
  </div>

  <!-- Default Content Frame -->
  <iframe id="contentFrame" src="dashboard.php"></iframe>
</div>

<script>
const sidebar = document.getElementById("sidebar");
const toggleBtn = document.getElementById("toggleSidebar");

toggleBtn.onclick = () => {
  sidebar.classList.toggle("collapsed");
};

function updateDateTime() {
  const now = new Date();
  document.getElementById('datetime').textContent =
    now.toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' }) +
    ' | ' + now.toLocaleTimeString();
}
setInterval(updateDateTime, 1000);
updateDateTime();

function navigate(page, element){
  document.getElementById('contentFrame').src = page;
  document.querySelectorAll('.menu li').forEach(i => i.classList.remove('active'));
  element.classList.add('active');
}

/* ✅ SweetAlert Logout Confirmation */
function logout(){
  Swal.fire({
    title: 'Are you sure you want to log out?',
    text: "You will be redirected to the login page.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, log me out',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: 'Logging out...',
        icon: 'success',
        showConfirmButton: false,
        timer: 1000
      });
      setTimeout(() => {
        window.location.href = '../login.php';
      }, 1000);
    }
  });
}
</script>

</body>
</html>


