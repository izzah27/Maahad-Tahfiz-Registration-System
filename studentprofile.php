<?php
session_start();
require_once("dbConnect.php");

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: welcome.php"); // Redirect to login page if not logged in
    exit;
}

// Fetch student data from the database
$studentInfo = [];
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    
    $sql = "SELECT * FROM STUDENT WHERE STUID = ?";
    $stmt = $dbCon->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $studentInfo = $result->fetch_assoc();
    } else {
        echo "<script>alert('Student data not found.');</script>";
    }

    $sql = "SELECT STUNAME FROM student WHERE STUID = ?";
    $stmt = $dbCon->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($fullName);
    $stmt->fetch();

    // Split the full name to get the first name and convert to uppercase
    $firstName = strtoupper(strtok($fullName, ' '));
    $stmt->close();
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@600;700&display=swap" />
    <title>User Dashboard</title>
</head>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
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
	background: #634711;
	color: #fff;
}
.welcome-name {
	font-size: 25px;
	margin-left: 40px;
}
.header i {
	font-size: 30px;
	cursor: pointer;
	color: #fff;
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
    background: white;
}
.side-bar ul li:hover > ul {
    display: block; /* Display submenu on hover */
}
.side-bar ul li a {
    text-decoration: none;
    color: black;
    cursor: pointer;
    letter-spacing: 1px;
    font-weight: bold;
}
.side-bar  i {
    display: inline-block;
    padding-right: 10px;
    width: 30px;
    vertical-align: center;
    font-size: 25px;
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
.profile-wrapper {
    width: 700px;
    height: auto;
    background: #fff;
    margin-top: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Adding a subtle shadow */
    border-radius: 10px; /* Optional: Adding rounded corners for a softer look */
    padding: 10px; /* Optional: Adding padding inside the box */
}

.profile-wrapper h2{
    text-align: center;
    font-size: 25px;
    padding: 15px;
    font-family: "Poppins", sans-serif;
    letter-spacing: 0.02em;
}
.profile-wrapper p{
    padding: 10px;
    font-size: 20px;
    margin-left: 20px;
    margin-right: 20px;
    font-family:"Inter";
}
.personal-class, .parents-class{
    margin-top: 10px;
    margin-bottom: 10px;
}
.link a {
    padding: 10px 20px;
    background: #F5C826;
    border: none;
    border-radius: 10px;
    color: white;
    font-family: "arial";
    font-size: 17px;
    text-decoration: none;
    font-weight: bold;
    transition: background 0.3s, color 0.3s; /* Add transition for smooth hover effect */
    position: absolute;
    top: 300px;
    left: 71%;

}

.link a:hover {
    background: #e0b71c; /* Change background color on hover */
    color: #fff200; /* Change text color on hover */
}

.image-wrap {
    border: 1px solid #ccc;
    margin-top: 40px;
    border-radius: 20px;
    position: relative; 
    overflow: hidden;
    border-radius: 30px; 
    box-shadow: 0 0 20px 5px rgba(139, 69, 19, 0.8); 
}

.image-wrap img {
    width: 150px;
    height: 150px;
    border-radius: 20px; /* Match border-radius of the wrap */
    display: block; /* Remove any default inline spacing */
}
.link i{
    margin-right: 5px;
}
</style>
<body>
        
    <input type="checkbox" id="checkbox">
    <div class="header">
        <label for="checkbox">
            <i id="navbtn" class="fa fa-bars" aria-hidden="true"></i>
        </label>
        <h2 class="welcome-name">Welcome <span style="color: #D1990A;"><?php echo htmlspecialchars($firstName); ?></span> !</h2>
        <div class="right-icon">
            <a href="studentprofile.php">
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
                    <a href="home.php">
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
                <li style="border-bottom: 1px solid grey;">
                    <a href="logout.php">
                        <i class="fa fa-sign-out" aria-hidden="true"></i>
                        <span style="padding-left:10px;">LOGOUT</span>
                    </a>
                </li>
                
            </ul>
        </nav>
        <section class="section-1">
            <div class="circled-menu-parent">
                <p><i class="fa fa-th-large" style="font-size:25px;"></i>Profile</p>
            </div>
            <div class="image-wrap">
                <img src="<?php echo !empty($studentInfo['STUIMAGE']) ? 'STUDENT/' . htmlspecialchars($studentInfo['STUIMAGE']) : 'default-profile.png'; ?>" alt="Profile Picture">
            </div>
            <div class="profile-wrapper">
                <h2>PERSONAL INFORMATION</h2>
                <hr>
                <div class="personal-class">
                <p><b>Full Name : </b> <span style="color: grey;"><?php echo $studentInfo['STUNAME']; ?></span></p>
                    <p><b>Phone Number : </b> <span style="color: grey;"><?php echo $studentInfo['STUPNO']; ?></span></p>
                    <p><b>Email : </b> <span style="color: grey;"><?php echo $studentInfo['STUEMAIL']; ?></span></p>
                    <p><b>Date of Birth : </b> <span style="color: grey;"><?php echo $studentInfo['STUDOB']; ?></span></p>
                    <p><b>Address : </b><span style="color: grey;"><?php echo $studentInfo['STUADDRESS']; ?></span></p>
                </div>
                <hr>
                <h2>PARENTS INFORMATION</h2>
                <hr>
                <div class="parents-class">
                    <p><b>Father's Name : </b><span style="color: grey;"><?php echo $studentInfo['FATHERNAME']; ?></span></p>
                    <p><b>Mother's Name : </b><span style="color: grey;"><?php echo $studentInfo['MOTHERNAME']; ?></span></p>
                    <p><b>Salary : </b> <span style="color: grey;">RM <?php echo number_format(!empty($studentInfo['SALARY']) ? $studentInfo['SALARY'] : 0.0, 2); ?></span></p>
                </div>
            </div>
            <div class="link">
                <a href="editprofile.php"><i class="fa fa-pencil"></i> Edit</a>
            </div>
        </section>
    </div>

</body>
</html>

