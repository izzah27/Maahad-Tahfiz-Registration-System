<?php
require_once("dbConnect.php");

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
                window.location.href = 'signup.php';</script>";
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Arial:wght@700&display=swap" />
    <title>Sign Up</title>
</head>
<style>
    * {
        padding: 0;
        margin: 0;
        box-sizing: border-box;
    }
    body {
        width: 100%;
        background-color: #E2E0E0;
        background-size: cover;
        display: flex;
        align-items: center;
        flex-direction: column;
    }
    .wrapper {
        width: 600px;
        height: 700px;
        margin-top: 4%;
        background-color: white;
        border-radius: 30px;
        box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
    }
    .wrapper h1 {
        text-align: center;
        font-weight: 200;
        padding-top: 50px;
        padding-bottom: 15px;
        font-size: 60px;
        letter-spacing: 0.05em;
    }
    .wrapper input[type="text"],
    .wrapper input[type="password"] {
        margin-top: 10px;
        margin-left: 100px;
        margin-bottom: 20px;
        font-size: 20px;
        padding-bottom: 20px;
        width: 390px;
        border-bottom-color: black;
        border-left: none;
        border-right: none;
        border-top: none;
        outline: none;
    }
    .wrapper input[type="submit"] {
        padding: 10px;
        width: 390px;
        height: 45px;
        font-size: 15px;
        margin-left: 100px;
        background-color: #CDB433;
        border: none;
        font-weight: bold;
        color: aliceblue;
        border-radius: 5px;
        cursor: pointer;
    }
    .wrapper input[type="submit"]:hover {
        text-decoration: underline;
        background-color: #b8a549;
    }
    .back-button {
    background: none;
    border: none;
    position: absolute;

    font-size: 30px;
    color: #000000;
}
.back-button a:hover{
    color: grey;
}
    .line1,
    .line2 {
        border: 1px solid grey;
        width: 150px;
        display: inline-block;
        align-items: center;
        justify-content: center;
    }
    .line1 {
        margin-top: 27px;
        margin-left: 130px;
    }
    .line2 {
        margin-top: 27px;
    }
    .already-have-account {
        margin-top: 30px;
        margin-left: 180px;
        font-family: 'Inter';
        color: rgba(0, 0, 0, 0.705);
    }
    .already-have-account a {
        margin-left: 10px;
        text-decoration: none;
        color: black;
    }
    .already-have-account a:hover {
        text-decoration: underline;
    }
    .error {
        color: red;
        font-size: 14px;
        margin-left: 100px;
    }
</style>
<body>
    <div class="wrapper">
    <button class="back-button"><a href="listofstudent.php"><i class="fa fa-arrow-left" aria-hidden="true"></i></a></button>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" onsubmit="return validateForm()">
            <h1>Register Student</h1>
            <p style="text-align: center; font-family: inter; color: rgba(0, 0, 0, 0.705); padding-bottom: 30px;">Add new student</p>
            
            <input type="text" name="STUNAME" id="STUNAME" placeholder="Enter full name" required>
            <span id="StuNameError" class="error"><?php echo $STUNAME_err?></span>
            <input type="text" name="STUEMAIL" id="STUEMAIL" placeholder="Enter email"required>
            <span id="StuEmailError" class="error"><?php echo $STUEMAIL_err?></span>
            <input type="text" name="STUPNO" id="STUPNO" placeholder="Enter phone no" required>
            <span id="StuPNOError" class="error"><?php echo $STUPNO_err?></span>
            <input type="password" name="STUPASSWORD" id="STUPASSWORD" placeholder="Enter password"  required>
            <span id="StuPasswordError" class="error"><?php echo $STUPASSWORD_err?></span>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password"  required>
            <span id="confirm_passwordError" class="error"><?php echo $confirm_password_err?></span>
            <input type="submit" name="submit" value="REGISTER">
        </form>
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
