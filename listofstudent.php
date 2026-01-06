<?php
require_once("dbConnect.php");
session_start();

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

$sqlPrincipal = "SELECT MAX(PRINCIPALID) FROM principal";
$stmtPrincipal = $dbCon->prepare($sqlPrincipal);
$stmtPrincipal->execute();
$stmtPrincipal->bind_result($principalId);
$stmtPrincipal->fetch();
$stmtPrincipal->close();

// Pagination setup
$limit = 10; // Number of entries per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Get the total number of students
$sql = "SELECT COUNT(*) FROM student";
$stmt = $dbCon->prepare($sql);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

$total_pages = ceil($total / $limit);

$sql = "SELECT s.STUID, s.STUNAME, s.STUEMAIL, r.STATUS, r.CLERKID FROM student as s JOIN registration as r ON s.STUID = r.STUID WHERE r.REGSTATUS = 'active' ORDER BY r.STATUS DESC LIMIT ?, ? ";
$stmt = $dbCon->prepare($sql);
$stmt->bind_param("ii", $start, $limit);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentId = $_POST['id'];
    $newStatus = $_POST['status'];

    $sql = "UPDATE registration SET STATUS = ?, CLERKID = ?, PRINCIPALID = ? WHERE STUID = ?";
    $stmtUpdate = $dbCon->prepare($sql);
    $stmtUpdate->bind_param("ssss", $newStatus, $username, $principalId, $studentId);

    if ($stmtUpdate->execute()) {
        // Function to add notification
        function addNotification($dbCon, $studentId, $message) {
            $sql = "INSERT INTO notifications (STUID, MESSAGE) VALUES (?, ?)";
            $stmt = $dbCon->prepare($sql);
            $stmt->bind_param("ss", $studentId, $message);
            $stmt->execute();
            $stmt->close();
        }

        // Check the registration status and add notification
        if ($newStatus === 'Approved') {
            addNotification($dbCon, $studentId, 'Your registration has been approved.');
        } elseif ($newStatus === 'Rejected') {
            addNotification($dbCon, $studentId, 'Your registration has been rejected.');
        }

        echo 'success';
    } else {
        echo 'error: ' . $stmtUpdate->error; // Log the error
    }

    $stmtUpdate->close();
    exit; // Ensure no further code is executed after updating the status
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap">
    <title>List of Students</title>
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
    justify-content: space-between; /* Adjusted for alignment */
    align-items: center; /* Center vertically */
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
    width: 80%;
    border-collapse: collapse;
    margin-top: 40px;
}
table th, table td{
    padding: 12px;
    font-weight: bold;
}
table th{
    background-color: #48332E;
    color: white;
}
table tr:nth-child(even) {
    background-color: white; /* Light grey background for even rows */
}
table tr:nth-child(odd) {
    background-color: #BDB8B8; /* Light grey background for even rows */
}
table tr[data-status="Pending"] {
    background-color: #ffdddd; /* Optional: highlight pending rows */
}

table tr[data-status="Approved"] {
    background-color: #ddffdd; /* Optional: highlight approved rows */
}
.action-icons{
    display: flex;
    justify-content: center; /* Center the icons horizontally */
    gap: 10px;
}
.action-icons a{
    text-decoration: none;
    color: #000;
    font-size: 20px;
}
.action-icons a:hover{
    color: #127b8e;
}
#printbtn {
    background-color:#edbea3;
    color: white;
    padding: 5px 10px;
    border: none;
    border-radius: 5px;
    
    cursor: pointer;
}
#printbtn:hover {
    background-color: #BF612D; 
}

select {
    padding: 3.3px 30px;
    border: 1px solid #ccc;
    border-radius: 5px;

}
select:focus {
    outline: none;
}
#updateSttsbtn{
    padding: 5px 15px;
    border: none;
    border-radius: 5px;
    background-color: #BF612D;
    color: white;
    cursor: pointer;
    margin-left: 5px;
}
#updateSttsbtn:hover {
    background-color: #48332E;
}
.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.pagination a {
    color: black;
    padding: 8px 16px;
    text-decoration: none;
    transition: background-color .3s;
    border: 1px solid #ddd;
    margin: 0 4px;
}

.pagination a:hover {
    background-color: #ddd;
}
.add-student {
            background-color: #f0ad4e;
            margin-left: 1100px;
            margin-top: 40px;
            border-radius: 15px;
            display: :flex;
            align-items: center;
            justify-content: center;
            padding: 15px 15px;
            transition: background-color 0.3s; /* Added transition for background color change */
            color: white;
            text-decoration: none; /* Ensure it behaves like a button or link */
        }

        .add-student:hover {
            background-color: ##cf9a4e; /* Darker shade on hover */
            color: white; /* Ensure text remains white on hover */
        }

        .add-student a{
            text-decoration: none;
            color: white;
            font-size: 18px;
            padding: 10px 15px;
        }
        .add-student i{
            margin-right: 5px;
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
                <p><i class="fa fa-th-large" style="font-size:25px;"></i>List of Student</p>
                <a href="printStudentList.php" id="printbtn" style="text-decoration: none; color: #000; text-align: end;">Print<i class="fa fa-print" style="font-size:25px;"></i></a>
            </div>
            <div class="add-student">
                <a href="addstu.php"><i class="fa fa-user-plus" aria-hidden="true"></i>Add Student</a>
            </div>
            <table>
                <tr>
                    <th>#</th>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php
                if ($result->num_rows > 0) {
                    $count = $start + 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td style='text-align: center'>" . $count++ . "</td>";
                        echo "<td style='text-align: center'>" . htmlspecialchars($row['STUID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['STUNAME']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['STUEMAIL']) . "</td>";
                        echo "<td style='text-align: center'>";
                        if ($row['STATUS'] == 'Pending' || $row['STATUS'] == 'Rejected') {
                            echo "<select class='status-dropdown' data-id='" . htmlspecialchars($row['STUID']) . "'>";
                            echo "<option value='Pending'" . ($row['STATUS'] == 'Pending' ? ' selected' : '') . ">Pending</option>";
                            echo "<option value='Approved'>Approve</option>";
                            echo "<option value='Rejected'" . ($row['STATUS'] == 'Rejected' ? ' selected' : '') . ">Reject</option>";
                            echo "</select>";
                            echo "<button id='updateSttsbtn' onclick='updateStatus(\"" . htmlspecialchars($row['STUID']) . "\")'>Update</button>";
                        } else {
                                echo htmlspecialchars($row['STATUS']);
                            }
                        echo "</td>";
                        echo "<td class='action-icons'>";
                        echo "<a href='viewstudent.php?id=" . htmlspecialchars($row['STUID']) . "'><i class='fa fa-eye' aria-hidden='true'></i></a>";
                        echo "<a href='editStudent.php?id=" . htmlspecialchars($row['STUID']) . "'><i class='fa fa-pencil' aria-hidden='true'></i></a>";
                        echo "<a href='printStudentReport.php?id=" . htmlspecialchars($row['STUID']) . "'><i class='fa fa-print' aria-hidden='true'></i></a>";
                        echo "<a href='javascript:void(0);' onclick='deleteStudent(\"" . htmlspecialchars($row['STUID']) . "\", \"" . htmlspecialchars($row['STUNAME']) . "\");'><i class='fa fa-trash' aria-hidden='true'></i></a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align: center;'>No student's record found</td></tr>";
                }
                ?>
            </table>
            <div class="pagination">
                <?php
                if ($page > 1) {
                    echo '<a href="?page=' . ($page - 1) . '">Previous</a>';
                }
                for ($i = 1; $i <= $total_pages; $i++) {
                    echo '<a href="?page=' . $i . '">' . $i . '</a>';
                }
                if ($page < $total_pages) {
                    echo '<a href="?page=' . ($page + 1) . '">Next</a>';
                }
                ?>
            </div>
        </section>
    </div>

    <script>
    function deleteStudent(studentId, studentName) {
        Swal.fire({
            title: "Are you sure?",
            text: "You are about to delete " + studentName + "'s record. This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'deleteStudent.php?id=' + studentId;
            }
        });
    }

    function updateStatus(studentId) {
        const selectElement = document.querySelector(`.status-dropdown[data-id='${studentId}']`);
        const newStatus = selectElement.value;

        fetch('listofstudent.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${studentId}&status=${newStatus}`
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === 'success') {
                // Reload the page immediately to see the updated status
                location.reload();
            } else {
                // Show an alert if the update failed
                console.error('Failed to update status.');
                alert('Failed to update status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the status.');
        });
    }


    // Sort the Rows
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.querySelector('table');
        const rows = Array.from(table.rows).slice(1); // Exclude the header row

        function sortRows() {
            const table = document.querySelector('table');
            const rows = Array.from(table.rows).slice(1); // Exclude the header row

            rows.sort((a, b) => {
                const statusA = a.getAttribute('data-status');
                const statusB = b.getAttribute('data-status');
                if (statusA === 'Pending' && statusB === 'Approved') return -1;
                if (statusA === 'Approved' && statusB === 'Pending') return 1;
                return 0;
            });

            rows.forEach(row => table.appendChild(row));
        }

        // Call sorting function initially
        sortRows();
    });
    </script>
</body>
</html>
