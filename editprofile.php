<?php
session_start();
require_once("dbConnect.php");

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: welcome.php"); // Redirect to login page if not logged in
    exit;
}

// Fetch current student data from the database
$studentInfo = [];
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    
    $sql = "SELECT * FROM STUDENT WHERE STUID = ?";
    $stmt = $dbCon->prepare($sql);
    if ($stmt === false) {
        die("Error preparing SQL: " . $dbCon->error);
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $studentInfo = $result->fetch_assoc();
    } else {
        echo "<script>alert('Student data not found.');</script>";
        exit;
    }

    $sql = "SELECT STUNAME FROM student WHERE STUID = ?";
    $stmt = $dbCon->prepare($sql);
    if ($stmt === false) {
        die("Error preparing SQL: " . $dbCon->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($fullName);
    $stmt->fetch();

    // Split the full name to get the first name and convert to uppercase
    $firstName = strtoupper(strtok($fullName, ' '));
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $STUNAME = $_POST['STUNAME'];
    $STUPNO = $_POST['STUPNO'];
    $STUEMAIL = $_POST['STUEMAIL'];
    $STUDOB = $_POST['STUDOB'];
    $STUADDRESS = $_POST['STUADDRESS'];
    $STUGENDER = $_POST['STUGENDER'];
    $FATHERNAME = $_POST['FATHERNAME'];
    $MOTHERNAME = $_POST['MOTHERNAME'];
    $SALARY = $_POST['SALARY'];
    $newProfileImage = $_FILES['STUIMAGE']['name'];

    // Image upload handling
    $target_dir = "STUDENT/";
    $target_file = $target_dir . basename($_FILES["STUIMAGE"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image or fake image
    if ($newProfileImage) {
        $check = getimagesize($_FILES["STUIMAGE"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["STUIMAGE"]["size"] > 500000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        } else {
            if (move_uploaded_file($_FILES["STUIMAGE"]["tmp_name"], $target_file)) {
                echo "The file " . htmlspecialchars(basename($_FILES["STUIMAGE"]["name"])) . " has been uploaded.";
                // Update the database with the new image path
                $stmt = $dbCon->prepare("UPDATE STUDENT SET STUIMAGE = ? WHERE STUID = ?");
                if ($stmt === false) {
                    die("Error preparing SQL: " . $dbCon->error);
                }
                $stmt->bind_param("ss", basename($_FILES["STUIMAGE"]["name"]), $username);
                if ($stmt->execute()) {
                    echo "Database updated with new profile image.";
                } else {
                    echo "Error updating database: " . $stmt->error;
                }
                $stmt->close();
                // No need to set $STUIMAGE here as it is not used further in the code
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    // Update user data
    if ($newProfileImage && $uploadOk) {
        // Update user data including new profile image
        $updateStmt = $dbCon->prepare("UPDATE STUDENT SET STUNAME = ?, STUPNO = ?, STUEMAIL = ?, STUDOB = ?, STUADDRESS = ?, STUGENDER = ?, FATHERNAME = ?, MOTHERNAME = ?, SALARY = ?, STUIMAGE = ? WHERE STUID = ?");
        if ($updateStmt === false) {
            die("Prepare failed: " . $dbCon->error);
        }
        $updateStmt->bind_param('sssssssssss', $STUNAME, $STUPNO, $STUEMAIL, $STUDOB, $STUADDRESS, $STUGENDER, $FATHERNAME, $MOTHERNAME, $SALARY, basename($_FILES["STUIMAGE"]["name"]), $username);
    } else {
        // Update user data without changing profile image
        $updateStmt = $dbCon->prepare("UPDATE STUDENT SET STUNAME = ?, STUPNO = ?, STUEMAIL = ?, STUDOB = ?, STUADDRESS = ?, STUGENDER = ?, FATHERNAME = ?, MOTHERNAME = ?, SALARY = ? WHERE STUID = ?");
        if ($updateStmt === false) {
            die("Prepare failed: " . $dbCon->error);
        }
        $updateStmt->bind_param('ssssssssss', $STUNAME, $STUPNO, $STUEMAIL, $STUDOB, $STUADDRESS, $STUGENDER, $FATHERNAME, $MOTHERNAME, $SALARY, $username);
    }

    if (!$updateStmt->execute()) {
        die("Execute failed: " . $updateStmt->error);
    }

    $updateStmt->close();
    header("Location: studentprofile.php");
    exit;
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
    <title>Edit Profile</title>
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
    color: black;
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
    color: black;
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
.profile-wrapper{
    width: 800px;
    height: auto;
    background: #9e9696d4;
    margin-top: 100px;
}
.profile-wrapper h2{
    text-align: center;
    font-size: 25px;
    padding: 15px;
    border: 1px solid grey;
    font-family:"Inter";
}
.profile-wrapper p{
    padding: 10px;
    font-size: 20px;
    margin-left: 40px;
    font-family:"Inter";
}
.rectangle, .rectangle2{
    width: 100%;
    height: 10px;
    background-color: black;
}
.rectangle2{
    margin-top: 20px;
}
table{
    width: 100%;
}
.link a{
    padding: 10px 30px;
    background: #F5C826;
    border: none;
    border-radius: 10px;
    color:white;
    font-family:"arial";
    font-size: 17px;
    text-decoration: none;
    font-weight: bold;
    display: flex;
    margin-top: 40px;
}
form {
    display: flex;
    flex-direction: column;
    margin-bottom: 20px;
    position: relative;
}
form tr{
    width: 100%;
}
form  th{
    text-align: left;
    padding-top: 10px;
    padding-left: 10px;
    padding-bottom: 5px;
    font-size: 18px;
}
form td input[type="text"], form tr td input[type="date"]  {
    padding: 7px;
    width: 90%;
    margin-left: 10px;
    border: none;
    border-radius: 5px;
    outline: none;
    font-size: 17px;
    cursor: pointer;
}
form tr td input[type="file"]
{
    padding-left: 7px;
    border-radius: 5px;
}
form td input[type="radio"] {
    margin-left: 10px;
    cursor: pointer;
}
form td label {
    margin-left: 5px;
    font-size: 17px;
}
input[type="submit"] {
    background-color: #8EC910;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: bold;
    padding: 15px;
    width: 150px;
    border-radius: 30px;
    margin: 30px auto;
    font-weight: bold;
    font-family: "Arial";
    font-size: 20px;
    position: absolute;
    margin-top: 65%;
    margin-left: 40%;
}
input[type="submit"]:hover {
    background-color: #e0b022;
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
                <li>
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
            <div class="profile-wrapper">
                <h2>PERSONAL INFORMATION</h2>
                <div class="rectangle"></div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                    <table >
                        <tr>
                            <th>Full Name</th>
                            <th>Date of Birth</th>
                        </tr>
                        <tr>
                            <td><input type="text" name="STUNAME" id="STUNAME" value="<?php echo $studentInfo['STUNAME']; ?>" required></td>
                            <td><input type="date" name="STUDOB" id="STUDOB" value="<?php echo $studentInfo['STUDOB']; ?>" required></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <th>Phone Number</th>
                        </tr>
                        <tr>
                            <td><input type="text" name="STUEMAIL" id="STUEMAIL" value="<?php echo $studentInfo['STUEMAIL']; ?>" required></td>
                            <td><input type="text" name="STUPNO" id="STUPNO" value="<?php echo $studentInfo['STUPNO']; ?>" required></td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <th>Gender</th>
                        </tr>
                        <tr>
                            <td><input type="text" name="STUADDRESS" id="STUADDRESS" value="<?php echo $studentInfo['STUADDRESS']; ?>" required></td>
                            <td>
                                <input type="radio" name="STUGENDER" id="STUGENDER_MALE" value="Male"><label for="STUGENDER_MALE">Male</label>
                                <input type="radio" name="STUGENDER" id="STUGENDER_FEMALE" value="Female"> <label for="STUGENDER_FEMALE">Female</label>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="rectangle2"></div>
                                <h2>PARENTS INFORMATION</h2>
                                <div class="rectangle"></div>
                            </td>
                        </tr>
                        <tr>
                            <th>Father's Name</th>
                            <th>Mother's Name</th>
                        </tr>
                        <tr>
                            <td><input type="text" name="FATHERNAME" id="FATHERNAME" value="<?php echo $studentInfo['FATHERNAME']; ?>" required></td>
                            <td><input type="text" name="MOTHERNAME" id="MOTHERNAME" value="<?php echo $studentInfo['MOTHERNAME']; ?>" required></td>
                        </tr>
                        <tr>
                            <th>Salary (RM)</th>
                            <th>Profile Image</th>
                        </tr>
                        <tr>
                            <td><input type="text" name="SALARY" id="SALARY" value="<?php echo $studentInfo['SALARY']; ?>" required></td>
                            <td><input type="file" name="STUIMAGE" id="STUIMAGE" accept="image/*"></td>
                        </tr>
                    </table>
                    <input type="submit" value="Update">
                </form>
            </div>
        </section>
    </div>
</body>
</html>
