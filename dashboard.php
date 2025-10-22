<?php
// Connect to database
$conn = new mysqli("localhost", "root", "", "csso");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get dashboard data
$totalStudents = $conn->query("SELECT COUNT(*) FROM student_profile")->fetch_row()[0] ?? 0;
$registrationCollected = $conn->query("SELECT IFNULL(SUM(amount),0) FROM registration WHERE payment_status='Paid'")->fetch_row()[0] ?? 0;
$finesCollected = $conn->query("SELECT IFNULL(SUM(penalty_amount),0) FROM fines_payments WHERE payment_status='Paid'")->fetch_row()[0] ?? 0;
$totalIncome = $registrationCollected + $finesCollected;
$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0] ?? 0;

$recent = $conn->query("SELECT sp.FirstName, sp.LastName, r.registration_date 
                        FROM registration r 
                        JOIN student_profile sp ON r.students_id = sp.students_id 
                        ORDER BY r.registration_date DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', sans-serif;
    background: #f7f9fc;
}
.container {
    padding: 20px;
}
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}
.card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: 0.3s;
}
.card:hover {
    transform: translateY(-4px);
}
.card i {
    font-size: 38px;
}
.card.blue i { color: #1e40af; }
.card.green i { color: #00ff66ff; }
.card.yellow i { color: #f59e0b; }
.card.purple i { color: #7c3aed; }
.card.red i { color: #dc2626; }

.card h3 {
    margin: 0;
    font-size: 15px;
    color: #475569;
}
.card p {
    font-size: 20px;
    font-weight: bold;
    color: #0f172a;
    margin: 5px 0 0;
}

/* Layout for Recent + Calendar */
.bottom-section {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}
.recent, .calendar {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    padding: 15px;
    flex: 1;
    min-width: 320px;
}

/* Recent Table */
.recent h3 {
    color: #0f172a;
    font-size: 18px;
    margin-bottom: 10px;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 8px;
}
.recent table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}
.recent th, .recent td {
    padding: 10px;
    border-bottom: 1px solid #f1f5f9;
}
.recent th {
    background: #0a1931;
    color: #fff;
}

/* Calendar */
.calendar h3 {
    color: #0f172a;
    font-size: 18px;
    margin-bottom: 15px;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 8px;
}
#calendar {
    width: 100%;
    text-align: center;
}
.calendar table {
    width: 100%;
    border-collapse: collapse;
}
.calendar th {
    color: #475569;
    font-weight: 600;
    padding: 6px 0;
}
.calendar td {
    padding: 10px;
    text-align: center;
    border-radius: 6px;
    transition: background 0.2s;
}
.calendar td:hover {
    background: #e0f2fe;
    cursor: pointer;
}
.today {
    background: #2563eb;
    color: white !important;
    font-weight: bold;
}
</style>
</head>
<body>
<div class="container">
    <div class="cards">
        <div class="card blue">
            <i class="fa fa-users"></i>
            <div>
                <h3>Total Students</h3>
                <p><?= $totalStudents ?></p>
            </div>
        </div>
        <div class="card green">
            <i class="fa fa-file-invoice-dollar"></i>
            <div>
                <h3>Registration Collected</h3>
                <p>₱<?= number_format($registrationCollected, 2) ?></p>
            </div>
        </div>
        <div class="card yellow">
            <i class="fa fa-gavel"></i>
            <div>
                <h3>Fines Collected</h3>
                <p>₱<?= number_format($finesCollected, 2) ?></p>
            </div>
        </div>
        <div class="card purple">
            <i class="fa fa-wallet"></i>
            <div>
                <h3>Total Income</h3>
                <p>₱<?= number_format($totalIncome, 2) ?></p>
            </div>
        </div>
        <div class="card red">
            <i class="fa fa-user"></i>
            <div>
                <h3>Total Users Registered</h3>
                <p><?= $totalUsers ?></p>
            </div>
        </div>
    </div>

    <div class="bottom-section">
        <div class="recent">
            <h3><i class="fa fa-user-plus"></i> Recent Registered Students</h3>
            <table>
                <thead>
                    <tr><th>Name</th><th>Date Registered</th></tr>
                </thead>
                <tbody>
                    <?php if ($recent->num_rows > 0): ?>
                        <?php while($r = $recent->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['FirstName'] . ' ' . $r['LastName']) ?></td>
                                <td><?= $r['registration_date'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="2">No recent students found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="calendar">
            <h3><i class="fa fa-calendar"></i> Calendar</h3>
            <div id="calendar"></div>
        </div>
    </div>
</div>

<script>
// Calendar generator
function generateCalendar() {
    const today = new Date();
    const month = today.getMonth();
    const year = today.getFullYear();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);

    const months = [
        "January","February","March","April","May","June",
        "July","August","September","October","November","December"
    ];

    let html = `<h4>${months[month]} ${year}</h4>`;
    html += `<table><thead><tr>`;
    const days = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];
    for (let d of days) html += `<th>${d}</th>`;
    html += `</tr></thead><tbody><tr>`;

    for (let i = 0; i < firstDay.getDay(); i++) html += `<td></td>`;
    for (let day = 1; day <= lastDay.getDate(); day++) {
        const date = new Date(year, month, day);
        const isToday = (day === today.getDate());
        html += `<td class="${isToday ? 'today' : ''}">${day}</td>`;
        if (date.getDay() === 6 && day < lastDay.getDate()) html += `</tr><tr>`;
    }

    html += `</tr></tbody></table>`;
    document.getElementById('calendar').innerHTML = html;
}
generateCalendar();
</script>
</body>
</html>

