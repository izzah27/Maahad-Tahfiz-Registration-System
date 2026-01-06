<?php
require_once("../dbConnect.php");
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../welcome.php"); // Redirect to login page if not logged in
    exit;
}

$username = $_SESSION['username'];

// Fetch the admin's full name
$sql = "SELECT CLERKNAME FROM clerk WHERE CLERKID = ?";
$stmt = $dbCon->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($fullName);
$stmt->fetch();
$stmt->close();

$firstName = strtoupper(strtok($fullName, ' '));

// Fetch the total number of clerks
$sql = "SELECT COUNT(*) FROM clerk WHERE STATUS = 'active'";
$result = $dbCon->query($sql);
$totalClerks = $result->fetch_row()[0];

// Fetch the total number of students
$sql = "SELECT COUNT(*) FROM student WHERE STATUS='active'";
$result = $dbCon->query($sql);
$totalStudents = $result->fetch_row()[0];

// Fetch the total number of principals
$sql = "SELECT COUNT(*) FROM principal";
$result = $dbCon->query($sql);
$totalPrincipals = $result->fetch_row()[0];

// Fetch the total number of users registered today
$sql = "SELECT COUNT(*) FROM registration WHERE DATE(regdate) = CURDATE()";
$result = $dbCon->query($sql);
$newRegistrationsToday = $result->fetch_row()[0];

$totalUsers = $totalPrincipals + $totalStudents + $totalClerks;

$sql = "SELECT COUNT(*) AS count, DATE_FORMAT(regdate, '%Y-%m') AS month FROM registration GROUP BY month ORDER BY month";
$result = $dbCon->query($sql);

$months = [];
$counts = [];

while ($row = $result->fetch_assoc()) {
    $months[] = $row['month'];
    $counts[] = $row['count'];
}

$dbCon->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <title>Admin Dashboard</title>
</head>
<style>
/* Existing styles */
* {
    padding: 0;
    margin: 0;
    box-sizing: border-box;
    font-family: arial, sans-serif;
}
.header {
    display: flex;
    align-items: center;
    padding: 28px 30px;
    background: #EFD577;
    color: white;
}
.welcome-name {
    font-size: 25px;
    margin-left: 40px;
}
.header i {
    font-size: 30px;
    cursor: pointer;
    color: black;
}
.header a{
    text-decoration: none;
    color: black;
}
.header i:hover {
    color: #127b8e;
}
.right-icon {
    margin-left: auto;
}
.right-icon i {
    margin-right: 15px; 
}
.right-icon i:last-child {
    margin-right: 0; 
}
.user-p {
    text-align: center;
    padding-top: 50px;
}
.user-p img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
}
.body {
    display: flex;
}
.side-bar {
    width: 350px;
    background: #AFAA79;
    min-height: 100vh;
    transition: 500ms width;
}
.side-bar ul {
    margin-top: 40px;
    list-style: none;
    border-top: 2px solid white;
}
.side-bar ul li {
    font-size: 20px;
    padding: 25px 0px;
    padding-left: 25px;
    transition: background 500ms;  /* Fixed transition property */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    border: none;
    border-top: 1px solid grey;  /* Fixed border syntax */
}
.side-bar ul li:hover {
    background: #EFD577;
    font-weight: bold;
}
.side-bar ul li a {
    text-decoration: none;
    color: white;
    cursor: pointer;
    letter-spacing: 1px;
}
.side-bar i {
    display: inline-block;
    padding-right: 10px;
    width: 30px;
    vertical-align: center;
    font-size: 25px;
    color: white;
}
.side-bar ul li a i {
    display: inline-block;
    padding-right: 10px;
    font-size: 30px;
}
.section-1 {
    width: 100%;
    background-color: #F5EFE7;
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: center;
    flex-direction: column;
}
#navbtn {
    display: inline-block;
    left: 20px;
    font-size: 20px;
    transition: 500ms color;
    color: #black;
}
#checkbox {
    display: none;
}
#checkbox:checked ~ .body .side-bar {
    width: 80px;
}
#checkbox:checked ~ .body .side-bar .user-p{
    visibility: hidden;
}
#checkbox:checked ~ .body .side-bar a span{
    display: none;
}
.circled-menu-parent{
    background-color: #f7f7f7;
    height: 60px;
    font-size: 25px;
    font-family: 'Inter';
    font-weight: bold;
    color: #434343;
    width: 100%;
    display: flex;
    justify-content: center;
    flex-direction: column;
    padding: 0 20px;
    font-family: "Poppins", sans-serif;
    border-bottom: 1px solid #ccc;
}
.circled-menu-parent p{
    margin: 0; /* Remove default margins to prevent alignment issues */
    display: flex; /* Use flexbox to align the icon and text */
    align-items: center; /* Vertically center the icon and text */
    font-size: 25px;
    font-family: "Poppins", sans-serif;
}
.circled-menu-parent i {
    margin-left: 20px;
    margin-right: 15px; /* Space between the icon and text */
}
h2{
    margin-top: 20px;
    width: 92%;
    padding: 10px;
}
/* New styles */
.stats-container {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    margin-top: 20px;
    gap: 80px;
}
.stat-box {
    background: white;
    width: 250px;
    height: 170px;
    border-radius: 10px;
    padding: 20px 30px;
    text-align: left;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    margin: 10px;
    text-align: center;
}
.stat-box h3 {
    font-size: 20px;
    margin-bottom: 10px;
    color: grey;
}
.stat-box p {
    font-size: 35px;
    font-weight: bold;
}
.stat-box i{
    font-size: 40px;
    margin-bottom: 20px;
}
.graph{
    width: 600px;
    background-color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column; /* Stacks children vertically */
    text-align: center;
    padding: 20px;
    border-radius: 10px;
}
h4{
    font-size: 25px;
}
</style>
<body>
    <input type="checkbox" id="checkbox">
    <div class="header">
        <label for="checkbox">
            <i id="navbtn" class="fa fa-bars" aria-hidden="true"></i>
        </label>
    </div>
    <div class="body">
        <nav class="side-bar">
            <div class="user-p">
                <img src="../logo.png" alt=""/>
            </div>
            <ul>
                <li>
                    <a href="AdminDashboard.php">
                        <i class="fa fa-home" aria-hidden="true"></i>
                        <span>DASHBOARD</span>
                    </a>
                </li>
                <li>
                    <a href="adminprofile.php">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        <span>PROFILE</span>
                    </a>
                </li>
                <li style="border-bottom: 1px solid grey;">
                    <a href="listofclerk.php">
                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                        <span>LIST OF CLERK</span>
                    </a>
                </li>
                <li style="border-bottom: 1px solid grey;">
                    <a href="../logout.php">
                        <i class="fa fa-sign-out" aria-hidden="true"></i>
                        <span>LOGOUT</span>
                    </a>
                </li>
            </ul>
        </nav>
        <section class="section-1">
            <div class="circled-menu-parent">
                <p><i class="fa fa-th-large" style="font-size:25px;"></i>Dashboard</p>
            </div>
            <h2>Welcome back, Admin <span style="color: #48332E;"><?php echo htmlspecialchars($firstName); ?></span>!</h2>
            <div class="stats-container">
                <div class="stat-box">
                    <span style="color: #252C65;"><i class="fa fa-users" aria-hidden="true"></i></span>
                    <h3>Total Users</h3>
                    <p><?php echo htmlspecialchars($totalUsers); ?></p> <!-- Example value, fetch from the database -->
                </div>
                <div class="stat-box">
                    <span style="color: #DAB01C;"><i class="fa fa-building-o" aria-hidden="true"></i></span>
                    <h3>Total Clerk</h3>
                    <p><?php echo htmlspecialchars($totalClerks); ?></p> <!-- Example value, fetch from the database -->
                </div>
                <div class="stat-box">
                <span style="color: #B10DAB;"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></span>
                    <h3>New Registrations</h3>
                    <p><?php echo htmlspecialchars($newRegistrationsToday); ?></p> <!-- Example value, fetch from the database -->
                </div>
                <div class="graph">
                    <h4 style="margin-top: 40px;">Monthly Registrations</h4>
                    <canvas id="registrationChart" width="400" height="200"></canvas>
                </div>
                
            </div>
                
        </section>
    </div>
    <script>
const ctx = document.getElementById('registrationChart').getContext('2d');
const registrationChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>, // Month labels
        datasets: [{
            label: 'Registrations',
            data: <?php echo json_encode($counts); ?>, // Registration counts
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderWidth: 2,
            fill: true,
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
</body>
</html>
