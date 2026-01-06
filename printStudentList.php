<?php
require_once("dbConnect.php");
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: welcome.php"); // Redirect to login page if not logged in
    exit;
}

// Fetch the list of students
$sql = "SELECT STUID, STUNAME, STUEMAIL FROM student";
$result = $dbCon->query($sql);

if ($result->num_rows > 0) {
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
} else {
    $students = [];
}

$dbCon->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .print-button {
            display: block;
            width: 100px;
            margin: 20px auto;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
        }
        .print-button:hover {
            background-color: #8de267;
        }
        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Student List</h1>
        <table>
            <tr>
                <th>No.</th>
                <th>Student Name</th>
                <th>Student ID</th>
                <th>Student Email</th>
            </tr>
            <?php foreach ($students as $index => $student) { ?>
            <tr>
                <td><?= $index + 1 . "."?></td>
                <td><?= htmlspecialchars($student['STUNAME']) ?></td>
                <td><?= htmlspecialchars($student['STUID']) ?></td>
                <td><?= htmlspecialchars($student['STUEMAIL']) ?></td>
            </tr>
            <?php } ?>
        </table>
        <a href="printStudentList.php" class="print-button">Print</a>
    </div>
</body>
</html>
