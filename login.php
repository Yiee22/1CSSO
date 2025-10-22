<?php
session_start();

$conn = new mysqli("localhost", "root", "", "csso");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$success = false;
$loginSuccess = false;
$redirectPage = "";

// ==================== LOGIN PROCESS ====================
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $usertype = trim($_POST['usertype']);

    $sql = "SELECT * FROM users WHERE username=? AND usertype=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $usertype);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $role = strtolower($row['usertype']);

        // ✅ Governor always ON, others must be status='on' or 1
        if ($role === 'governor') {
            $statusOk = true;
        } else {
            $statusOk = false;
            if (isset($row['status'])) {
                $statusOk = ($row['status'] == 1 || strtolower($row['status']) === 'on');
            }
        }

        if (!$statusOk) {
            $message = "Your account is not yet activated. Please wait for admin approval.";
        }
        elseif (!password_verify($password, $row['password'])) {
            $message = "Invalid password!";
        }
        else {
            // ✅ Login success
            session_regenerate_id(true);
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['usertype'] = $row['usertype'];

            $loginSuccess = true;

            // ✅ Redirect based on role
            if ($row['usertype'] == "Governor" || $row['usertype'] == "Vice Governor") {
                $redirectPage = "admin/admin_dashboard.php";
            } else {
                $redirectPage = "user/user_dashboard.php";
            }
        }
    } else {
        $message = "Username or usertype not found!";
    }
}

// ==================== SIGNUP PROCESS ====================
if (isset($_POST['signup'])) {
    $fname = trim($_POST['first_name']);
    $lname = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $rawPassword = trim($_POST['password']);
    $usertype = trim($_POST['usertype']);

    $limits = [
        'Governor'        => 1,
        'Vice Governor'   => 1,
        'Secretary'       => 1,
        'Auditor'         => 1,
        'Treasurer'       => 1,
        'Social Manager'  => 5,
        'Senator'         => 5
    ];

    if (!array_key_exists($usertype, $limits)) {
        $message = "Invalid user type selected!";
    } else {
        $checkUsername = $conn->prepare("SELECT COUNT(*) AS total FROM users WHERE username = ?");
        $checkUsername->bind_param("s", $username);
        $checkUsername->execute();
        $resUser = $checkUsername->get_result()->fetch_assoc();

        if ($resUser['total'] > 0) {
            $message = "Username already taken! Please choose another.";
        } else {
            $checkRole = $conn->prepare("SELECT COUNT(*) AS total FROM users WHERE usertype = ?");
            $checkRole->bind_param("s", $usertype);
            $checkRole->execute();
            $resRole = $checkRole->get_result()->fetch_assoc();

            $limit = $limits[$usertype];
            if ($resRole['total'] >= $limit) {
                $message = "$usertype is already registered. Only one $usertype is allowed.";
            } else {
                if (strlen($rawPassword) < 8) {
                    $message = "Password must be at least 8 characters long!";
                } else {
                    $password = password_hash($rawPassword, PASSWORD_DEFAULT);
                    $defaultStatus = ($usertype == 'Governor') ? 'on' : 'off';

                    $sql = "INSERT INTO users (first_name, last_name, username, password, usertype, status) 
                            VALUES (?,?,?,?,?,?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssss", $fname, $lname, $username, $password, $usertype, $defaultStatus);

                    if ($stmt->execute()) {
                        $success = true;
                    } else {
                        $message = "Error: " . $stmt->error;
                    }
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - CSSO</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body {
  font-family: 'Segoe UI', Arial, sans-serif;
  background: #f4f6fa;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
}

/* ✅ Login Box */
.login-box {
  background: rgba(255, 255, 255, 0.5);
  width: 380px;
  padding: 32px 26px;
  border-radius: 16px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
  border: 2px solid rgba(0, 123, 255, 0.4);
  backdrop-filter: blur(15px);
}

h2 {
  text-align: center;
  margin-bottom: 22px;
  color: #000;
  font-weight: 600;
}

/* ✅ Input Group */
.input-group {
  position: relative;
  margin-bottom: 15px;
}

.input-group i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #000;
  font-size: 14px;
}

/* ✅ Align input and select perfectly */
.input-group input,
.input-group select {
  width: 100%;
  height: 45px;
  padding: 10px 10px 10px 38px;
  border-radius: 6px;
  border: 1px solid rgba(0,0,0,0.25);
  outline: none;
  font-size: 14px;
  color: #000;
  background: rgba(255,255,255,0.85);
  box-sizing: border-box;
}

.input-group input:focus,
.input-group select:focus {
  border-color: #007bff;
  box-shadow: 0 0 4px rgba(0, 123, 255, 0.3);
}

/* ✅ Buttons */
.btn-primary {
  width: 100%;
  height: 45px;
  background: linear-gradient(135deg, #00c853, #64dd17);
  color: #fff;
  border: none;
  border-radius: 6px;
  font-size: 15px;
  cursor: pointer;
  margin-top: 5px;
  transition: 0.3s ease;
}
.btn-primary:hover {
  background: linear-gradient(135deg, #64dd17, #00c853);
}

/* ✅ Separator Line */
.separator {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 16px 0;
  color: #555;
  font-size: 13px;
}
.separator::before,
.separator::after {
  content: "";
  flex: 1;
  height: 1px;
  background: rgba(0,0,0,0.2);
  margin: 0 10px;
}

/* ✅ Toggle Section */
.toggle-section {
  text-align: center;
  margin-top: 14px;
  color: #000;
  font-size: 14px;
}
.toggle-link {
  cursor: pointer;
  color: #000;
  font-weight: bold;
}
.toggle-link:hover {
  text-decoration: underline;
}

/* ✅ Two-column signup row */
.row {
  display: flex;
  gap: 10px;
}
.row .input-group {
  flex: 1;
}

/* ✅ Smooth transition for form switching */
#signupForm {
  opacity: 0;
  height: 0;
  overflow: hidden;
  pointer-events: none;
  transition: all 0.4s ease;
}
#signupForm.active {
  opacity: 1;
  height: auto;
  pointer-events: auto;
}
#loginForm.hide {
  opacity: 0;
  height: 0;
  overflow: hidden;
  pointer-events: none;
}
</style>
</head>

<body>
<div class="login-box">
  <h2 id="formTitle">Login</h2>

  <!-- ✅ LOGIN FORM -->
  <form method="POST" id="loginForm" autocomplete="off">
    <div class="input-group"><i class="fa fa-user"></i><input type="text" name="username" placeholder="Enter your username" required></div>
    <div class="input-group"><i class="fa fa-lock"></i><input type="password" name="password" placeholder="Enter your password" required></div>
    <div class="input-group">
      <i class="fa fa-users"></i>
      <select name="usertype" required>
        <option value="" disabled selected>Select user type</option>
        <option value="Governor">Governor</option>
        <option value="Vice Governor">Vice Governor</option>
        <option value="Secretary">Secretary</option>
        <option value="Auditor">Auditor</option>
        <option value="Treasurer">Treasurer</option>
        <option value="Social Manager">Social Manager</option>
        <option value="Senator">Senator</option>
      </select>
    </div>
    <button type="submit" class="btn-primary" name="login">Login</button>

    <div class="separator">OR</div>

    <div class="toggle-section">
      Don’t have an account? <span class="toggle-link" onclick="toggleForms('signup')">Sign up here</span>
    </div>
  </form>

  <!-- ✅ SIGNUP FORM -->
  <form method="POST" id="signupForm" autocomplete="off">
    <div class="row">
      <div class="input-group"><i class="fa fa-user"></i><input type="text" name="first_name" placeholder="First name" required></div>
      <div class="input-group"><i class="fa fa-user"></i><input type="text" name="last_name" placeholder="Last name" required></div>
    </div>
    <div class="input-group"><i class="fa fa-at"></i><input type="text" name="username" placeholder="Choose a username" required></div>
    <div class="input-group"><i class="fa fa-lock"></i><input type="password" name="password" id="signupPassword" placeholder="Create a password" required></div>
    <div class="input-group">
      <i class="fa fa-id-badge"></i>
      <select name="usertype" required>
        <option value="" disabled selected>Select user type</option>
        <option value="Governor">Governor</option>
        <option value="Vice Governor">Vice Governor</option>
        <option value="Secretary">Secretary</option>
        <option value="Auditor">Auditor</option>
        <option value="Treasurer">Treasurer</option>
        <option value="Social Manager">Social Manager</option>
        <option value="Senator">Senator</option>
      </select>
    </div>
    <button type="submit" class="btn-primary" name="signup">Create Account</button>

    <div class="toggle-section">
      Already have an account? <span class="toggle-link" onclick="toggleForms('login')">Login here</span>
    </div>
  </form>
</div>

<!-- ✅ SWEETALERT MESSAGES -->
<?php if ($message != ""): ?>
<script>
Swal.fire({
  title: "Error!",
  text: "<?php echo $message; ?>",
  icon: "error",
  confirmButtonColor: "#1E40AF",
  confirmButtonText: "OK"
});
</script>
<?php endif; ?>

<?php if ($success): ?>
<script>
Swal.fire({
  title: "Signup Successful!",
  text: "You can now login.",
  icon: "success",
  confirmButtonColor: "#1E40AF"
}).then(() => {
  toggleForms('login');
});
</script>
<?php endif; ?>

<?php if ($loginSuccess): ?>
<script>
Swal.fire({
  title: "Login Successful!",
  text: "Welcome back!",
  icon: "success",
  confirmButtonColor: "#1E40AF",
  timer: 1500,
  showConfirmButton: false
}).then(() => {
  window.location.href = "<?php echo $redirectPage; ?>";
});
</script>
<?php endif; ?>

<script>
function toggleForms(form) {
  const loginForm = document.getElementById('loginForm');
  const signupForm = document.getElementById('signupForm');
  const formTitle = document.getElementById('formTitle');
  document.querySelectorAll('input').forEach(i => i.value = '');
  document.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
  if (form === 'signup') {
    loginForm.classList.add('hide');
    signupForm.classList.add('active');
    formTitle.innerText = "Create Account";
  } else {
    loginForm.classList.remove('hide');
    signupForm.classList.remove('active');
    formTitle.innerText = "Login";
  }
}
</script>
</body>
</html>
