<?php
require_once("../dbConnect.php");

$clerkId = $_GET['clerkid'];

// Use a prepared statement to prevent SQL injection
$stmt = $dbCon->prepare("SELECT * FROM clerk WHERE CLERKID = ?");
$stmt->bind_param("i", $clerkId);
$stmt->execute();
$result = $stmt->get_result();

$clerk = $result->fetch_assoc(); // Fetch single clerk row

$dbCon->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap">
    <title>Print Clerk Profile</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #f9f9f9;
        }
        .header {
            text-align: center;
            background-color: #607D3B;
            color: #fff;
            padding: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 25px;
            margin: 0;
        }
        .image-wrap {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 20px;
        }
        .image-wrap img {
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
        .info-school {
            text-align: right;
            margin-bottom: 20px;
        }
        .info-school p {
            font-size: 12px;
            margin: 0;
        }
        .header2 {
            text-align: center;
            margin-bottom: 20px;
            color: black;
        }
        .header2 h2 {
            font-size: 22px;
            margin: 10px 0;
        }
        .image-profile {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 40px;
            margin-top: 40px;
        }
        .image-profile img {
            width: 150px;
            height: 150px;
            border: 3px solid #ccc;
            border-radius: 15px;
            object-fit: cover;
        }
        .profile-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
            text-align: left;
        }
        .profile-container h2 {
            font-size: 28px;
            margin-bottom: 20px;
        }
        .profile-container p {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .profile-container p strong {
            display: block;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: left;
        }
        table td {
            text-align: left;
        }
        .print-button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .print-button:hover {
            background-color: #0056b3;
        }
        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>EMPLOYEE PROFILE</h1>
    </div>
    <div class="image-wrap">
        <img src="../logo.png" alt="School Logo">
    </div>
    <div class="info-school">
        <p>
            <i class="fa fa-phone" aria-hidden="true"></i> 09-773 7300 <br>
            <i class="fa fa-phone" aria-hidden="true"></i> 09-773 7303 <br>
            <i class="fa fa-envelope-o" aria-hidden="true"></i> mstg@yik.edu.my
        </p>
    </div>
    <div class="header2">
        <hr>
        <h2>PERSONAL PARTICULAR</h2>
        <hr>
    </div>
    <div class="image-profile">
        <img src="<?php echo !empty($clerk['CLERKIMAGE']) ? '../CLERK/' . htmlspecialchars($clerk['CLERKIMAGE']) : '../default-profile.png'; ?>" alt="Profile Picture">
    </div>
    <div class="profile-container">
        <?php if ($clerk): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <td><?php echo htmlspecialchars($clerk['CLERKID']); ?></td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td><?php echo htmlspecialchars($clerk['CLERKNAME']); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo htmlspecialchars($clerk['CLERKEMAIL']); ?></td>
                </tr>
                <tr>
                    <th>Phone Number</th>
                    <td><?php echo htmlspecialchars($clerk['CLERKPNO']); ?></td>
                </tr>
                <tr>
                    <th>Date of Birth</th>
                    <td><?php echo htmlspecialchars($clerk['CLERKDOB']); ?></td>
                </tr>
            </table>
        <?php else: ?>
            <p>No profile details found for this clerk.</p>
        <?php endif; ?>
        <button class="print-button" onclick="window.print()">Print</button>
    </div>
</body>
</html>
