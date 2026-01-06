<?php
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {

    $param_id = trim($_GET["id"]);
    // Include dbConnect file
    require_once "dbConnect.php";
    
    // Prepare a select statement
    $sql = "SELECT * FROM STUDENT WHERE stuid = ?";
    
    if ($stmt = mysqli_prepare($dbCon, $sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        
        // Set parameters
      
        
        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                
                $stuID = $row["STUID"];
                $stuName = $row["STUNAME"];
                $stuEmail = $row["STUEMAIL"];
                $stuPNO = $row["STUPNO"];
                $stuAddress = $row["STUADDRESS"];
                $stuGender = $row["STUGENDER"];
                $stuDOB = $row["STUDOB"];
                $stuFatherName = $row["FATHERNAME"];
                $stuMotherName = $row["MOTHERNAME"];
                $stuParentsSalary = $row["SALARY"];
                $stuImage = $row["STUIMAGE"];
            } else {
                exit();
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
    mysqli_free_result($result);
    mysqli_close($dbCon);
} else {
    exit();
}