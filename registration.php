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

// ✅ AUTO FETCH STUDENT NAME
if (isset($_GET['fetch_student'])) {
    $students_id = $_GET['fetch_student'];
    $query = "SELECT FirstName, LastName, MI, Suffix FROM student_profile WHERE students_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $students_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $fullname = trim($row['FirstName'] . ' ' . ($row['MI'] ? $row['MI'] . '. ' : '') . $row['LastName'] . ' ' . $row['Suffix']);
        echo json_encode(["success" => true, "fullname" => $fullname]);
    } else {
        echo json_encode(["success" => false]);
    }
    exit;
}

// ✅ SAVE WHEN PAY BUTTON IS CLICKED
if (isset($_POST['payNow'])) {
    $students_id = $_POST['students_id'];
    $registration_no = 'R' . rand(1000, 9999);
    $registration_date = date("Y-m-d");
    $semester = $_POST['semester'];
    $membership_fee = 100.00;
    $amount = $_POST['amount'];
    $payment_type = $_POST['payment_type'];
    $payment_status = $_POST['payment_status'];
    $user_id = $_SESSION['user_id'] ?? null;

    if ($user_id) {
        $query = "INSERT INTO registration (registration_no, students_id, registration_date, semester, membership_fee, amount, payment_type, payment_status, user_id)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssddssi", $registration_no, $students_id, $registration_date, $semester, $membership_fee, $amount, $payment_type, $payment_status, $user_id);
    } else {
        $query = "INSERT INTO registration (registration_no, students_id, registration_date, semester, membership_fee, amount, payment_type, payment_status)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssddss", $registration_no, $students_id, $registration_date, $semester, $membership_fee, $amount, $payment_type, $payment_status);
    }

    if ($stmt->execute()) {
        // fetch student full name for receipt
        $queryName = "SELECT FirstName, LastName, MI, Suffix FROM student_profile WHERE students_id = ?";
        $stmtName = $conn->prepare($queryName);
        $stmtName->bind_param("s", $students_id);
        $stmtName->execute();
        $resultName = $stmtName->get_result();
        $fullname = "";
        if ($resultName && $resultName->num_rows > 0) {
            $row = $resultName->fetch_assoc();
            $fullname = trim($row['FirstName'] . ' ' . ($row['MI'] ? $row['MI'] . '. ' : '') . $row['LastName'] . ' ' . $row['Suffix']);
        }

        echo "<script>
        window.onload = () => showReceiptPopup('$registration_no', '$students_id', '$fullname', '$registration_date', '$semester', '$membership_fee', '$amount', '$payment_type');
        </script>";
    } else {
        echo "<script>window.onload = () => showPopup('FAILED TO SAVE RECORD. PLEASE TRY AGAIN.', false);</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Registration | CSSO</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body {
  font-family: 'Segoe UI', sans-serif;
  background-color: #f4f6f9;
  margin: 0;
  padding: 20px;
  color: #333;
}
.container { display: flex; gap: 20px; }

/* LEFT FORM */
.form-section {
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  flex: 2;
}
.header-bar {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;
}
.header-bar h2 {
  color: #2563eb; font-weight: 700; display: flex; align-items: center; gap: 10px;
}
.header-bar h2 i { color: #2563eb; }
.list-btn {
  background: #2563eb; color: #fff; border: none; padding: 10px 18px;
  border-radius: 6px; cursor: pointer; font-size: 15px; transition: 0.3s;
}
.list-btn:hover { background: #0b2559; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px 20px; }
.form-group label { display: block; font-weight: 600; margin-bottom: 5px; }
.form-group input, .form-group select {
  width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; font-size: 15px;
}
.form-group input[readonly] {
  background-color: #f0f0f0; font-weight: 600; color: #444;
}

/* RIGHT SIDE */
.summary {
  flex: 1; background: #232222ff; border-radius: 12px; color: #fff;
  display: flex; flex-direction: column; justify-content: space-between;
  padding: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
.summary-box { text-align: center; }
.summary h3 {
  background: #243763; color: #fff; padding: 8px;
  border-radius: 6px; margin-bottom: 10px;
}
.summary .amount { font-size: 40px; font-weight: bold; }
.summary input {
  width: 100%; text-align: center; padding: 8px; font-size: 20px;
  margin-top: 8px; border-radius: 6px; border: none;
}
.button {
  background: #1e8449; border: none; color: #fff;
  font-size: 18px; padding: 12px; border-radius: 8px;
  cursor: pointer; width: 100%; transition: 0.3s;
}
.button:hover { background: #27ae60; }

/* POPUP RECEIPT */
.popup {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  display: none; justify-content: center; align-items: center;
  background: rgba(0,0,0,0.4); z-index: 999;
}
.popup-content {
  background: #fff; padding: 25px 40px; border-radius: 10px;
  font-size: 16px; color: #000; box-shadow: 0 4px 20px rgba(0,0,0,0.3);
  animation: popIn 0.3s ease; width: 450px;
}
@keyframes popIn { from {transform: scale(0.7); opacity: 0;} to {transform: scale(1); opacity: 1;} }
.popup-header { text-align: center; margin-bottom: 15px; font-weight: bold; color: black; }
.popup-header h2, .popup-header p { margin: 3px 0; color: black; }
.blue { color: #1e3a8a; font-weight: bold; }
.receipt-table { width: 100%; margin-top: 10px; border-collapse: collapse; }
.receipt-table td { padding: 6px 4px; }
.receipt-table td:first-child { font-weight: 600; }

.btn-row { text-align: center; margin-top: 20px; }
.close-btn, .print-btn {
  background: #1e3a8a; color: #fff; border: none; padding: 8px 25px;
  border-radius: 6px; cursor: pointer; font-size: 15px; margin: 0 5px;
}
.print-btn { background: #1e8449; }
.print-btn:hover { background: #27ae60; }
.close-btn:hover { background: #0b2559; }

/* CENTERED CANCEL POPUP */
.center-popup {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  display: none; justify-content: center; align-items: center;
  background: rgba(0,0,0,0.5); z-index: 1000;
}
.center-content {
  background: #fff; padding: 25px 40px; border-radius: 10px;
  text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}
.center-content h3 { margin: 0; color: #000; font-size: 18px; }
</style>
</head>
<body>

<div class="container">
  <div class="form-section">
    <div class="header-bar">
      <h2><i class="fa-solid fa-user-plus"></i> Registration</h2>
      <button class="list-btn" onclick="window.location.href='reglist.php'"><i class="fa-solid fa-list"></i> Registration List</button>
    </div>

    <form method="POST" id="regForm">
      <div class="form-grid">
        <div class="form-group">
          <label>Student ID</label>
          <input type="text" name="students_id" id="students_id" placeholder="Enter Student ID" required>
        </div>
        <div class="form-group">
          <label>Student Name</label>
          <input type="text" id="student_name" placeholder="Auto-filled" readonly>
        </div>
        <div class="form-group">
          <label>Registration Date</label>
          <input type="text" name="registration_date" value="<?php echo date('Y-m-d'); ?>" readonly>
        </div>
        <div class="form-group">
          <label>Semester</label>
          <select name="semester" required>
            <option value="">Select Semester</option>
            <option value="First Semester">First Semester</option>
            <option value="Second Semester">Second Semester</option>
          </select>
        </div>
        <div class="form-group">
          <label>Membership Fee</label>
          <input type="text" name="membership_fee" value="₱100.00" readonly>
        </div>
        <div class="form-group">
          <label>Amount</label>
          <input type="number" name="amount" id="amount" placeholder="Enter amount" required>
        </div>
        <div class="form-group">
          <label>Payment Type</label>
          <select name="payment_type" required>
            <option value="Cash">Cash</option>
            <option value="Gcash">Gcash</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div class="form-group">
          <label>Payment Status</label>
          <select name="payment_status" required>
            <option value="Paid">Paid</option>
            <option value="Unpaid">Unpaid</option>
            <option value="Partial Paid">Partial Paid</option>
          </select>
        </div>
      </div>
      <input type="hidden" name="payNow" value="1">
    </form>
  </div>

  <!-- RIGHT SIDE -->
  <div class="summary">
    <div class="summary-box">
      <h3>Membership Fee</h3>
      <div class="amount" style="color:red;">₱100.00</div>
    </div>
    <div class="summary-box">
      <h3>Amount</h3>
      <div class="amount" id="displayAmount" style="color:lime;">₱0.00</div>
    </div>
    <div class="summary-box">
      <h3>Cash</h3>
      <input type="number" id="cash" placeholder="Enter cash">
    </div>
    <div class="summary-box">
      <h3>Change</h3>
      <div class="amount" id="changeDisplay" style="color:orange;">₱0.00</div>
    </div>
    <button class="button" id="payBtn" type="button"><i class="fa-solid fa-money-bill"></i> PAY</button>
  </div>
</div>

<!-- RECEIPT POPUP -->
<div class="popup" id="popupBox">
  <div class="popup-content" id="popupContent"></div>
</div>

<!-- CENTERED CANCEL MESSAGE -->
<div class="center-popup" id="centerPopup">
  <div class="center-content">
    <h3>This student is now a member of the CSSO.</h3>
  </div>
</div>

<script>
const studentID = document.getElementById('students_id');
const studentName = document.getElementById('student_name');

studentID.addEventListener('input', function() {
  const id = this.value.trim();
  if (id.length >= 5) {
    fetch(`registration.php?fetch_student=${id}`)
      .then(res => res.json())
      .then(data => {
        studentName.value = data.success ? data.fullname : "No record found";
      })
      .catch(() => studentName.value = "Error fetching");
  } else studentName.value = "";
});

const amountInput = document.getElementById('amount');
const cashInput = document.getElementById('cash');
const displayAmount = document.getElementById('displayAmount');
const changeDisplay = document.getElementById('changeDisplay');
amountInput.addEventListener('input', () => displayAmount.textContent = '₱' + (parseFloat(amountInput.value || 0)).toFixed(2));
cashInput.addEventListener('input', () => {
  let amt = parseFloat(amountInput.value || 0);
  let cash = parseFloat(cashInput.value || 0);
  let change = cash - amt;
  changeDisplay.textContent = '₱' + (change > 0 ? change.toFixed(2) : '0.00');
});
document.getElementById('payBtn').addEventListener('click', () => document.getElementById('regForm').submit());

function showReceiptPopup(no, id, fullname, date, sem, fee, amount, type) {
  const popup = document.getElementById('popupBox');
  const content = document.getElementById('popupContent');
  popup.style.display = 'flex';
  content.innerHTML = `
    <div class='popup-header'>
      <h2>COMPUTER STUDIES STUDENT ORGANIZATION</h2>
      <p>Camiguin Polytechnic State College</p>
      <p>Balbagon, Mambajao 9100, Camiguin Province</p>
    </div>
    <hr>
    <table class='receipt-table'>
      <tr><td>Registration No:</td><td>${no}</td></tr>
      <tr><td>Student ID:</td><td>${id}</td></tr>
      <tr><td>Student Name:</td><td>${fullname}</td></tr>
      <tr><td>Date:</td><td>${date}</td></tr>
      <tr><td>Semester:</td><td>${sem}</td></tr>
      <tr><td>Membership Fee:</td><td>₱${parseFloat(fee).toFixed(2)}</td></tr>
      <tr><td>Amount Paid:</td><td>₱${parseFloat(amount).toFixed(2)}</td></tr>
      <tr><td>Payment Type:</td><td class='blue'>${type}</td></tr>
    </table>
    <div class='btn-row'>
      <button class='print-btn' id='printBtn'>PRINT RECEIPT</button>
      <button class='close-btn' id='cancelBtn'>CANCEL</button>
    </div>
  `;
  document.getElementById('printBtn').onclick = () => window.print();
  document.getElementById('cancelBtn').onclick = () => {
    popup.style.display = 'none';
    document.getElementById('centerPopup').style.display = 'flex';
    setTimeout(() => document.getElementById('centerPopup').style.display = 'none', 2000);
  };
}
</script>

</body>
</html>







