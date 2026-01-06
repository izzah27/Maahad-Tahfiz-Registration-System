<?php
require_once("dbConnect.php");
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: welcome.php");
    exit;
}

$username = $_SESSION['username'];

// Fetch the admin's full name
$sql = "SELECT CLERKNAME FROM CLERK WHERE CLERKID = ?";
$stmt = $dbCon->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($fullName);
$stmt->fetch();
$stmt->close();

// Split the full name to get the first name and convert to uppercase
$firstName = strtoupper(strtok($fullName, ' '));

// Fetch the list of clerks
$sql = "SELECT CLERKID, CLERKNAME, CLERKEMAIL FROM CLERK";
$stmt = $dbCon->prepare($sql);
$stmt->execute();
$stmt->bind_result($clerkId, $clerkName, $clerkEmail);
$clerks = [];
while ($stmt->fetch()) {
    $clerks[] = ['id' => $clerkId, 'name' => $clerkName, 'email' => $clerkEmail];
}
$stmt->close();
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
        color: #000;
    }
    .header i {
        font-size: 30px;
        cursor: pointer;
        color: #000;
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
        background: #D9D7C8;
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
        background: white;
    }
    .side-bar ul li:hover > ul {
        display: block;
    }
    .side-bar ul li a {
        text-decoration: none;
        color: black;
        cursor: pointer;
        letter-spacing: 1px;
        font-weight: bold;
    }
    .side-bar i {
        display: inline-block;
        padding-right: 10px;
        width: 30px;
        vertical-align: center;
        font-size: 25px;
    }
    .side-bar ul li a i {
        padding-right: 10px;
        font-size: 30px;
    }
    .section-1 {
        width: 100%;
        background-color: #F5EFE7;
        background-size: cover;
        display: flex;
        flex-direction: column;
    }
    #navbtn {
        display: inline-block;
        left: 20px;
        font-size: 20px;
        transition: 500ms color;
        color: #000;
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
    }
    .circled-menu-parent p {
        margin: 0;
        display: flex;
        align-items: center;
        font-size: 25px;
    }
    .circled-menu-parent i {
        margin-left: 20px;
        margin-right: 15px;
    }
    .welcome-name {
        font-size: 30px;
        margin-left: 50px;
        font-family: "Inter";
        margin-top: 40px;
    }
    .clerk-table {
        width: 80%;
        margin: 100px auto;
        border-collapse: collapse;

    }
    .clerk-table th, .clerk-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    .clerk-table th {
        background-color: #E26C16;
    }
    .clerk-table td{
        background-color: #fff;
    }
    .clerk-table tr:hover {
        background-color: #A6A6A6;
    }
    .action-link {
        padding: 10px 20px;
        margin: 5px;
        border: none;
        cursor: pointer;
        font-size: 16px;
        text-decoration: none;
        display: inline-block;
        color: white;
    }
    .add-link {
        background-color: #4CAF50;
    }
    .drop-link {
        background-color: #f44336;
    }
    .button-container {
        margin: 20px;
        text-align: center;
    }
    .clerk-table tr th, 
    .clerk-table tr td {
        text-align: center;
    }
    </style>
</head>
<body>
    <input type="checkbox" id="checkbox">
    <div class="header">
        <label for="checkbox">
            <i id="navbtn" class="fa fa-bars" aria-hidden="true"></i>
        </label>
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
                    <a href="ClerkDashboard.php">
                        <i class="fa fa-home" aria-hidden="true"></i>
                        <span>DASHBOARD</span>
                    </a>
                </li>
                <li>
                    <a href="ClerkProfile.php">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        <span>PROFILE</span>
                    </a>
                </li>
                <li style="border-bottom: 1px solid grey;">
                    <a href="listclerk.php">
                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                        <span>LIST OF CLERK</span>
                    </a>
                </li>
                <li style="border-bottom: 1px solid grey;">
                    <a href="logout.php">
                        <i class="fa fa-sign-out" aria-hidden="true"></i>
                        <span>LOGOUT</span>
                    </a>
                </li>
            </ul>
        </nav>
        <section class="section-1">
            <div class="circled-menu-parent">
                <p><i class="fa fa-th-large"></i>List of Clerk</p>
            </div>
            <table class="clerk-table">
                <thead>
                    <tr>
                        <th>Clerk ID</th>
                        <th>Clerk Name</th>
                        <th>Clerk Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clerks as $clerk): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($clerk['id']); ?></td>
                        <td><?php echo htmlspecialchars($clerk['name']); ?></td>
                        <td><?php echo htmlspecialchars($clerk['email']); ?></td>
                        <td>
                            <a href="addclerk.php?id=<?php echo htmlspecialchars($clerk['id']); ?>" class="action-link add-link">Add</a>
                            <a href="dropclerk.php?id=<?php echo htmlspecialchars($clerk['id']); ?>" class="action-link drop-link">Drop</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="button-container">
                <a href="addclerk.php" class="action-link add-link">Add Clerk</a>
                <a href="dropclerk.php" class="action-link drop-link">Drop Clerk</a>
            </div>
        </section>
    </div>
</body>
</html>