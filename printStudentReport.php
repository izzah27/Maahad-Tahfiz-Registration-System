<!DOCTYPE html>
<html>
    <head>
        <?php
            require_once('SRDetails.php');
        ?>
        <script >
            window.print();
        </script>
        <title>Print Order Statement</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
        <style>
            .container {
                padding: 20px;
                max-width: 800px;
                width: fit-content;
                max-height: 900px;
                height: 100%;
                margin: auto;
                font-family: "Poppins", sans-serif;
            }
            .section {
                padding: 10px;
                height: 900px;
              
            }
            .section table {
                width: 100%;
                height: 90%;
                border-collapse: collapse;
                background-color: white;
            }
            .section th, .section td {
                padding: 3px;
                text-align: left;
            }
            .section th {
                background-color: #f0f0f0;
            }
            .section td.all-b {
                border: 2px solid black;
                border-right: 0px solid black;
                text-align: center;
                margin: 0;
            }
            .section td.all-bc {
                border:2px solid black;
                border-left: 0px solid black;
                text-align: end;
                margin: -20%;
            }
            .section td.all-c {
                border-right:2px solid black;
                text-align: end;
                margin: 0;
            }
            .section td.right-b {
                border-top: 2px solid black;
                border-bottom: 2px solid black;
                border-left: 2px solid black;
            }
            .section td.left-b {
                border-top: 2px solid black;
                border-bottom: 2px solid black;
                border-right: 2px solid black;
            }
            .section td.bottom-b {
                border-top: 2px solid black;
                border-right: 2px solid black;
                border-left: 2px solid black;
            }
            .section td.bottom-c {
                border-top: 2px solid black;
                border-right: 2px solid black;
                border-left: 2px solid black;
                text-align: center;
            }
            .section td.top-b {
                border-bottom: 2px solid black;
                border-right: 2px solid black;
                border-left: 2px solid black;
            }
            .section td.topright-b {
                border-bottom: 2px solid black;
                border-left: 2px solid black;
            }
            .section td.topbotright-b {
                border-left: 2px solid black;
            }
            .section td.topleft-b {
                border-bottom: 2px solid black;
                border-right: 0px solid black;
            }
            .section td.topleft-c {
                border-bottom: 2px solid black;
                border-right: 2px solid black;
            }
            
            
            .section td.topbotleft-b {
                border-right: 0px solid black;
            }
            .section td.topbotleft-c {
                border-right: 2px solid black;
                text-align: start;
            }
            .section tr.details{
                font-size: small;
            }
            .logo {
                height: 100px;
                width: 100px;
                margin-left: 25%;
            }
            .profileImage {
                height: 250px;
                width: auto;
            }
            #ref {
                text-align: end;
                font-size: 8px;
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
    </head>
    <body>
    <div class="container">
    <div class="section">
        <table>
            <tr>
                <td class="all-b" colspan="2"><h2>“MELAHIRKAN PELAJAR YANG BERPRESTASI TINGGI DAN<br> MAMPU MEMIMPIN UMMAH BERDASARKAN AL-QURAN DAN AS-SUNNAH”</h2></td>
                <td class="all-bc"><img class="logo" src="logo.png"><br><p id="ref">58, Jalan Maktab, Taman Orkid, <br>16100 Kota Bharu, Kelantan</p>
                <p id="ref">09-773 7300</p></td>
            </tr>
            <tr>
                <td class="bottom-b" colspan="3" style="font-weight:bold; font-size:medium; text-align:center;">STUDENT'S INFORMATION</td>
                
                
                
            </tr>
            <tr class="details">
                <td class="topbotright-b">Student ID: </td>
                <td ><?php echo $stuID;?></td>
                <td class="all-c" rowspan="6" style="text-align: center; padding-left:10px; padding-left:10px; padding-right:10px;"><img class="profileImage" src="<?php echo !empty($stuImage) ? 'STUDENT/' . htmlspecialchars($stuImage) : 'default-profile.png'; ?>" alt="Profile Picture"></td>
                
                
            </tr>
            
            <tr class="details">
                <td class="topbotright-b" >Name:</td>
                <td class="topbotleft-b"><?php echo $stuName; ?></td>
            </tr>
            <tr class="details">
                <td class="topbotright-b" >Phone No:</td>
                <td class="topbotleft-b"><?php echo $stuPNO; ?></td>
            </tr>
            <tr class="details">
                <td class="topbotright-b">Address:</td>
                <td class="topbotleft-b"><?php echo $stuAddress; ?></td>
            </tr>
            <tr class="details">
                <td class="topbotright-b" >Date Of Birth:</td>
                <td class="topbotleft-b"><?php // Creating timestamp from given date
                                            $timestamp = strtotime($stuDOB);
                                            
                                            // Creating new date format from that timestamp
                                            $new_date = date("d-m-Y", $timestamp);
                                            echo $new_date;  ?></td>
            </tr>
            <tr class="details">
                <td class="topright-b" >Gender:</td>
                <td class="topleft-b"><?php echo $stuGender; ?></td>
            </tr>
            <tr>
                <td class="bottom-c" colspan="3" style="font-weight:bold; font-size:medium;">PARENTS' INFORMATION</td>
            </tr>
            <tr class="details">
                <td class="topbotright-b" >Father Name:</td>
                <td class="topbotleft-c" colspan="2"><?php echo $stuFatherName; ?></td>
            </tr>
            <tr class="details">
                <td class="topbotright-b" >Mother Name:</td>
                <td class="topbotleft-c" colspan="2"><?php echo $stuMotherName; ?></td>
            </tr>
            <tr class="details">
                <td class="topright-b">Parents' Total Salary: </td>
                <td class="topleft-c" colspan="2"><?php echo "RM ". $stuParentsSalary; ?></td>
            </tr>
            <tr></tr>
        </table>
        <a href="printStudentReport.php?id=<?php echo $stuID; ?>"  class="print-button">Print</a>
    </div>
    </div>
    </body>
</html>