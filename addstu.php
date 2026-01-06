<?php
session_start();
require_once("dbConnect.php");

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

$STUID = $STUNAME = $STUEMAIL = $STUPNO = $STUPASSWORD = $confirm_password = "";
$STUID_err = $STUNAME_err = $STUEMAIL_err = $STUPNO_err = $STUPASSWORD_err = $confirm_password_err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate the student name
    if (empty(trim($_POST["STUNAME"]))) {
        $STUNAME_err = "Please enter your full name.";
    } else {
        $STUNAME = trim($_POST["STUNAME"]);
        if (!preg_match("/^[A-Za-z\s@'.\/]{1,255}$/", $STUNAME)) {
            $STUNAME_err = "Only letters and white space allowed";
        }
    }

    // Validate the student email
    if (empty(trim($_POST["STUEMAIL"]))) {
        $STUEMAIL_err = "Please enter your email.";
    } else {
        $STUEMAIL = trim($_POST["STUEMAIL"]);
        if (!filter_var($STUEMAIL, FILTER_VALIDATE_EMAIL)) {
            $STUEMAIL_err = "Invalid email format.";
        }
    }

    // Validate the student phone number
    if (empty(trim($_POST["STUPNO"]))) {
        $STUPNO_err = "Please enter your phone number.";
    } else {
        $STUPNO = trim($_POST["STUPNO"]);
        if (!preg_match("/^\d{3}-\d{7}|\d{3}-\d{6}$/", $STUPNO)) {
            $STUPNO_err = "Phone Number must be in format 'XXX-XXXXXXXX' or 'XXX-XXXXXXX'";
        }
    }

    // Validate the student password
    if (empty(trim($_POST["STUPASSWORD"]))) {
        $STUPASSWORD_err = "Please enter a password.";
    } else {
        $STUPASSWORD = trim($_POST["STUPASSWORD"]);
        if (strlen($STUPASSWORD) < 3) {
            $STUPASSWORD_err = "Password must have at least 3 characters.";
        }
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm your password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if ($STUPASSWORD !== $confirm_password) {
            $confirm_password_err = "Passwords do not match.";
        }
    }

    if (empty($STUNAME_err) && empty($STUEMAIL_err) && empty($STUPNO_err) && empty($STUPASSWORD_err) && empty($confirm_password_err)) {
        // Check the highest STUID and increment it by 1
        $sql = "SELECT MAX(STUID) AS max_id FROM STUDENT";
        $result = $dbCon->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $STUID = $row['max_id'];
            if ($STUID) {
                $newID = str_pad((int)$STUID + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newID = '001';
            }
        } else {
            $newID = '001';
        }

        // Insert the new student into the database
        $sql = "INSERT INTO STUDENT (STUID, STUNAME, STUPNO, STUEMAIL, STUPASSWORD, STATUS) VALUES (?, ?, ?, ?, ?, 'active')";
        $stmt = $dbCon->prepare($sql);
        $stmt->bind_param('sssss', $newID, $STUNAME, $STUPNO, $STUEMAIL, $STUPASSWORD);

        if ($stmt->execute()) {
            $sql2 = "INSERT INTO REGISTRATION (STUID, STATUS, REGDATE, REGSTATUS) VALUES (?, 'Pending', NOW(), 'active')";
            $stmt2 = $dbCon->prepare($sql2);
            $stmt2->bind_param('s', $newID);

            if ($stmt2->execute()) {
                echo "<script>
                alert('Successfully registered new student with ID" . $newID . "');
                window.location.href = 'listofstudent.php';</script>";
            } else {
                echo "<script>alert('Error: " . $stmt2->error . "');</script>";
            }
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }

        // Close the statement and the connection
        $stmt->close();
        $stmt2->close();
        $dbCon->close();
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
    <title>Add Student</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
.header a {
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
    color: black;
}
#checkbox {
    display: none;
}
#checkbox:checked ~ .body .side-bar {
    width: 80px;
}
#checkbox:checked ~ .body .side-bar .user-p {
    visibility: hidden;
}
#checkbox:checked ~ .body .side-bar a span {
    display: none;
}
.modal-content {
    background-color: #fefefe;
    margin: 9% auto;
    padding: 20px;
    width: 80%;
    max-width: 600px;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 500px;
}
input[type="text"],
input[type="date"],
input[type="password"] {
    width: 100%;
    padding: 10px;
    font-size: 15px;
    margin-top: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-family: "Poppins", sans-serif;
}
input[type="submit"] {
    width: 100px;
    padding: 10px;
    font-family: "Poppins", sans-serif;
    font-size: 15px;
    background-color: black;
    border-radius: 5px;
    color: white;
    cursor: pointer;
    margin-left: 40%;
    margin-top: 25px;
    transition: background-color 0.3s ease, color 0.3s ease;
}
input[type="submit"]:hover {
    background-color: white;
    color: black;
    border: 2px solid black;
}
.cancel-button {
    width: 100px;
    padding: 14px;
    font-family: "Poppins", sans-serif;
    font-size: 15px;
    background-color: #ff4d4d;
    border-radius: 5px;
    color: white;
    cursor: pointer;
    text-decoration: none;
    margin-left: 10px;
    transition: background-color 0.3s ease, color 0.3s ease;
}
.cancel-button:hover {
    background-color: #ff1a1a;
    color: #fff;
}
.close-icon {
    font-size: 30px;
    color: black;
    cursor: pointer;
    align-self: flex-end;
    text-decoration: none;
    margin-bottom: 10px;
}
form{
    width: 80%;
    margin-top: 20px;
}
.error{
    color: red;
    margin-top: 20px
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
                <li>
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
            <div class="modal-content">
                <a href="listofstudent.php" class="close-icon"><i class="fa fa-times" aria-hidden="true"></i></a>
                <h1>Add Student</h1>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <input type="text" name="STUNAME" id="STUNAME" placeholder="Enter full name" required>
                    <span id="StuNameError" class="error"><?php echo $STUNAME_err?></span>
                    <input type="text" name="STUEMAIL" id="STUEMAIL" placeholder="Enter email"required>
                    <span id="StuEmailError" class="error"><?php echo $STUEMAIL_err?></span>
                    <input type="text" name="STUPNO" id="STUPNO" placeholder="Enter phone no" required>
                    <span id="StuPNOError" class="error"><?php echo $STUPNO_err?></span>
                    <input type="password" name="STUPASSWORD" id="STUPASSWORD" placeholder="Enter password" required>
                    <span id="StuPasswordError" class="error"><?php echo $STUPASSWORD_err?></span>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password" required>
                    <span id="confirm_passwordError" class="error"><?php echo $confirm_password_err?></span>
                    <input type="submit" value="Submit">
                </form>
            </div>
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

            // Validate Student's Email
            var StuEmail = document.getElementById('STUEMAIL').value.trim();
            if (!StuEmail.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                document.getElementById('StuEmailError').innerHTML = 'Invalid Email.';
                isValid = false;
            } else {
                document.getElementById('StuEmailError').innerHTML = '';
            }

            // Validate Student's Password
            var StuPassword = document.getElementById('STUPASSWORD').value.trim();
            if (StuPassword.length < 3) {
                document.getElementById('StuPasswordError').innerHTML = 'Password must have at least 3 characters.';
                if(StuPassword !== document.getElementById('confirm_password').value.trim()) {
                    document.getElementById('confirm_password').value = '';
                    document.getElementById('confirm_password').focus();
                    document.getElementById('confirm_password').placeholder = 'Passwords do not match.';

                    isValid = false;
                } else {
                    document.getElementById('confirm_password').placeholder = 'Confirm password';
                }
            } else {
                document.getElementById('StuPasswordError').innerHTML = '';
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

        document.getElementById('STUEMAIL').addEventListener('input', function() {
            var StuEmail = this.value.trim();
            if (!StuEmail.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                document.getElementById('StuEmailError').innerHTML = 'Invalid Email.';
            } else {
                document.getElementById('StuEmailError').innerHTML = '';
            }
        });

        document.getElementById('STUPASSWORD').addEventListener('input', function() {
            var StuPassword = this.value.trim();
            if (StuPassword.length < 3) {
                document.getElementById('StuPasswordError').innerHTML = 'Password must have at least 3 characters.';
            } else {
                document.getElementById('StuPasswordError').innerHTML = '';
            }
        });

        document.getElementById('confirm_password').addEventListener('input', function() {
            var StuPassword = document.getElementById('STUPASSWORD').value.trim();
            var confirm_password = this.value.trim();

            if (confirm_password.length < 3) {
                document.getElementById('confirm_passwordError').innerHTML = 'Password must have at least 3 characters.';
            } else if (StuPassword !== confirm_password) {
                document.getElementById('confirm_passwordError').innerHTML = 'Passwords do not match.';
            } else {
                document.getElementById('confirm_passwordError').innerHTML = '';
            }
        });

    </script>  
</body>
</html>

