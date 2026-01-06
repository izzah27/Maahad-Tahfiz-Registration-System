<?php
require_once("../dbConnect.php");

if (isset($_GET['clerkid'])) {
    $clerkId = $_GET['clerkid'];
    $sql = "SELECT * FROM clerk WHERE CLERKID = ?";
    $stmt = $dbCon->prepare($sql);
    $stmt->bind_param("i", $clerkId);
    $stmt->execute();
    $result = $stmt->get_result();
    $clerk = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clerkId = $_POST['CLERKID'];
    $name = $_POST['CLERKNAME'];
    $email = $_POST['CLERKEMAIL'];
    $phone = $_POST['CLERKPNO'];
    $dob = $_POST['CLERKDOB'];

    $sql = "UPDATE clerk SET CLERKNAME = ?, CLERKEMAIL = ?, CLERKPNO = ?, CLERKDOB = ? WHERE CLERKID = ?";
    $stmt = $dbCon->prepare($sql);
    $stmt->bind_param("ssssi", $name, $email, $phone, $dob, $clerkId);

    if ($stmt->execute()) {
        echo "Clerk details updated successfully.";
    } else {
        echo "Error updating clerk details: " . $stmt->error;
    }

    $stmt->close();
    $dbCon->close();
    header("Location: listofclerk.php");
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
    transition: background 500ms;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    border: none;
    border-top: 1px solid grey;
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
.circled-menu-parent {
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
.circled-menu-parent p {
    margin: 0;
    display: flex;
    align-items: center;
    font-size: 25px;
    font-family: "Poppins", sans-serif;
}
.circled-menu-parent i {
    margin-left: 20px;
    margin-right: 15px;
}
.edit-clerk {
    width: 600px;
    background: white;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 10px;
    margin-top: 100px;
}
.image-container {
    margin-top: 40px;
    margin-bottom: 20px;
    width: 200px;
    height: 200px;
    border-radius: 10%;
    border: 3px solid #ADD8E6; /* Soft blue color for the border */
    box-shadow: 0 0 10px 3px rgba(173, 216, 230, 0.7); /* Soft blue glow effect */
}
.image-container img{
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 10%;
}
.form-class {
    padding: 10px;
    width: 80%;
}
.form-class label {
    font-family: "Poppins", sans-serif;
    font-size: 18px;
    font-weight: bold;
}
.form-class input[type="text"],
.form-class input[type="date"] {
    margin-top: 5px;
    padding: 10px;
    width: 100%;
    font-size: 15px;
    font-family: "Poppins", sans-serif;
    border-radius: 10px;
    border: 1px solid #ccc;
    margin-bottom: 15px;
}
.submit-container {
    display: flex;
    justify-content: center;
    margin-top: 10px;
    margin-bottom: 10px;
    gap: 10px; /* Add space between buttons */
}

.form-class input[type="submit"] {
    padding: 10px 20px;
    font-size: 15px;
    font-family: "Poppins", sans-serif;
    border-radius: 10px;
    border: none;
    background-color: #4CAF50; /* Green background color */
    color: white;
    cursor: pointer;
    transition: background-color 0.3s, box-shadow 0.3s;
}

.form-class input[type="submit"]:hover {
    background-color: #45a049; /* Darker green on hover */
    box-shadow: 0 4px 8px rgba(0, 128, 0, 0.4); /* Green shadow on hover */
}

.submit-container a {
    text-decoration: none;
}

.submit-container a.cancel-button {
    display: inline-block;
    padding: 10px 20px;
    font-size: 15px;
    font-family: "Poppins", sans-serif;
    border-radius: 10px;
    border: none;
    background-color: #f44336; /* Red background color */
    color: white;
    cursor: pointer;
    transition: background-color 0.3s, box-shadow 0.3s;
    text-align: center; /* Center the text inside the button */
}

.submit-container a.cancel-button:hover {
    background-color: #c62828; /* Darker red on hover */
    box-shadow: 0 4px 8px rgba(255, 0, 0, 0.4); /* Red shadow on hover */
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
            <div class="edit-clerk">
                <div class="image-container">
                    <?php
                        $imagePath = htmlspecialchars($clerk['CLERKIMAGE']);
                        echo "<img src='../CLERK/$imagePath' alt='Profile Image'>";
                    ?>
                </div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="form-class">
                    <input type="text" id="CLERKID" name="CLERKID" value="<?php echo htmlspecialchars($clerk['CLERKID']); ?>" hidden><br>
                    <label for="CLERKNAME">Name</label><br>
                    <input type="text" id="CLERKNAME" name="CLERKNAME" value="<?php echo htmlspecialchars($clerk['CLERKNAME']); ?>" required><br>
                    
                    <label for="CLERKEMAIL">Email</label><br>
                    <input type="text" id="CLERKEMAIL" name="CLERKEMAIL" value="<?php echo htmlspecialchars($clerk['CLERKEMAIL']); ?>" required><br>
                    
                    <label for="clerkpno">Phone</label><br>
                    <input type="text" id="CLERKPNO" name="CLERKPNO" value="<?php echo htmlspecialchars($clerk['CLERKPNO']); ?>" required><br>
                    
                    <label for="CLERKDOB">Date of Birth</label><br>
                    <input type="date" id="CLERKDOB" name="CLERKDOB" value="<?php echo htmlspecialchars($clerk['CLERKDOB']); ?>" required><br>
                    
                    <div class="submit-container">
                        <input type="submit" value="Save">
                        <a href="listofclerk.php" class="cancel-button">Cancel</a>
                    </div>
                </form>
            </div>

        </section>
    </div>
</body>
</html>
