<?php
require_once("../dbConnect.php");
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../welcome.php"); // Redirect to login page if not logged in
    exit;
}

$username = $_SESSION['username'];

// Pagination logic
$itemsPerPage = 9; // 3 rows * 3 columns
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Handle search query
$search = isset($_GET['search']) ? $dbCon->real_escape_string($_GET['search']) : '';

// Count total clerks
$sql = "SELECT COUNT(*) AS total FROM clerk WHERE CLERKTYPE='clerk' AND STATUS='active'";
if ($search) {
    $sql .= " AND (CLERKNAME LIKE '%$search%' OR CLERKEMAIL LIKE '%$search%')";
}
$result = $dbCon->query($sql);
$row = $result->fetch_assoc();
$totalClerks = $row['total'];
$totalPages = ceil($totalClerks / $itemsPerPage);

// Fetch clerks for the current page
$sql = "SELECT * FROM clerk WHERE CLERKTYPE='clerk' AND STATUS='active'";
if ($search) {
    $sql .= " AND (CLERKNAME LIKE '%$search%' OR CLERKEMAIL LIKE '%$search%')";
}
$sql .= " LIMIT $itemsPerPage OFFSET $offset";
$result = $dbCon->query($sql);

$clerks = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Extract the first two words from the CLERKNAME
        $fullName = $row['CLERKNAME'];
        $words = explode(' ', $fullName);
        $firstTwoWords = implode(' ', array_slice($words, 0, 2));
        
        // Assign the modified name back to the row
        $row['CLERKNAME'] = $firstTwoWords;

        // Prepend '../CLERK/' to the CLERKIMAGE path if not empty, else use default image
        if (!empty($row['CLERKIMAGE'])) {
            $row['CLERKIMAGE'] = '../CLERK/' . $row['CLERKIMAGE'];
        } else {
            $row['CLERKIMAGE'] = '../default-profile.png'; // Use your specific default image here
        }

        // Store the modified row in the clerks array
        $clerks[] = $row;
    }
}

if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    echo "<script type='text/javascript'>alert('$message');</script>";
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
    <title>List of Clerk</title>
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
        .line1{
            display: flex;
            width: 100%;
            justify-content: right;
            align-items: center;

        }
        .search-bar {
            margin-top: 20px;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 15px;
        }
        .search-bar input[type="text"] {
            flex: 1;
            border: none;
            outline: none;
            font-size: 18px;
            padding: 8px;
        }
        .search-bar button {
            background-color: transparent;
            color: grey;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            margin-left: 10px;
        }
        .search-bar i{
            font-size: 20px;
        }
        .search-bar button:hover {
            color: black;
        }
        .add-clerk {
            background-color: #4C1CD5;
            margin-right: 40px;
            margin-left: 40px;
            margin-top: 15px;
            border-radius: 15px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 15px;
            transition: background-color 0.3s; /* Added transition for background color change */
            color: white;
            text-decoration: none; /* Ensure it behaves like a button or link */
        }

        .add-clerk:hover {
            background-color: #5e28e4; /* Darker shade on hover */
            color: white; /* Ensure text remains white on hover */
        }

        .add-clerk a{
            text-decoration: none;
            color: white;
            font-size: 18px;
            padding: 10px 15px;
        }
        .add-clerk i{
            margin-right: 5px;
        }
        .list-clerk{
            width: 80%;
            background: #E7E4E4;
            padding: 30px;
            border-radius: 10px;
            margin-top: 40px;
        }
        .clerk-profile {
            width: 350px;
            background: #fff;
            display: flex;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Adding a subtle shadow */
            margin-right: 80px;
            margin-bottom: 40px;
        }

        .image-wrap{
            border-radius: 50%;
            width: 80px;
            height: 80px;
            margin-left: 10px;
        }
        .image-wrap img{
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        .clerk-profile table{
            margin-left: 20px;
            width: 200px;
            
        }
        .clerk-profile table th{
            font-size: 20px;
            font-family: "Poppins", sans-serif;
            text-align: left;
        }
        .clerk-profile table td{
            font-size: 15px;
            font-family: "Poppins", sans-serif;
            text-align: left;
            line-height: 1.5;
        }
        .clerk-profile a{
            text-decoration: none;
            font-weight: bold;
        }
        .clerk-profile a:hover{
            color: grey;
        }
        .pagination {
            display: flex;
            justify-content: right;
            margin-top: 20px;
        }
        .pagination a {
            margin: 0 5px;
            padding: 10px 20px;
            text-decoration: none;
            color: black;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            font-family: "Poppins", sans-serif;
        }
        .pagination a:hover {
            background-color: #ddd;
        }
        .pagination a.active {
            font-weight: bold;
            background-color: #aaa;
            color: white;
        }

        /* Modal Styles */
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
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
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
                <p><i class="fa fa-th-large" style="font-size:25px;"></i>List of Clerk</p>
            </div>
            <div class="line1">
    <form class="search-bar" method="GET" action="">
        <input type="text" name="search" placeholder="Search here" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="submit"><i class="fa fa-search"></i></button>
    </form>
    <div class="add-clerk">
        <a href="addclerk.php"><i class="fa fa-user-plus" aria-hidden="true"></i>Add Clerk</a>
    </div>
</div>

            <div class="list-clerk">
                <table>
                    <?php
                    $counter = 0;
                    foreach ($clerks as $clerk) {
                        if ($counter % 3 == 0) {
                            echo '<tr>';
                        }
                        echo '<td>';
                        echo '<div class="clerk-profile">';
                        echo '<div class="image-wrap">';
                        echo '<img src="' . (!empty($clerk['CLERKIMAGE']) ? htmlspecialchars($clerk['CLERKIMAGE']) : 'default-profile.png') . '" alt="Profile Picture">';
                        echo '</div>';
                        echo '<table>';
                        echo '<tr><th>' . htmlspecialchars($clerk['CLERKNAME']) . '</th></tr>';
                        echo '<tr><td><span style="color: grey;">' . htmlspecialchars($clerk['CLERKEMAIL']) . '</span></td></tr>';
                        echo '<tr><td><a href="#" class="view-profile" clerkid="' . htmlspecialchars($clerk['CLERKID']) . '">View Profile</a></td></tr>';
                        echo '</table>';
                        echo '</div>';
                        echo '</td>';
                        if ($counter % 3 == 2) {
                            echo '</tr>';
                        }
                        $counter++;
                    }
                    // Close the last row if necessary
                    if ($counter % 3 != 0) {
                        echo '</tr>';
                    }
                    ?>
                </table>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>">&laquo;</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">&raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <!-- The Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="profile-details"></div>
        </div>
    </div>
    <script>
        // Get the modal
        var modal = document.getElementById("myModal");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

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

        // When the user clicks on a "View Profile" link, open the modal and display the profile details
        var viewProfileLinks = document.getElementsByClassName("view-profile");
        for (var i = 0; i < viewProfileLinks.length; i++) {
            viewProfileLinks[i].onclick = function(event) {
                event.preventDefault();
                var clerkId = this.getAttribute("clerkid");
                // Fetch profile details using AJAX
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "fetchProfileDetails.php?clerkid=" + clerkId, true);
                xhr.onload = function() {
                    if (xhr.status == 200) {
                        document.getElementById("profile-details").innerHTML = xhr.responseText;
                        modal.style.display = "block";
                    }
                };
                xhr.send();
            };
        }
    </script>

</body>
</html>