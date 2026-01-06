<?php
require_once("dbConnect.php");
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: welcome.php"); // Redirect to login page if not logged in
    exit;
}

$username = $_SESSION['username'];

// Fetch the student's full name
$sql = "SELECT CLERKNAME FROM clerk WHERE CLERKID = ?";
$stmt = $dbCon->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($fullName);
$stmt->fetch();
$stmt->close();

$firstName = strtoupper(strtok($fullName, ' '));

if(isset($_GET['id'])){
    $STUID = $_GET['id'];
    $sql = "SELECT * FROM student WHERE STUID = '$STUID'";
    $result = $dbCon->query($sql);
    $row = $result->fetch_assoc();
    $STUID = $row['STUID'];
    $STUNAME = $row['STUNAME'];
    $STUEMAIL = $row['STUEMAIL'];
    $STUPNO = $row['STUPNO'];
    $STUDOB = $row['STUDOB'];
    $STUGENDER = $row['STUGENDER'];
    $FATHERNAME = $row['FATHERNAME'];
    $MOTHERNAME = $row['MOTHERNAME'];
    $SALARY = $row['SALARY'];
    $STUDENTIMAGE = $row['STUIMAGE'];

} else{
    echo "<script>alert('No student ID is set.');</script>";
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
    <title>View Student</title>
</head>
<style>
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
	background: #BF612D;
	color: white;
}
.welcome-name {
	font-size: 25px;
	margin-left: 40px;
}
.header i {
	font-size: 30px;
	cursor: pointer;
	color: #black;
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
    background: #48332E;
    font-weight: bold;
}
.side-bar ul li:hover > ul {
    display: block; /* Display submenu on hover */
}
.side-bar ul li a {
    text-decoration: none;
    color: white;
    cursor: pointer;
    letter-spacing: 1px;
}
.side-bar  i {
    display: inline-block;
    padding-right: 10px;
    width: 30px;
    vertical-align: center;
    font-size: 25px;
    color: white;
}
.side-bar ul li a {
    text-decoration: none;
    color: #black;
    cursor: pointer;
    letter-spacing: 1px;
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
.profile-wrap {
    width: 80%;
    background-color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column; /* Added to stack items vertically */
    padding: 20px; /* Added padding for spacing */
    margin-top: 60px;
    position: relative;
}

.back-button {
    background: none;
    border: none;
    position: absolute;
    top: 20px; /* Adjust top position */
    left: 40px; /* Adjust left position */
    font-size: 30px;
    color: black;
}
.back-button a:hover{
    color: grey;
}
.image-frame {
    width: 200px;
    height: 200px;
    overflow: hidden;
    border-radius: 5%;
    margin-bottom: 20px;
    border: 2px solid #BF612D;
    display: flex;
    margin-top: 20px;
}
.image-frame img {
    width: 100%;
    height: auto;
}
.table-class {
    margin-top: 20px;
    width: 80%;
    border-collapse: collapse; /* Ensure borders collapse properly */
}

.table-class th {
    font-size: 20px;
    background-color: #A39F9F;
    padding: 10px;
    border-bottom: 1px solid #ccc; /* Add bottom border to table headers */
}

.table-class td {
    padding: 13px 15px;
    font-size: 18px;
    border-bottom: 1px solid #ccc; /* Add bottom border to table cells */
}

.table-class tr {
    border-bottom: 1px solid #ccc; /* Add bottom border to all table rows */
}

.table-class td:first-child {
    width: 300px; /* Adjust the width of the first column */
}
#printbtn {
    background-color: #BF612D;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    margin-top: 20px;
    cursor: pointer;
}
#printbtn:hover {
    background-color: #48332E;
}
</style>
<body>
    <input type="checkbox" id="checkbox">
    <div class="header">
        <label for="checkbox">
            <i id="navbtn" class="fa fa-bars" aria-hidden="true"></i>
        </label>
        <h2 class="welcome-name">Welcome <span style="color: #48332E;"><?php echo htmlspecialchars($firstName); ?></span> !</h2>
        <div class="right-icon">
            <a href="ClerkProfile.php">
                <i class="fa fa-user" aria-hidden="true"></i>
            </a>
        </div>
    </div>
    <div class="body">
        <nav class="side-bar">
            <div class="user-p">
                <img src="logo.png" alt=""/>
            </div>
            <ul>
                <li >
                    <a href="ClerkDashboard.php">
                        <i class="fa fa-home" aria-hidden="true"></i>
                        <span style="padding-left:10px;">DASHBOARD</span>
                    </a>
                </li>
                <li>
                    <a href="studentprofile.php">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        <span style="padding-left:10px;">PROFILE</span>
                    </a>
                </li>
                <li>
                    <a href="listofstudent.php">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        <span style="padding-left:10px;">LIST OF STUDENT</span>
                    </a>
                </li>
                <li style="border-bottom: 1px solid grey;">
                    <a href="logout.php">
                        <i class="fa fa-sign-out" aria-hidden="true"></i>
                        <span style="padding-left:10px;">LOGOUT</span>
                    </a>
                </li>
            </ul>
        </nav>
        <section class="section-1">
            <div class="profile-wrap">
                <button class="back-button"><a href="listofstudent.php"><i class="fa fa-arrow-left" aria-hidden="true"></i></a></button>
                <div class="image-frame">
                <img src="<?php echo !empty($STUDENTIMAGE) ? 'STUDENT/' . htmlspecialchars($STUDENTIMAGE) : 'default-profile.png'; ?>" alt="Profile Picture">
                </div>
                <table class="table-class">
                    <tr>
                        <th colspan="2">STUDENT 'S INFORMATION</th>
                    </tr>
                    <tr>
                        <td><b>Student ID</b></td>
                        <td><?= isset($STUID) ? $STUID : 'NULL'?></td>
                    </tr>
                    <tr>
                        <td><b>Name</b></td>
                        <td><?= isset($STUNAME) ? $STUNAME : 'NULL'?></td>
                    </tr>
                    <tr>
                        <td><b>Email</b></td>
                        <td><?= isset($STUEMAIL) ? $STUEMAIL : 'NULL'?></td>
                    </tr>
                    <tr>
                        <td><b>Date of Birth</b></td>
                        <td><?= isset($STUDOB) ? $STUDOB : 'NULL'?></td>
                    </tr>
                    <tr>
                        <td><b>Phone Number</b></td>
                        <td><?= isset($STUPNO) ? $STUPNO : 'NULL'?></td>
                    </tr>
                    <tr>
                        <td><b>Gender</b></td>
                        <td><?= isset($STUGENDER) ? $STUGENDER : 'NULL'?></td>
                    </tr>
                    <tr>
                        <th colspan="2">PARENT 'S INFORMATION</th>
                    </tr>
                    <tr>
                        <td><b>Father Name</b></td>
                        <td><?= isset($FATHERNAME) ? $FATHERNAME : 'NULL'?></td>
                    </tr>
                    <tr>
                        <td><b>Mother Name</b></td>
                        <td><?= isset($MOTHERNAME) ? $MOTHERNAME : 'NULL'?></td>
                    </tr>
                    <tr>
                        <td><b>Salary</b></td>
                        <td><?= isset($SALARY) ? "RM ".$SALARY : 'NULL'?></td>
                    </tr>
                </table>
                <a id="printbtn" href="printStudentReport.php?id=<?= $STUID; ?>">PRINT <i class='fa fa-print' aria-hidden='true'></i></a>
            </div>
        </section>
    </div>
</body>
</html>

