<?php
require_once("dbConnect.php");
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: welcome.php"); // Redirect to login page if not logged in
    exit;
}

$username = $_SESSION['username'];

// Fetch the student's full name
$sql = "SELECT CLERKNAME, CLERKIMAGE FROM clerk WHERE CLERKID = ?";
$stmt = $dbCon->prepare($sql);
$stmt->bind_param("i", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($fullName, $CLERKIMAGE);
$stmt->fetch();
$stmt->close();

$firstName = strtoupper(strtok($fullName, ' '));

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
.image-wrap {
    width: 250px;
    height: 250px;
    border: 4px solid #7360ff;
    box shadow: 0 0 10px rgba(0,0,0,0.3);
    border-radius: 10%;
    overflow: hidden; 
    margin-left: 40px;
}

.image-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Ensures the image covers the entire area while maintaining aspect ratio */
    object-position: center; /* Centers the image within the frame */
}

table tr td{
    padding: 10px 15px;
}
.clerkinput{
    width: 300px;
    line-height: 1.5;
    margin-top: 15px;
}
.clerkinput1{
    width: 300px;
    line-height: 1.5;
    margin-top: 40px;
}
.clerkinput span, .clerkinput1 span{
    color: #808080;
    font-weight: 600;
}
.edit-button{
    display: inline-flex;
    background-color: #7360ff;
    padding: 10px 15px;
    margin-top: 5px;
    border-radius: 10px;
    transition: background-color 0.3s ease;
    margin-left: 53%;
    margin-top: 40px;

}
.edit-button a {
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
    color: white;
}
.edit-button i{
    margin-right: 8px;
}
.edit-button:hover{
    background-color: #5a47d8;
}
.profile-picture {
            width: 150px; /* Adjust size as needed */
            height: 150px;
            border-radius: 1rem;
            object-fit: cover;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }

        .profile-picture:hover {
            transform: scale(1.1); /* Scale up the image on hover */
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
            <?php
                $sql = "SELECT * FROM clerk WHERE CLERKID = ?";
                $stmt1 = $dbCon->prepare($sql);
                $stmt1->bind_param("i", $username);
                $stmt1->execute();
                $stmt1->store_result();
                $stmt1->bind_result($clerkID, $clerkName, $clerkPhone, $clerkDOB, $clerkEmail, $clerkType, $clerkPassword, $clerkImage,$clerkStatus);
                $row = $stmt1->fetch();

            ?>
            <div class="profile-wrap">
                <table>
                    <tr>
                        <td>
                            <div class="image-wrap">
                            <img src="<?php echo !empty($CLERKIMAGE) ? 'CLERK/' . htmlspecialchars($CLERKIMAGE) : 'default-profile.png'; ?>" alt="Profile Picture">
                            </div>
                        </td>
                        <td>
                            <div class="clerkinput1">
                                <p>
                                    <b>ID :</b><br>
                                    <span><?php echo $clerkID;?></span>
                                </p>
                            </div>
                            <div class="clerkinput">
                                <p>
                                    <b>Name :</b><br>
                                    <span><?php echo $clerkName; ?></span>
                                </p>
                            </div>
                            <div class="clerkinput">
                                <p>
                                    <b>Phone Number :</b><br>
                                    <span><?php echo $clerkPhone; ?></span>
                                </p>
                            </div>
                            <div class="clerkinput">
                                <p>
                                    <b>Email :</b><br>
                                    <span><?php echo $clerkEmail; ?></span>
                                </p>
                            </div>
                            <div class="clerkinput">
                                <p>
                                    <b>Date of Birth :</b><br>
                                    <span><?php echo $clerkDOB; ?></span>
                                </p>
                            </div>
                            <div class="clerkinput">
                                <p>
                                    <b>Clerk Status :</b><br>
                                    <span><?php echo $clerkStatus; ?></span>
                                </p>
                            </div>
                            <div class="edit-button">
                                <a href="editclerkprofile.php"><i class="fa fa-pencil" aria-hidden="true"></i>Edit Profile</a>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </section>
    </div>
    <script>
        document.getElementById('profile-picture-upload').addEventListener('change', function() {
            document.getElementById('submit-profile-picture').click();
        });
    </script>
</body>
</html>
