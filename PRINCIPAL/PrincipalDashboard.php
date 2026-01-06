<?php
require_once("../dbConnect.php");

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../welcome.php"); // Redirect to login page if not logged in
    exit;
}

// Query to get total count of clerks
$stmtClerkCount = $dbCon->prepare("SELECT COUNT(*) AS total_clerks FROM clerk");
$stmtClerkCount->execute();
$stmtClerkCount->bind_result($totalClerks);
$stmtClerkCount->fetch();
$stmtClerkCount->close();

// Query to get total count of students
$stmtStudentCount = $dbCon->prepare("SELECT COUNT(*) AS total_students FROM student");
$stmtStudentCount->execute();
$stmtStudentCount->bind_result($totalStudents);
$stmtStudentCount->fetch();
$stmtStudentCount->close();

// Query to get total count of unapproved students where status is 'pending'
$stmtUnapprovedCount = $dbCon->prepare("
    SELECT COUNT(*) AS total_unapproved 
    FROM student s 
    JOIN registration r ON s.stuid = r.stuid 
    WHERE r.status = 'pending'
");
$stmtUnapprovedCount->execute();
$stmtUnapprovedCount->bind_result($totalUnapproved);
$stmtUnapprovedCount->fetch();
$stmtUnapprovedCount->close();

// Query to get total count of approved students where status is 'approved'
$stmtApprovedCount = $dbCon->prepare("
    SELECT COUNT(*) AS total_approved 
    FROM student s 
    JOIN registration r ON s.stuid = r.stuid 
    WHERE r.status = 'Approved'
");
$stmtApprovedCount->execute();
$stmtApprovedCount->bind_result($totalApproved);
$stmtApprovedCount->fetch();
$stmtApprovedCount->close();

// Query to get gender distribution of students, excluding null values
$stmtGenderDistribution = $dbCon->prepare("SELECT stugender AS gender, COUNT(*) as count FROM student WHERE stugender IN ('Male', 'Female') GROUP BY stugender");
$stmtGenderDistribution->execute();
$resultGenderDistribution = $stmtGenderDistribution->get_result();
$genderData = [];
while ($row = $resultGenderDistribution->fetch_assoc()) {
    $genderData[] = $row;
}
$stmtGenderDistribution->close();


// Query to get number of student registrations per year
$stmtRegistrationPerYear = $dbCon->prepare("
    SELECT YEAR(regdate) as year, COUNT(*) as count 
    FROM registration 
    GROUP BY YEAR(regdate) 
    ORDER BY YEAR(regdate)
");
$stmtRegistrationPerYear->execute();
$resultRegistrationPerYear = $stmtRegistrationPerYear->get_result();
$registrationData = [];
while ($row = $resultRegistrationPerYear->fetch_assoc()) {
    $registrationData[] = $row;
}
$stmtRegistrationPerYear->close();

$dbCon->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@600&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Arial:wght@400;700&display=swap" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0/dist/chartjs-plugin-datalabels.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Arial:wght@700&display=swap" />
</head>
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

:root{
    /* ===== Colors ===== */
    --body-color: #ECE8C6;
    --sidebar-color: #FFF;
    --primary-color: #9F9A6A;
    --primary-color-light: #F6F5FF;
    --toggle-color: #DDD;
    --text-color: #707070;

    /* ====== Transition ====== */
    --tran-03: all 0.2s ease;
    --tran-03: all 0.3s ease;
    --tran-04: all 0.3s ease;
    --tran-05: all 0.3s ease;
}

body{
    min-height: 100vh;
    background-color: var(--body-color);
    transition: var(--tran-05);
}

::selection{
    background-color: var(--primary-color);
    color: #fff;
}

body.dark{
    --body-color: #18191a;
    --sidebar-color: #242526;
    --primary-color: #3a3b3c;
    --primary-color-light: #3a3b3c;
    --toggle-color: #fff;
    --text-color: #634711;
}

/* ===== Sidebar ===== */
 .sidebar{
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    width: 250px;
    padding: 10px 14px;
    background: var(--sidebar-color);
    transition: var(--tran-05);
    z-index: 100;  
}
.sidebar.close{
    width: 88px;
}

/* ===== Reusable code - Here ===== */
.sidebar li{
    height: 50px;
    list-style: none;
    display: flex;
    align-items: center;
    margin-top: 10px;
}

.sidebar header .image,
.sidebar .icon{
    min-width: 60px;
    border-radius: 6px;
}

.sidebar .icon{
    min-width: 60px;
    border-radius: 6px;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.sidebar .text,
.sidebar .icon{
    color: var(--text-color);
    transition: var(--tran-03);
}

.sidebar .text{
    font-size: 17px;
    font-weight: 500;
    white-space: nowrap;
    opacity: 1;

}
.sidebar.close .text{
    opacity: 0;
}
/* =========================== */

.sidebar header{
    position: relative;
}
.sidebar header .logo-text{
    display: flex;
    flex-direction: column;
    text-align: center;
    margin-top: 10px;
}
header .name {
    margin-top: 2px;
    font-size: 18px;
    font-weight: 600;
}

header .image-text .profession{
    font-size: 16px;
    margin-top: -2px;
    display: block;
}

.sidebar header .image{
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 40px;
}

.sidebar header .image img{
    width: 70px;
    border-radius: 6px;
}

.sidebar header .toggle{
    position: absolute;
    top: 50%;
    right: -25px;
    transform: translateY(-50%) rotate(180deg);
    height: 25px;
    width: 25px;
    background-color: var(--primary-color);
    color: var(--sidebar-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    cursor: pointer;
    transition: var(--tran-05);
}

body.dark .sidebar header .toggle{
    color: var(--text-color);
}

.sidebar.close .toggle{
    transform: translateY(-50%) rotate(0deg);
}

.sidebar .menu{
    margin-top: 20px;
}

.sidebar li.search-box{
    border-radius: 6px;
    background-color: var(--primary-color-light);
    cursor: pointer;
    transition: var(--tran-05);
}

.sidebar li.search-box input{
    height: 100%;
    width: 100%;
    outline: none;
    border: none;
    background-color: var(--primary-color-light);
    color: var(--text-color);
    border-radius: 6px;
    font-size: 17px;
    font-weight: 500;
    transition: var(--tran-05);
}
.sidebar li a{
    list-style: none;
    height: 100%;
    background-color: transparent;
    display: flex;
    align-items: center;
    height: 100%;
    width: 100%;
    border-radius: 6px;
    text-decoration: none;
    transition: var(--tran-03);
}

.sidebar li a:hover{
    background-color: var(--primary-color);
}
.sidebar li a:hover .icon,
.sidebar li a:hover .text{
    color: var(--sidebar-color);
}
body.dark .sidebar li a:hover .icon,
body.dark .sidebar li a:hover .text{
    color: var(--text-color);
}

.sidebar .menu-bar{
    height: calc(100% - 55px);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow-y: scroll;
}
.menu-bar::-webkit-scrollbar{
    display: none;
}
.sidebar .menu-bar .mode{
    border-radius: 6px;
    background-color: var(--primary-color-light);
    position: relative;
    transition: var(--tran-05);
}

.menu-bar .mode .sun-moon{
    height: 50px;
    width: 60px;
}

.mode .sun-moon i{
    position: absolute;
}
.mode .sun-moon i.sun{
    opacity: 0;
}
body.dark .mode .sun-moon i.sun{
    opacity: 1;
}
body.dark .mode .sun-moon i.moon{
    opacity: 0;
}

.menu-bar .bottom-content .toggle-switch{
    position: absolute;
    right: 0;
    min-width: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    cursor: pointer;
    top: -100px;
}
.toggle-switch .switch{
    position: relative;
    height: 22px;
    width: 40px;
    border-radius: 25px;
    background-color: var(--toggle-color);
    transition: var(--tran-05);
}

.switch::before{
    content: '';
    position: absolute;
    height: 15px;
    width: 15px;
    border-radius: 50%;
    top: 50%;
    left: 5px;
    transform: translateY(-50%);
    background-color: var(--sidebar-color);
    transition: var(--tran-04);
}

body.dark .switch::before{
    left: 20px;
}

.home{
    position: absolute;
    top: 0;
    top: 0;
    left: 250px;
    height: 100vh;
    width: calc(100% - 250px);
    background-color: var(--body-color);
    transition: var(--tran-05);
}
.dashboard-wrapper{
    font-size: 30px;
    font-weight: 500;
    color: var(--text-color);
    padding: 12px 40px;
    background: white;
    margin-top: 20px;
    margin-left: 20px;
    border-top-left-radius: 20px;
    border-bottom-left-radius: 20px;
}

.sidebar.close ~ .home{
    left: 78px;
    height: 100vh;
    width: calc(100% - 78px);
}
body.dark .home .text{
    color: var(--text-color);
}
.content-in {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 40px;
}

.content-in .content-in-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.content-in .content-in-pie {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.show-box1,
.show-box2,
.show-box3,
.show-box4, 
.show-box5,
.show-box6  {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    background-color: white;
    width: 300px;
    padding: 20px;
    text-align: center;
    border: 1px solid #ccc;
    border-radius: 10px;
}

.icon-class1,
.icon-class2,
.icon-class3,
.icon-class4,
.icon-class5,
.icon-class6{
    position: absolute;
    width: 60px;
    height: 60px;
    justify-content: center;
    align-items: center;
    margin-top: -80px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    color: white;
    font-size: 25px;
}

.icon-class1 {
    background: #4caf50;
}

.icon-class2 {
    background: #0492c2;
}

.icon-class3 {
    background: #f09c48;
}

.icon-class4 {
    background: #ffea17;
}

.icon-class5 {
    background: #ffea18;
}

.icon-class6 {
    background: #ffea31;
}


.content-in-pie {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 40px;
}

.content-in-pie .show-box4 {
    width: 400px;
    padding: 20px;
    text-align: center;
    border: 1px solid #ccc;
    border-radius: 10px;
}

</style>
<body>
    <nav class="sidebar close">
        <header>
            <span class="image">
                <img src="../ClerkImage/chibi.jpg" alt="">
            </span>
            <div class="text logo-text">
                <span class="name">Welcome!</span>
                <span class="profession">Principal</span>
            </div>
            <i class='bx bx-chevron-right toggle'></i>
        </header>

        <div class="menu-bar">
            <div class="menu">
                <ul class="menu-links">
                    <li class="nav-link">
                        <a href="../PRINCIPAL/PrincipalDashboard.php">
                            <i class='bx bx-home-alt icon' ></i>
                            <span class="text nav-text">Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-link">
                        <a href="../PRINCIPAL/report.php">
                            <i class='bx bx-bar-chart-alt-2 icon' ></i>
                            <span class="text nav-text">Student Report</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="../PRINCIPAL/clerkReport.php">
                            <i class='bx bx-bar-chart-alt-2 icon' ></i>
                            <span class="text nav-text">Clerk Report</span>
                        </a>
                    </li>
                    <li class="">
                        <a href="../logout.php">
                            <i class='bx bx-log-out icon' ></i>
                            <span class="text nav-text">Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="home">
        <div class="dashboard-wrapper">Dashboard</div>
        <div class="content-in">
            <div class="content-in-row">
                <div class="show-box1">
                    <h3>Total Clerk</h3>
                    <div class="icon-class1">
                        <span><i class="fa fa-users" aria-hidden="true"></i></span>
                    </div>
                    <p><b><?php echo $totalClerks; ?></b></p>
                </div>
                <div class="show-box2">
                    <h3>Total Student</h3>
                    <div class="icon-class2">
                        <span><i class="fa fa-graduation-cap" aria-hidden="true"></i></span>
                    </div>
                    <p><b><?php echo $totalStudents; ?></b></p>
                </div>
                <div class="show-box3">
                    <h3>Total Approved</h3>
                    <div class="icon-class3">
                        <span><i class="fas fa-calendar-check"></i></span>
                    </div>
                    <p><b><?php echo $totalApproved; ?></b></p>
                </div>
                <div class="show-box4">
                    <h3>Total Unapproved</h3>
                    <div class="icon-class4">
                        <span><i class="fa fa-spinner" aria-hidden="true"></i></span>
                    </div>
                    <p><b><?php echo $totalUnapproved; ?></b></p>
                </div>
            </div>
            <div class="content-in-pie">
                <div class="show-box5">
                    <h3>Student Gender</h3>
                    <canvas id="genderPieChart" width="400" height="400"></canvas>
                </div>
                <div class="show-box6">
                    <h3>Registration</h3>
                    <canvas id="registrationChart" width="400" height="400"></canvas>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Replace these variables with your actual data
            const genderData = <?php echo json_encode($genderData); ?>;
            const registrationData = <?php echo json_encode($registrationData); ?>;

            const genderLabels = genderData.map(item => item.gender);
            const genderCounts = genderData.map(item => item.count);

            const genderCtx = document.getElementById('genderPieChart').getContext('2d');
            new Chart(genderCtx, {
                type: 'pie',
                data: {
                    labels: genderLabels,
                    datasets: [{
                        data: genderCounts,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Student Gender Distribution'
                        },
                        datalabels: {
                            formatter: (value, context) => {
                                let sum = 0;
                                const dataArr = context.chart.data.datasets[0].data;
                                dataArr.map(data => {
                                    sum += data;
                                });
                                const percentage = (value * 100 / sum).toFixed(2) + "%";
                                return `${value} (${percentage})`;
                            },
                            color: '#000',
                            font: {
                                weight: 'bold'
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });

            const registrationLabels = registrationData.map(item => item.year);
            const registrationCounts = registrationData.map(item => item.count);

            const registrationCtx = document.getElementById('registrationChart').getContext('2d');
            new Chart(registrationCtx, {
                type: 'bar',
                data: {
                    labels: registrationLabels,
                    datasets: [{
                        label: 'Number of Students Registered per Year',
                        data: registrationCounts,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Student Registrations Over the Years'
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'end',
                            formatter: (value) => value,
                            color: '#000',
                            font: {
                                weight: 'bold'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        });
    </script>
</body>
</html>