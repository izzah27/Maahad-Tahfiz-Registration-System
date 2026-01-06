<?php
require_once("dbConnect.php");
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: welcome.php"); // Redirect to login page if not logged in
    exit;
}

$username = $_SESSION['username'];

// Fetch the student's full name
$sql = "SELECT CLERKNAME, CLERKPNO, CLERKDOB, CLERKEMAIL FROM clerk WHERE CLERKID = ?";
$stmt = $dbCon->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($fullName, $phoneNo, $dob, $email);
$stmt->fetch();
$stmt->close();

$firstName = strtoupper(strtok($fullName, ' '));

$CLERKNAME = $CLERKPHONENO = $CLERKEMAIL = $CLERKDOB = "";
$CLERKNAME_err = $CLERKPHONENO_err = $CLERKEMAIL_err = $CLERKDOB_err = "";

if($_SERVER["REQUEST_METHOD"]== "POST"){

    if(empty(trim($_POST["CLERKNAME"]))){
        $CLERKNAME_err = "Please enter your name.";
    } else{
        $CLERKNAME = trim($_POST["CLERKNAME"]);
        if(!preg_match("/^[a-zA-Z-' ]*$/", $CLERKNAME)){
            $CLERKNAME_err = "Only letters and white space allowed.";
        }
    }
    if(empty(trim($_POST['CLERKPNO']))){
        $CLERKPHONENO_err = "Please enter your phone number.";
    } else{
        $CLERKPNO = trim($_POST['CLERKPNO']);
        if(!preg_match("/^\d{3}-\d{7}|\d{3}-\d{6}$/", $CLERKPNO)){
            $CLERKPHONENO_err = "Phone Number must be in format 'XXX-XXXXXXXX' or 'XXX-XXXXXXX'";
        }
    }
    if(empty(trim($_POST['CLERKEMAIL']))){
        $CLERKEMAIL_err = "Please enter your email.";
    } else{
        $CLERKEMAIL = trim($_POST['CLERKEMAIL']);
        if(!filter_var($CLERKEMAIL, FILTER_VALIDATE_EMAIL)){
            $CLERKEMAIL_err = "Invalid email format.";
        }
    }

    $CLERKDOB = $_POST['CLERKDOB'];
    

    $sql1 = "UPDATE clerk SET CLERKNAME = ?, CLERKPNO = ?, CLERKEMAIL = ?, CLERKDOB = ? WHERE CLERKID = ?";
    $stmt1 = $dbCon->prepare($sql1);
    $stmt1->bind_param("sssss", $CLERKNAME, $CLERKPNO, $CLERKEMAIL, $CLERKDOB, $username);
    if($stmt1->execute()){
        echo "<script>alert('Profile updated successfully.');
        window.location.href = 'ClerkProfile.php'</script>";
    } else {
        echo "<script>alert('Error updating profile.');</script>";
    }

    $newProfileImage = $_FILES['CLERKIMAGE']['name'];

    // Image upload handling
    $target_dir = "CLERK/";
    $target_file = $target_dir . basename($_FILES["CLERKIMAGE"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image or fake image
    if ($newProfileImage) {
        $check = getimagesize($_FILES["CLERKIMAGE"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["CLERKIMAGE"]["size"] > 500000) {
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
            if (move_uploaded_file($_FILES["CLERKIMAGE"]["tmp_name"], $target_file)) {
                echo "The file " . htmlspecialchars(basename($_FILES["CLERKIMAGE"]["name"])) . " has been uploaded.";
                // Update the database with the new image path
                $stmt = $dbCon->prepare("UPDATE CLERK SET CLERKIMAGE = ? WHERE CLERKID = ?");
                if ($stmt === false) {
                    die("Error preparing SQL: " . $dbCon->error);
                }
                $stmt->bind_param("ss", basename($_FILES["CLERKIMAGE"]["name"]), $username);
                if ($stmt->execute()) {
                    echo "Database updated with new profile image.";
                } else {
                    echo "Error updating database: " . $stmt->error;
                }
                $stmt->close();
                // No need to set $CLERKIMAGE here as it is not used further in the code
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    // Update user data
    if ($newProfileImage && $uploadOk) {
        // Update user data including new profile image
        $updateStmt = $dbCon->prepare("UPDATE CLERK SET CLERKNAME = ?, CLERKPNO = ?, CLERKEMAIL = ?, CLERKDOB = ?, CLERKIMAGE = ? WHERE CLERKID = ?");
        if ($updateStmt === false) {
            die("Prepare failed: " . $dbCon->error);
        }
        $updateStmt->bind_param('sssss', $CLERKNAME, $CLERKPNO, $CLERKEMAIL, $CLERKDOB, basename($_FILES["CLERKIMAGE"]["name"]), $username);
    } else {
        // Update user data without changing profile image
        $updateStmt = $dbCon->prepare("UPDATE CLERK SET CLERKNAME = ?, CLERKPNO = ?, CLERKEMAIL = ?, CLERKDOB = ? WHERE CLERKID = ?");
        if ($updateStmt === false) {
            die("Prepare failed: " . $dbCon->error);
        }
        $updateStmt->bind_param('sssss', $CLERKNAME, $CLERKPNO, $CLERKEMAIL, $CLERKDOB, $username);
    }

    if (!$updateStmt->execute()) {
        die("Execute failed: " . $updateStmt->error);
    }

    $updateStmt->close();
    header("Location: ClerkProfile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap">
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
.profile-wrap{
    width: 50%;
    background-color: #fff;
    border-radius: 10px;
    margin-top: 100px;
    padding: 20px;
}
table{
    width: 100%;
}
table tr td{
    padding: 10px;
    font-size: 18px;
}
.cancel-button{
    width: 90px;
    border: 2px solid #7360ff;
    padding: 10px 15px;
    margin-top: 5px;
    border-radius: 10px;
    background-color: transparent;
}
.cancel-button a {
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
    color: black;
}
.cancel-button i{
    margin-right: 8px;
}
.cancel-button:hover
{
    background-color: #7360ff;
}
.cancel-button a:hover
{
    color: white;
}
.profile-wrap input[type="text"], .profile-wrap input[type="date"]{
    padding: 10px 15px;
    width: 100%;
    font-size: 18px;
    border-radius: 10px;
    outline: none;
    border: 1px solid #ccc;
}
.profile-wrap input[type="file"]
{
    font-size: 15px;
    cursor: pointer;
}
.profile-wrap input[type="submit"]
{
    width: 90px;
    padding: 10px 15px;
    color: white;
    font-size: 17px;
    font-weight: bold;
    border: none;
    cursor: pointer;
    margin-right: 20px;
    background-color: #7360ff;
    transition: background-color 0.3s ease;
    border-radius: 10px;
}
.profile-wrap input[type="submit"]:hover{
    background-color: #5a47d8;
}
/* Add this class to center elements within a table cell */
.center-buttons {
    text-align: center;
}

.profile-wrap input[type="submit"],
.profile-wrap .cancel-button {
    display: inline-block;
    width: auto; /* Adjust width to fit content */
    margin: 10px 5px; /* Optional: Add margin for spacing */
}
.error {
            color: red;
            font-weight: bold;
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
            <div class="circled-menu-parent">
                <p><i class="fa fa-th-large" style="font-size:25px;"></i>Profile</p>
            </div>
            <div class="profile-wrap">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                    <table>
                        <tr>
                            <td><b>ID : </b></td>
                            <td><?php echo $_SESSION['username']?></td>
                        </tr>
                        <tr>
                            <td><b>Name : </b></td>
                            <td><input type="text" name="CLERKNAME" id="CLERKNAME" value="<?php echo $fullName; ?>"><span id="ClerkNameError" class="error"><?php echo $CLERKNAME_err?></span></td>
                            
                        </tr>
                        <tr>
                            <td><b>Phone Number : </b></td>
                            <td><input type="text" name="CLERKPNO" id="CLERKPNO" value="<?php echo $phoneNo; ?>"><span id="ClerkPNOError" class="error"><?php echo $CLERKPHONENO_err?></span></td>
                            
                        </tr>
                        <tr>
                            <td><b>Email :</b></td>
                            <td><input type="text" name="CLERKEMAIL" id="CLERKEMAIL" value="<?php echo $email; ?>"><span id="ClerkEmailError" class="error"><?php echo $CLERKEMAIL_err?></span></td>
                        </tr>
                        <tr>
                            <td><b>Date of Birth :</b></td>
                            <td><input type="date" name="CLERKDOB" value="<?php echo $dob; ?>"></td>
                        </tr>
                        <tr>
                            <td><b>Profile Image :</b></td>
                            <td><input type="file" id="CLERKIMAGE" name="CLERKIMAGE" accept="image/*"></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="center-buttons">
                                <input type="submit" value="Save">
                                <button class="cancel-button"><a href="ClerkProfile.php">Cancel</a></button>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </section>
    </div>
    <script>
        // Function to validate form fields on submit
        function validateForm() {
            var isValid = true;

            // Validate Student's Name
            var ClerkName = document.getElementById('CLERKNAME').value.trim();
            if (!ClerkName.match(/^[A-Za-z\s@'.\/]{1,255}$/)) {
                document.getElementById('ClerkNameError').innerHTML = 'Please enter a valid name (only letters and spaces, max 50 characters).';
                isValid = false;
            } else {
                document.getElementById('ClerkNameError').innerHTML = '';
            }

            // Validate Student's Phone Number
            var ClerkPhone = document.getElementById('CLERKPNO').value.trim();
            if (!ClerkPhone.match(/^\d{3}-\d{7}|\d{3}-\d{6}$/)) {
                document.getElementById('ClerkPNOError').innerHTML = 'Please enter a valid phone number (format: XXX-XXXXXXX or XXX-XXXXXXXX).';
                isValid = false;
            } else {
                document.getElementById('ClerkPNOError').innerHTML = '';
            }

            // Validate Student's Email
            var ClerkEmail = document.getElementById('CLERKEMAIL').value.trim();
            if (!ClerkEmail.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                document.getElementById('ClerkEmailError').innerHTML = 'Invalid Email.';
                isValid = false;
            } else {
                document.getElementById('ClerkEmailError').innerHTML = '';
            }

            return isValid;
        }

        // Real-time validation on input change
        document.getElementById('CLERKNAME').addEventListener('input', function() {
            var ClerkName = this.value.trim();
            if (!ClerkName.match(/^[A-Za-z\s@'.\/]{1,255}$/)) {
                document.getElementById('ClerkNameError').innerHTML = 'Please enter a valid name (only letters and spaces).';
            } else {
                document.getElementById('ClerkNameError').innerHTML = '';
            }
        });

        document.getElementById('CLERKPNO').addEventListener('input', function() {
            var ClerkPNO = this.value.trim();
            if (!ClerkPNO.match(/^\d{3}-\d{7}|\d{3}-\d{6}$/)) {
                document.getElementById('ClerkPNOError').innerHTML = 'Please enter a valid phone number (format: XXX-XXXXXXX or XXX-XXXXXXXX).';
            } else {
                document.getElementById('ClerkPNOError').innerHTML = '';
            }
        });

        document.getElementById('CLERKEMAIL').addEventListener('input', function() {
            var ClerkEmail = this.value.trim();
            if (!ClerkEmail.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                document.getElementById('ClerkEmailError').innerHTML = 'Invalid Email.';
            } else {
                document.getElementById('ClerkEmailError').innerHTML = '';
            }
        });

    </script>   
</body>
</html>
