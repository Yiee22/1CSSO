<?php
session_start();
if (!isset($_SESSION['username']) || !in_array($_SESSION['usertype'], ['Governor', 'Vice Governor'])) {
    header("Location: ../login.php");
    exit();
}

// Database Connection
$conn = new mysqli("localhost", "root", "", "csso");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Save Student Info
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $students_id = $_POST['students_id']; // Manual input by user

    // Generate fam_id and edu_id
    $resultFam = $conn->query("SELECT COUNT(*) AS count FROM family_background");
    $famCount = $resultFam->fetch_assoc()['count'] + 1;
    $fam_id = "fam" . $famCount;

    $resultEdu = $conn->query("SELECT COUNT(*) AS count FROM educational_background");
    $eduCount = $resultEdu->fetch_assoc()['count'] + 1;
    $edu_id = "edu" . $eduCount;

    // Insert into student_profile
    $stmt = $conn->prepare("INSERT INTO student_profile 
        (students_id, user_id, FirstName, LastName, MI, Suffix, Course, YearLevel, Section, PhoneNumber, Gender, DOB, Age, Religion, EmailAddress, Street, Barangay, Municipality, Province, ZipCode) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssssssssisssssss", 
        $students_id, $user_id, $_POST['FirstName'], $_POST['LastName'], $_POST['MI'], $_POST['Suffix'], $_POST['Course'], 
        $_POST['YearLevel'], $_POST['Section'], $_POST['PhoneNumber'], $_POST['Gender'], $_POST['DOB'], $_POST['Age'], 
        $_POST['Religion'], $_POST['EmailAddress'], $_POST['Street'], $_POST['Barangay'], $_POST['Municipality'], 
        $_POST['Province'], $_POST['ZipCode']
    );

    if ($stmt->execute()) {

        // Insert into family_background
        $stmt2 = $conn->prepare("INSERT INTO family_background 
            (fam_id, students_id, father_name, father_occupation, mother_name, mother_occupation, phone_number, siblings_count, guardian_name, guardian_occupation, contact_number, street, barangay, municipality, province, zipcode)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("sisssssisssssss", 
            $fam_id, $students_id,
            $_POST['father_name'], $_POST['father_occupation'],
            $_POST['mother_name'], $_POST['mother_occupation'],
            $_POST['phone_number'], $_POST['siblings_count'],
            $_POST['guardian_name'], $_POST['guardian_occupation'],
            $_POST['contact_number'], $_POST['fam_street'],
            $_POST['fam_barangay'], $_POST['fam_municipality'],
            $_POST['fam_province'], $_POST['fam_zipcode']
        );
        $stmt2->execute();

        // Insert into educational_background
        $stmt3 = $conn->prepare("INSERT INTO educational_background 
            (edu_id, students_id, elementary, elem_year_grad, elem_received, junior_high, jr_high_grad, jr_received, senior_high, sr_high_grad, sr_received)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt3->bind_param("sissssssss", 
            $edu_id, $students_id,
            $_POST['elementary'], $_POST['elem_year_grad'], $_POST['elem_received'],
            $_POST['junior_high'], $_POST['jr_high_grad'], $_POST['jr_received'],
            $_POST['senior_high'], $_POST['sr_high_grad'], $_POST['sr_received']
        );
        $stmt3->execute();

        echo "<script>alert('Student successfully added!'); window.location='students.php';</script>";
    } else {
        echo "<script>alert('Error saving student data.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Student | CSSO</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { font-family:'Segoe UI',sans-serif; background:#f8fafc; margin:0; padding:0; }
.container { width:90%; max-width:900px; margin:30px auto; background:#fff; border-radius:10px; padding:25px; box-shadow:0 2px 10px rgba(0,0,0,0.1);}
h2 { color:#1e3a8a; display:flex; align-items:center; gap:10px; }
h2 i { background:#2563eb; color:#fff; padding:8px; border-radius:8px; }
section { margin-top:30px; }
section h3 { color:#334155; border-left:4px solid #2563eb; padding-left:8px; }
label { display:block; margin-top:10px; font-weight:600; color:#1e293b;}
input, select { width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:6px; margin-top:4px; }
input[readonly] { background:#f1f5f9; }
.hidden { display:none; }
.buttons { display:flex; justify-content:space-between; align-items:center; margin-top:30px; }
button { border:none; padding:10px 18px; border-radius:6px; cursor:pointer; font-weight:600; }
.back-btn { background:#64748b; color:#fff; }
.back-btn:hover { background:#475569; }
.save-btn { background:#2563eb; color:#fff; }
.save-btn:hover { background:#1e40af; }
</style>
<script>
function calculateAge() {
    const dob = document.querySelector('input[name="DOB"]').value;
    if(dob){
        const birthDate = new Date(dob);
        const diff = Date.now() - birthDate.getTime();
        const age = new Date(diff).getUTCFullYear() - 1970;
        document.querySelector('input[name="Age"]').value = age;
    }
}
</script>
</head>
<body>
<div class="container">
<h2><i class="fa fa-user-plus"></i> Add New Student</h2>

<form method="POST">
    <!-- STUDENT PROFILE -->
    <section>
        <h3>Student Profile</h3>
        <label>Students ID</label><input type="text" name="students_id" required>
        <label>First Name</label><input type="text" name="FirstName" required>
        <label>Last Name</label><input type="text" name="LastName" required>
        <label>Middle Initial</label><input type="text" name="MI">
        <label>Suffix</label>
        <select name="Suffix">
            <option value="">None</option>
            <option value="Jr">Jr</option>
            <option value="Sr">Sr</option>
            <option value="III">III</option>
            <option value="IV">IV</option>
        </select>
        <label>Course</label>
        <select name="Course" required>
            <option value="BSIT">BSIT</option>
            <option value="BSCS">BSCS</option>
        </select>
        <label>Year Level</label>
        <select name="YearLevel" required>
            <option value="1stYear">1st Year</option>
            <option value="2ndYear">2nd Year</option>
            <option value="3rdYear">3rd Year</option>
            <option value="4thYear">4th Year</option>
        </select>
        <label>Section</label>
        <select name="Section" required>
            <option value="BSIT 1A">BSIT 1A</option>
            <option value="BSIT 1B">BSIT 1B</option>
            <option value="BSIT 2A">BSIT 2A</option>
            <option value="BSIT 2B">BSIT 2B</option>
            <option value="BSIT 3A">BSIT 3A</option>
            <option value="BSIT 3B">BSIT 3B</option>
            <option value="BSIT 4A">BSIT 4A</option>
            <option value="BSIT 4B">BSIT 4B</option>
            <option value="BSCS 1A">BSCS 1A</option>
            <option value="BSCS 1B">BSCS 1B</option>
            <option value="BSCS 2A">BSCS 2A</option>
            <option value="BSCS 2B">BSCS 2B</option>
            <option value="BSCS 3A">BSCS 3A</option>
            <option value="BSCS 3B">BSCS 3B</option>
            <option value="BSCS 4A">BSCS 4A</option>
            <option value="BSCS 4B">BSCS 4B</option>
        </select>
        <label>Phone Number</label><input type="text" name="PhoneNumber">
        <label>Gender</label>
        <select name="Gender" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>
        <label>Date of Birth</label><input type="date" name="DOB" onchange="calculateAge()">
        <label>Age</label><input type="number" name="Age" readonly>
        <label>Religion</label><input type="text" name="Religion">
        <label>Email Address</label><input type="email" name="EmailAddress">
        <label>Street</label><input type="text" name="Street">
        <label>Barangay</label><input type="text" name="Barangay">
        <label>Municipality</label><input type="text" name="Municipality">
        <label>Province</label><input type="text" name="Province">
        <label>Zip Code</label><input type="text" name="ZipCode">
    </section>

    <!-- FAMILY BACKGROUND -->
    <section>
        <h3>Family Background</h3>
        <input type="hidden" name="fam_id" class="hidden">
        <label>Father's Name</label><input type="text" name="father_name" required>
        <label>Father's Occupation</label><input type="text" name="father_occupation">
        <label>Mother's Name</label><input type="text" name="mother_name">
        <label>Mother's Occupation</label><input type="text" name="mother_occupation">
        <label>Phone Number</label><input type="text" name="phone_number">
        <label>Number of Siblings</label><input type="number" name="siblings_count">
        <label>Guardian Name</label><input type="text" name="guardian_name">
        <label>Guardian Occupation</label><input type="text" name="guardian_occupation">
        <label>Guardian Contact</label><input type="text" name="contact_number">
        <label>Street</label><input type="text" name="fam_street">
        <label>Barangay</label><input type="text" name="fam_barangay">
        <label>Municipality</label><input type="text" name="fam_municipality">
        <label>Province</label><input type="text" name="fam_province">
        <label>Zip Code</label><input type="text" name="fam_zipcode">
    </section>

    <!-- EDUCATIONAL BACKGROUND -->
    <section>
        <h3>Educational Background</h3>
        <input type="hidden" name="edu_id" class="hidden">
        <label>Elementary School</label><input type="text" name="elementary">
        <label>Year Graduated</label><input type="date" name="elem_year_grad">
        <label>Received</label><input type="text" name="elem_received">
        <label>Junior High School</label><input type="text" name="junior_high">
        <label>Year Graduated</label><input type="date" name="jr_high_grad">
        <label>Received</label><input type="text" name="jr_received">
        <label>Senior High School</label><input type="text" name="senior_high">
        <label>Year Graduated</label><input type="date" name="sr_high_grad">
        <label>Received</label><input type="text" name="sr_received">
    </section>

    <div class="buttons">
        <button type="button" class="back-btn" onclick="window.location.href='students.php'"><i class="fa fa-arrow-left"></i> Back</button>
        <button type="submit" class="save-btn"><i class="fa fa-save"></i> Add Student</button>
    </div>
</form>
</div>
</body>
</html>



