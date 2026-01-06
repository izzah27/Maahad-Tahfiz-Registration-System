<?php
require_once("dbConnect.php");
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: welcome.php"); // Redirect to login page if not logged in
    exit;
}

$username = $_SESSION['username'];

$message = "";
$STUNAME_err = $STUEMAIL_err = $STUPNO_err = $STUGENDER_err = $STUDOB_err = $STUADDRESS_err = $FATHERNAME_err= $MOTHERNAME_err = $SALARY_err = "";
$STUNAME = $STUEMAIL = $STUPNO = $STUGENDER = $STUDOB = $STUADDRESS = $FATHERNAME = $MOTHERNAME = $SALARY = "";

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
$stuID = "";
$sql = "SELECT * FROM student";
$result = $dbCon->query($sql);

if (isset($_GET['id'])) {
    $stuID = $_GET['id'];
} else {
    echo "<script>alert('Error: Student ID not set.');
    window.location.href = 'editStudent.php?id=" . $_GET['id'] . "'
    </script>";
}

$sql1 = "SELECT * FROM student WHERE STUID = ?";
$stmt1 = $dbCon->prepare($sql1);
$stmt1->bind_param("i", $stuID);
if($stmt1->execute()){
    $result1 = $stmt1->get_result();
    $row = $result1->fetch_assoc();
    $STUID = $row['STUID'];
    $STUNAME = $row['STUNAME'];
    $STUPNO = $row['STUPNO'];
    $STUEMAIL = $row['STUEMAIL'];
    $STUPASSWORD = $row['STUPASSWORD'];
    $STUGENDER = $row['STUGENDER'];
    $STUDOB = $row['STUDOB'];
    $STUADDRESS = $row['STUADDRESS'];
    $FATHERNAME = $row['FATHERNAME'];
    $MOTHERNAME = $row['MOTHERNAME'];
    $SALARY = $row['SALARY'];

    $stmt1->close();
} else{
    echo "<script>alert('Error: " . $stmt1->error . "');</script>";
}

$sql2 = "UPDATE student SET STUNAME = ?, STUPNO = ?, STUEMAIL = ?, STUPASSWORD = ?, STUGENDER = ?, STUDOB = ?, STUADDRESS = ?, FATHERNAME = ?, MOTHERNAME = ?, SALARY = ? WHERE STUID = ?";
$stmt2 = $dbCon->prepare($sql2);
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(empty(trim($_POST['STUNAME']))){
        $STUNAME_err = "Please enter student's name.";
    } else{
        $STUNAME = trim($_POST['STUNAME']);
        if(!preg_match("/^[a-zA-Z-' ]*$/", $STUNAME)){
            $STUNAME_err = "Only letters and white space allowed.";
        }
    }
    if(empty(trim($_POST['STUEMAIL']))){
        $STUEMAIL_err = "Please enter student's email.";
    } else{
        $STUEMAIL = trim($_POST['STUEMAIL']);
        if(!filter_var($STUEMAIL, FILTER_VALIDATE_EMAIL)){
            $STUEMAIL_err = "Invalid email format.";
        }
    }
    if(empty(trim($_POST['STUPNO']))){
        $STUPNO_err = "Please enter student's phone number.";
    } else{
        $STUPNO = trim($_POST['STUPNO']);
        if(!preg_match("/^\d{3}-\d{7}|\d{3}-\d{6}$/", $STUPNO)){
            $STUPNO_err = "Phone Number must be in format 'XXX-XXXXXXXX' or 'XXX-XXXXXXX'";
        }
    }
    if(empty(trim($_POST['STUADDRESS']))){
        $STUADDRESS_err = "Please enter student's address.";
    } else{
        $STUADDRESS = trim($_POST['STUADDRESS']);
    }
    if(empty(trim($_POST['FATHERNAME']))){
        $FATHERNAME_err = "Please enter father's name.";
    } else{
        $FATHERNAME = trim($_POST['FATHERNAME']);
        if(!preg_match("/^[a-zA-Z-' ]*$/", $FATHERNAME)){
            $FATHERNAME_err = "Only letters and white space allowed.";
        }
    }
    if(empty(trim($_POST['MOTHERNAME']))){
        $MOTHERNAME_err = "Please enter mother's name.";
    } else{
        $MOTHERNAME = trim($_POST['MOTHERNAME']);
        if(!preg_match("/^[a-zA-Z-' ]*$/", $MOTHERNAME)){
            $MOTHERNAME_err = "Only letters and white space allowed.";
        }
    }
    if(empty(trim($_POST['SALARY']))){
        $SALARY_err = "Please enter parent's salary.";
    } else{
        $SALARY = trim($_POST['SALARY']);
        if(!preg_match("/^\d+(\.\d+)?$/", $SALARY)){
            $SALARY_err = "Only numbers allowed.";
        }
    }
    if(empty(trim($_POST['STUGENDER']))){
        $STUGENDER_err = "Please select student gender.";
    } else{ 
        $STUGENDER = trim($_POST['STUGENDER']);
    }
    if(empty(trim($_POST['STUDOB']))){
        $STUDOB_err = "Please enter student's date of birth.";
    } else{
        $STUDOB = trim($_POST['STUDOB']);
    }

    if(empty($STUNAME_err) && empty($STUEMAIL_err) && empty($STUPNO_err) && empty($STUADDRESS_err) && empty($FATHERNAME_err) && empty($MOTHERNAME_err) && empty($SALARY_err) && empty($STUGENDER_err) && empty($STUDOB_err)){
        $stmt2->bind_param("ssssssssssi", $STUNAME, $STUPNO, $STUEMAIL, $STUPASSWORD, $STUGENDER, $STUDOB, $STUADDRESS, $FATHERNAME, $MOTHERNAME, $SALARY, $stuID);
        if($stmt2->execute()){
            echo "<script>alert('Student information updated successfully.');
            window.location.href = 'listofstudent.php';</script>";
        } else{
            echo "<script>alert('Error: " . $stmt2->error . "');</script>";
        }
    }
}
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
	color: white;
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
    position: relative;
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
.form-wrapper{
    margin-top: 60px;
    width: 80%;
    position: relative;
}
.personal-class {
    margin-top: 20px;
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 40px;
    background: white;
    overflow: hidden;
    border-radius: 20px;
    
}

.personal-class th {
    width: 100%;
    font-size: 20px;
    font-family: 'Poppins', sans-serif;
    letter-spacing: 0.05em;
    background-color: #8D3A0B;
    padding: 15px;
    border-top-left-radius: 20px; /* Corrected property */
    border-top-right-radius: 20px; /* Corrected property */
    padding-left: 20px;
    border-bottom: 1px solid #ccc; /* Add bottom border to table headers */
    text-align: left;
    overflow: hidden;
    color: white;
}

.personal-class td {
    padding: 15px;
    font-size: 18px;
    line-height: 1.5;
}
input[type="text"], input[type="date"], select {
    padding: 10px 15px;
    width: 100%;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 18px;
    margin-bottom: 5px;
}
input[type="submit"]
{
    position: absolute;
    padding: 10px 15px;
    font-size: 18px;
    background: #DCB012;
    left: 84%;
    border: 1px solid #ccc;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-weight: 700;
    border-radius: 5px;
    color: #634711;
}
input[type="submit"]:hover
{
    background-color: #C79D07;
}

.back-button {
    background: none;
    border: none;
    position: absolute;
    font-size: 25px;
    color: black;
    left: 80px;
    top: 60px;
}
.back-button a:hover{
    color: grey;
}
.error {
    color: red;
    font-weight: bold;
    width: 100%;
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
                    <a href="ClerkProfile.php">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        <span style="padding-left:10px;">PROFILE</span>
                    </a>
                </li>
                <li>
                    <a href="listofstudent.php">
                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
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
            <button class="back-button"><a href="listofstudent.php"><i class="fa fa-arrow-left" aria-hidden="true"></i></a></button>
                <form action="editStudent.php?id=<?= htmlspecialchars($_GET['id'])?>" method="POST" class="form-wrapper">
                    <table class="personal-class">
                        <tr>
                            <th colspan="2">
                                Personal Details
                            </th>
                        </tr>
                        <tr>
                            <td>
                                <b>Name <span style="color: red;">*</span></b><br>
                                <input type="text" name="STUNAME" id="STUNAME" value="<?= isset($STUNAME) ? $STUNAME : ''; ?>" placeholder="Enter Student's Name" required><br>
                                <span id="StuNameError" class="error"><?php echo $STUNAME_err?></span>
                            </td>
                            <td>
                                <b>Phone Number <span style="color: red;">*</span></b><br>
                                <input type="text" name="STUPNO" id="STUPNO" value="<?= isset($STUPNO) ? $STUPNO : ''; ?>" placeholder="Enter Student's Phone Number" required><br>
                                <span id="StuPNOError" class="error"><?php echo $STUPNO_err?></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Date of Birth <span style="color: red;">*</span></b><br>
                                <input type="date" name="STUDOB" id="STUDOB" value="<?= isset($STUDOB) ? $STUDOB : ''; ?>" placeholder="Enter Student's Date of Birth" required><br>
                                <span id="StuDOB" class="error"><?php echo $STUDOB_err?></span>
                            </td>
                            <td>
                                <b>Gender <span style="color: red;">*</span></b><br>
                                <select name="STUGENDER" id="STUGENDER" required>
                                    <option value="" disabled <?= !isset($STUGENDER) ? 'selected' : ''; ?>>Select Gender</option>
                                    <option value="Male" <?= (isset($STUGENDER) && $STUGENDER == 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?= (isset($STUGENDER) && $STUGENDER == 'Female') ? 'selected' : ''; ?>>Female</option>
                                </select>
                                <span id="StuGenderError" class="error"><?php echo $STUGENDER_err; ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Address <span style="color: red;">*</span></b><br>
                                <input type="text" name="STUADDRESS" id="STUADDRESS" value="<?= isset($STUADDRESS) ? $STUADDRESS : ''; ?>" placeholder="Enter Student's Address" required><br>
                                <span id="StuAddressError" class="error"><?php echo $STUADDRESS_err?></span>
                            </td>
                            <td>
                                <b>Email<span style="color: red;">*</span></b><br>
                                <input type="text" name="STUEMAIL" id="STUEMAIL" value="<?= isset($STUEMAIL)? $STUEMAIL: ''; ?>" placeholder="Enter Student's Email" required><br>
                                <span id="StuEmailError" class="error"><?php echo $STUEMAIL_err?></span>
                            </td>
                        </tr>
                    </table>
                    <table class="personal-class">
                        <tr>
                            <th colspan="2">
                                Parents Details
                            </th>
                        </tr>
                        <tr>
                            <td>
                                <b>Father Name <span style="color: red;">*</span></b><br>
                                <input type="text" name="FATHERNAME" id="FATHERNAME" value="<?= isset($FATHERNAME) ? $FATHERNAME : ''?>" placeholder="Enter Father's Name" required><br>
                                <span id="FatherNameError" class="error"><?php echo $FATHERNAME_err?></span>
                            </td>
                            <td>
                                <b>Mother Name <span style="color: red;">*</span></b><br>
                                <input type="text" name="MOTHERNAME" id="MOTHERNAME" value="<?= isset($MOTHERNAME) ? $MOTHERNAME : ''?>" placeholder="Enter Mother's Name" required><br>
                                <span id="MotherNameError" class="error"><?php echo $MOTHERNAME_err?></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Salary (RM)<span style="color: red;">*</span></b><br>
                                <input type="text" name="SALARY" id="SALARY" value="<?= isset($SALARY) ? $SALARY : '' ?>" placeholder="Enter Parents' Salary" required><br>
                                <span id="SalaryError" class="error"><?php echo $SALARY_err?></span>
                            </td>
                            <td>
                            </td>
                        </tr>
                    </table>
                    <input type="submit" value="Update Information">
                </form>
        </section>
    </div>
    <script>
        // Function to validate form fields on submit
        function validateForm() {
            var isValid = true;

            // Validate Student's Name
            var StuName = document.getElementById('STUNAME').value.trim();
            if (!StuName.match(/^[A-Za-z\s@'.\/]{1,255}$/)) {
                document.getElementById('StuNameError').innerHTML = 'Please enter a valid name (only letters and spaces, max 50 characters).';
                isValid = false;
            } else {
                document.getElementById('StuNameError').innerHTML = '';
            }

            // Validate Student's Phone Number
            var StuPhone = document.getElementById('STUPNO').value.trim();
            if (!StuPhone.match(/^\d{3}-\d{7}|\d{3}-\d{6}$/)) {
                document.getElementById('StuPNOError').innerHTML = 'format: XXX-XXXXXXX or XXX-XXXXXXXX.';
                isValid = false;
            } else {
                document.getElementById('StuPNOError').innerHTML = '';
            }

            // Validate Student's Address
            var StuAddress = document.getElementById('STUADDRESS').value.trim();
            if (StuAddress === '') {
                document.getElementById('StuAddressError').innerHTML = 'Please enter the student\'s address.';
                isValid = false;
            } else {
                document.getElementById('StuAddressError').innerHTML = '';
            }

            // Validate Student's Email
            var StuEmail = document.getElementById('STUEMAIL').value.trim();
            if (!StuEmail.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                document.getElementById('StuEmailError').innerHTML = 'Invalid Email.';
                isValid = false;
            } else {
                document.getElementById('StuEmailError').innerHTML = '';
            }

            // Validate Father Name
            var FatherName = document.getElementById('FATHERNAME').value.trim();
            if (!FatherName.match(/^[A-Za-z\s@'.\/]{1,255}$/)) {
                document.getElementById('FatherNameError').innerHTML = 'Name must contain only letters and spaces.';
                isValid = false;
            } else {
                document.getElementById('FatherNameError').innerHTML = '';
            }

            // Validate Mother Name
            var MotherName = document.getElementById('MOTHERNAME').value.trim();
            if (!MotherName.match(/^[A-Za-z\s@'.\/]{1,255}$/)) {
                document.getElementById('MotherNameError').innerHTML = 'Name must contain only letters and spaces.';
                isValid = false;
            } else {
                document.getElementById('MotherNameError').innerHTML = '';
            }

            // Validate Salary
            function validateWeight() {
                var salary = document.getElementById('SALARY').value.trim();
                if (!salary.match(/^\d+(\.\d{1})?$/) || parseFloat(salary) <= 0) {
                    document.getElementById('SalaryError').innerHTML = 'Please enter a valid salary (> 0).';
                    return false;
                } else {
                    document.getElementById('SalaryError').innerHTML = '';
                    return true;
                }
            }

            return isValid;
        }

        // Real-time validation on input change
        document.getElementById('STUNAME').addEventListener('input', function() {
            var StuName = this.value.trim();
            if (!StuName.match(/^[A-Za-z\s@'.\/]{1,255}$/)) {
                document.getElementById('StuNameError').innerHTML = 'Please enter a valid name (only letters and spaces).';
            } else {
                document.getElementById('StuNameError').innerHTML = '';
            }
        });

        document.getElementById('STUPNO').addEventListener('input', function() {
            var StuPNO = this.value.trim();
            if (!StuPNO.match(/^\d{3}-\d{7}|\d{3}-\d{6}$/)) {
                document.getElementById('StuPNOError').innerHTML = 'format: XXX-XXXXXXX or XXX-XXXXXXXX.';
            } else {
                document.getElementById('StuPNOError').innerHTML = '';
            }
        });

        document.getElementById('STUADDRESS').addEventListener('input', function() {
            var StuAddress = this.value.trim();
            if (StuAddress === '') {
                document.getElementById('StuAddressError').innerHTML = 'Please enter the student\'s address.';
            } else {
                document.getElementById('StuAddressError').innerHTML = '';
            }
        });

        document.getElementById('STUEMAIL').addEventListener('input', function() {
            var StuEmail = this.value.trim();
            if (!StuEmail.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                document.getElementById('StuEmailError').innerHTML = 'Invalid Email.';
            } else {
                document.getElementById('StuEmailError').innerHTML = '';
            }
        });

        document.getElementById('FATHERNAME').addEventListener('input', function() {
            var FatherName = this.value.trim();
            if (!FatherName.match(/^[A-Za-z\s@'.\/]{1,255}$/)) {
                document.getElementById('FatherNameError').innerHTML = 'Name must contain only letters and spaces.';
            } else {
                document.getElementById('FatherNameError').innerHTML = '';
            }
        });

        document.getElementById('MOTHERNAME').addEventListener('input', function() {
            var MotherName = this.value.trim();
            if (!MotherName.match(/^[A-Za-z\s@'.\/]{1,255}$/)) {
                document.getElementById('MotherNameError').innerHTML = 'Name must contain only letters and spaces.';
            } else {
                document.getElementById('MotherNameError').innerHTML = '';
            }
        });

        document.getElementById('SALARY').addEventListener('input', function() {
            var salary = this.value.trim();
            if (!salary.match(/^\d+(\.\d{1,2})?$/) || parseFloat(salary) <= 0) {
                document.getElementById('SalaryError').innerHTML = 'Please enter a valid salary (> 0).';
            } else {
                document.getElementById('SalaryError').innerHTML = '';
            }
        });

    </script>   
</body>
</html>