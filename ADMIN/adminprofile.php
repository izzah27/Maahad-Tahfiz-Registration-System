<?php
require_once("../dbConnect.php");
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../welcome.php"); // Redirect to login page if not logged in
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

// Fetch all clerk information
$sql = "SELECT CLERKNAME, CLERKEMAIL, CLERKDOB, CLERKPNO, CLERKIMAGE FROM clerk WHERE CLERKID = ?";
$stmt = $dbCon->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($CLERKNAME, $CLERKEMAIL, $CLERKDOB, $CLERKPNO, $CLERKIMAGE);
$stmt->fetch();
$stmt->close();

$formattedDOB = date("d F Y", strtotime($CLERKDOB));

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $CLERKNAME = $_POST['CLERKNAME'];
    $CLERKDOB = $_POST['CLERKDOB'];
    $CLERKPNO = $_POST['CLERKPNO'];
    $CLERKEMAIL = $_POST['CLERKEMAIL'];
    $newProfileImage = $_FILES['CLERKIMAGE']['name'];

    // Image upload handling
    $target_dir = "../CLERK/";
    $target_file = $target_dir . basename($_FILES["CLERKIMAGE"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image or fake image
    if ($newProfileImage) {
        $check = getimagesize($_FILES["CLERKIMAGE"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File is not an image.<br>";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["CLERKIMAGE"]["size"] > 500000) {
            echo "Sorry, your file is too large.<br>";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.<br>";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.<br>";
        } else {
            // Ensure the directory exists
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            if (move_uploaded_file($_FILES["CLERKIMAGE"]["tmp_name"], $target_file)) {
                echo "The file " . htmlspecialchars(basename($_FILES["CLERKIMAGE"]["name"])) . " has been uploaded.<br>";
                // Update the database with the new image path
                $stmt = $dbCon->prepare("UPDATE clerk SET CLERKIMAGE = ? WHERE CLERKID = ?");
                if ($stmt === false) {
                    die("Error preparing SQL: " . $dbCon->error);
                }
                $stmt->bind_param("ss", basename($_FILES["CLERKIMAGE"]["name"]), $username);
                if ($stmt->execute()) {
                    echo "Database updated with new profile image.<br>";
                } else {
                    echo "Error updating database: " . $stmt->error . "<br>";
                }
                $stmt->close();
            } else {
                echo "Sorry, there was an error uploading your file.<br>";
            }
        }
    }

    // Update user data
    if ($newProfileImage && $uploadOk) {
        // Update user data including new profile image
        $updateStmt = $dbCon->prepare("UPDATE clerk SET CLERKNAME = ?, CLERKPNO = ?, CLERKEMAIL = ?, CLERKDOB = ?, CLERKIMAGE = ? WHERE CLERKID = ?");
        if ($updateStmt === false) {
            die("Prepare failed: " . $dbCon->error);
        }
        $updateStmt->bind_param('ssssss', $CLERKNAME, $CLERKPNO, $CLERKEMAIL, $CLERKDOB, basename($_FILES["CLERKIMAGE"]["name"]), $username);
    } else {
        // Update user data without changing profile image
        $updateStmt = $dbCon->prepare("UPDATE clerk SET CLERKNAME = ?, CLERKPNO = ?, CLERKEMAIL = ?, CLERKDOB = ? WHERE CLERKID = ?");
        if ($updateStmt === false) {
            die("Prepare failed: " . $dbCon->error);
        }
        $updateStmt->bind_param('sssss', $CLERKNAME, $CLERKPNO, $CLERKEMAIL, $CLERKDOB, $username);
    }

    if (!$updateStmt->execute()) {
        die("Execute failed: " . $updateStmt->error);
    }

    $updateStmt->close();
    header("Location: adminprofile.php");
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
    <title>Clerk Profile</title>
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
.profile-wrap {
    width: 800px;
    display: flex;
    background-color: white;
    border-radius: 10px;
    padding: 40px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Adding shadow */
    margin-top: 120px;
}

.image-frame{
    width: 250px;
    height: 250px;
    border: 3px solid #ccc;
    border-radius: 50%;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Adding shadow */
    margin-top: 40px;
    margin-left: 20px;
}
.image-frame img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}
.profile-wrap table{
    width: 350x;
    margin-left: 40px;
    position: relative;
}
.profile-wrap i{
    width: 20px;
    height: 20px;
    text-align: center;
    font-size: 23px;
    margin-right: 5px;
    margin-bottom: 15px;
}
.profile-wrap table th{
    text-align: left;
    padding: 10px;
    font-size: 20px;
    font-family: "Poppins", sans-serif;
}
.profile-wrap table td{
    padding: 10px;
    font-size: 18px;
    font-family: "Poppins", sans-serif;
    letter-spacing: 0.02em; /* Adjust the value as per your design */
}
.edit-button{
    position: absolute;
    left: 75%;
}
.edit-button button{
    text-decoration: none;
    color: black;
    background: none;
    border: none;
    cursor: pointer;
}
.edit-button button:hover{
    color: grey;
}
.modal {
    display: none;
    position: fixed; 
    z-index: 1; 
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%; 
    overflow: auto; 
    background-color: rgb(0,0,0); 
    background-color: rgba(0,0,0,0.4); 
}

.modal-content {
    background-color: #fefefe;
    margin: 12% auto; 
    padding: 20px;
    width: 80%;
    max-width: 1200px;
    border-radius: 10px;
    display: flex;
    height: 570px;

}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    position: absolute;
    left: 79%;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
.edit-image{
    width: 400px;
    height: 400px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.25), 0 6px 6px rgba(0, 0, 0, 0.22); /* Adjusted shadow */
    border-radius: 10%;
    position: relative;
    margin-left: 80px;
    margin-top: 30px;
}
.edit-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 10%;
}
.modal-content .table1 {
    margin-left: 200px; 
    width: 450px;
    position: absolute;
    top: 27%;
    left: 40%;
}
.table1 th{
    font-family: "Poppins", sans-serif;
    text-align: left;
    font-size: 20px;
    padding: 10px;
}
.table1 td {
    font-family: "Poppins", sans-serif;
    font-size: 18px;
    padding: 10px;
}
input[type="text"],
input[type="date"]
{
    width: 100%;
    padding: 10px;
    font-size: 15px;
    margin-top: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-family: "Poppins", sans-serif;
}
input[type="submit"]
{
    width: 100px;
    padding: 10px;
    font-family: "Poppins", sans-serif;
    font-size: 15px;
    background-color: black;
    border-radius: 5px;
    color: white;
    cursor: pointer;
    margin-left: 77%;
}
input[type="submit"]:hover{
    background-color: white;
    color: black;
    border: 2px solid black;
}
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');


:root {
	--blue: #0071FF;
	--light-blue: #B6DBF6;
	--dark-blue: #005DD1;
	--grey: #f2f2f2;
}

.container {
	max-width: 400px;
	width: 100%;
	background: #fff;
	padding: 30px;
	border-radius: 30px;
    position: relative;
}
.img-area {
	position: relative;
	width: 100%;
	height: 240px;
	background: var(--grey);
	margin-bottom: 30px;
	border-radius: 15px;
	overflow: hidden;
	display: flex;
	justify-content: center;
	align-items: center;
	flex-direction: column;
}
.img-area .icon {
	font-size: 100px;
}
.img-area h3 {
	font-size: 20px;
	font-weight: 500;
	margin-bottom: 6px;
    font-family: 'Poppins', sans-serif;
}
.img-area p {
	color: #999;
    font-family: 'Poppins', sans-serif;
}
.img-area p span {
	font-weight: 600;
}
.img-area img {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	object-fit: cover;
	object-position: center;
	z-index: 100;
}
.img-area::before {
	content: attr(data-img);
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: rgba(0, 0, 0, .5);
	color: #fff;
	font-weight: 500;
	text-align: center;
	display: flex;
	justify-content: center;
	align-items: center;
	pointer-events: none;
	opacity: 0;
	transition: all .3s ease;
	z-index: 200;
}
.img-area.active:hover::before {
	opacity: 1;
}
.select-image {
	display: block;
	width: 100%;
	padding: 16px 0;
	border-radius: 15px;
	background: var(--blue);
	color: #fff;
	font-weight: 500;
	font-size: 16px;
	border: none;
	cursor: pointer;
	transition: all .3s ease;
    font-family: 'Poppins', sans-serif;
}
.select-image:hover {
	background: var(--dark-blue);
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
                <p><i class="fa fa-th-large" style="font-size:25px;"></i>Profile</p>
            </div>
            <div class="profile-wrap">
                <div class="image-frame">
                    <img src="<?php echo !empty($CLERKIMAGE) ? '../CLERK/' . htmlspecialchars($CLERKIMAGE) : 'default-profile.png'; ?>" alt="Profile Picture">
                </div>
                <table>
                    <tr>
                        <th>Personal Details</th>
                    </tr>
                    <tr>
                        <td>
                            <b><i class="fa fa-user-o" aria-hidden="true"></i> Full Name</b><br>
                            <p><?php echo htmlspecialchars($CLERKNAME); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b><i class="fa fa-envelope-o" aria-hidden="true"></i> Email</b><br>
                            <p><?php echo htmlspecialchars($CLERKEMAIL); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b><i class="fa fa-calendar" aria-hidden="true"></i> Date Of Birth</b><br>
                            <p><?php echo htmlspecialchars($formattedDOB); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b><i class="fa fa-mobile" aria-hidden="true"></i> Phone Number</b><br>
                            <p><?php echo htmlspecialchars($CLERKPNO); ?></p>
                        </td>
                    </tr>
                </table>
                <div class="edit-button">
                    <button><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
                </div>
            </div>
        </section>
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                    <div class="edit-image">
                        <div class="container">
                            
                            <div class="img-area" data-img="">
                                <i class='bx bxs-cloud-upload icon'></i>
                                <h3>Upload Image</h3>
                                <p>Image size must be less than <span>2MB</span></p>
                            </div>
                            <button class="select-image">Select Image
                                <input type="file" id="CLERKIMAGE" name="CLERKIMAGE" accept="image/*" hidden>
                            </button>

                        </div>
                    </div>
                    <table class="table1">
                        <tr>
                            <th>Account Details</th>
                        </tr>
                        <tr>
                            <td>
                                Full Name <span style="color:red">*</span><br>
                                <input type="text" name="CLERKNAME" required>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Phone Number <span style="color:red">*</span><br>
                                <input type="text" name="CLERKPNO" required>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Date Of Birth <span style="color:red">*</span><br>
                                <input type="date" name="CLERKDOB" required>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Email <span style="color:red">*</span><br>
                                <input type="text" name="CLERKEMAIL" required>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="submit" value="Save"></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>
    <script>
        // Get the modal
        var modal = document.getElementById("editModal");

        // Get the button that opens the modal
        var btn = document.querySelector(".edit-button button");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks the button, open the modal 
        btn.onclick = function(event) {
            event.preventDefault();
            modal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        const selectImage = document.querySelector('.select-image');
        const inputFile = document.querySelector('#CLERKIMAGE'); // Updated to match the ID in the HTML
        const imgArea = document.querySelector('.img-area');

        selectImage.addEventListener('click', function () {
            inputFile.click();
        });

        inputFile.addEventListener('change', function () {
            const image = this.files[0];
            if(image.size < 2000000) {
                const reader = new FileReader();
                reader.onload = () => {
                    const allImg = imgArea.querySelectorAll('img');
                    allImg.forEach(item => item.remove());
                    const imgUrl = reader.result;
                    const img = document.createElement('img');
                    img.src = imgUrl;
                    imgArea.appendChild(img);
                    imgArea.classList.add('active');
                    imgArea.dataset.img = image.name;
                };
                reader.readAsDataURL(image);
            } else {
                alert("Image size more than 2MB");
            }
        });

    </script>
</body>
</html>
