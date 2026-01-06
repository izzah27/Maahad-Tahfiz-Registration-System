<?php
require_once("dbConnect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    session_start();

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $userType = $_POST["userType"];

    if ($userType == "student") {
        $_SESSION['userType'] = $userType;
        $sql = "SELECT STUID, STUPASSWORD FROM STUDENT WHERE STUID = ?";
        if ($stmt = mysqli_prepare($dbCon, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $username);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if ($result && mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    if ($password == $row['STUPASSWORD']) {
                        $_SESSION['loggedin'] = true;
                        $_SESSION['username'] = $row['STUID'];
                        header("location: home.php");
                        exit();
                    } else {
                        echo "<script>alert('Invalid username or password. Please try again.');</script>";
                    }
                } else {
                    echo "<script>alert('Invalid username or password. Please try again.');</script>";
                }
            } else {
                echo "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    } 
    else if ($userType == "clerk") {
        $sql = "SELECT CLERKID, CLERKPASSWORD, CLERKTYPE FROM CLERK WHERE CLERKID = ?";
        if ($stmt = mysqli_prepare($dbCon, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $username);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if ($result && mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    if ($password == $row['CLERKPASSWORD']) {
                        $_SESSION['loggedin'] = true;
                        $_SESSION['username'] = $row['CLERKID'];
                        if ($row['CLERKTYPE'] == 'admin') {
                            $_SESSION['userType'] = $row['CLERKTYPE'];
                            header("location: ADMIN/AdminDashboard.php");
                        } else {
                            $_SESSION['userType'] = $row['CLERKTYPE'];
                            header("location: ClerkDashboard.php");
                        }
                        exit();
                    } else {
                        echo "<script>alert('Invalid username or password. Please try again.');</script>";
                    }
                } else {
                    echo "<script>alert('Invalid username or password. Please try again.');</script>";
                }
            } else {
                echo "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    else if ($userType == "principal") {
        $sql = "SELECT PRINCIPALID, PRINCIPALPASS FROM PRINCIPAL WHERE PRINCIPALID = ?";
        if ($stmt = mysqli_prepare($dbCon, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $username);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if ($result && mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    if ($password == $row['PRINCIPALPASS']) {
                        $_SESSION['loggedin'] = true;
                        $_SESSION['username'] = $row['PRINCIPALID'];
                        header("location: PRINCIPAL/PrincipalDashboard.php");
                        exit();
                    } else {
                        echo "<script>alert('Invalid username or password. Please try again.');</script>";
                    }
                } else {
                    echo "<script>alert('Invalid username or password. Please try again.');</script>";
                }
            } else {
                echo "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($dbCon);
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Arial:wght@700&display=swap" />
        <title>Login</title>
    </head>
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }
        body {
            width: 100%;
            background-color: #E2E0E0;
            background-size: cover;
            display: flex;
            align-items: center;
            flex-direction: column;
        }
        .wrapper {
            width: 600px;
            height: 600px;
            margin-top: 6%;
            background-color: white;
            border-radius: 30px;
            box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
        }
        .wrapper h1 {
            text-align: center;
            font-weight: 200;
            padding-top: 50px;
            padding-bottom: 40px;
            font-size: 60px;
            letter-spacing: 0.05em;
        }
        .wrapper i {
            color: grey;
            font-size: 35px;
            margin-left: 17px;
            margin-right: 15px;
            margin-bottom: 15px;
            text-align: center;
        }
        .wrapper label {
            font-family: 'Arial';
            letter-spacing: 0.02em;
            font-size: 23px;
            font-weight: 600;
            margin-left: 95px;
            color: rgba(0, 0, 0, 0.575);
            font-weight: 600;
        }
        .wrapper input[type="text"],
        .wrapper input[type="password"] {
            height: 50px;
            width: 390px;
            margin-left: 110px;
            background-color: #4d4a4a67;
            padding: 20px;
            border-radius: 5px;
            border: none;
            outline: none;
        }
        .wrapper input[type="text"] {
            margin-bottom: 20px;
        }
        .wrapper input[type="password"] {
            margin-bottom: 15px;
        }
        .wrapper input[type="submit"] {
            height: 50px;
            width: 390px;
            margin-left: 110px;
            margin-bottom: 27px;
            margin-top: 13px;
            background-color: #D09B35;
            border-radius: 5px;
            font-size: 20px;
            letter-spacing: 0.02em;
            font-family: 'Arial';
            font-weight: bold;
            color: rgba(0, 0, 0, 0.699);
            border: none;
            cursor: pointer;
        }
        .wrapper input[type="submit"]:hover {
            background-color: #e4a634;
            text-decoration: underline;
        }
        .wrapper a {
            margin-left: 227px;
            text-decoration: none;
            color: black;
            font-family: 'Inter';
            font-size: 20px;
            display: flex;
        }
        .line1,
        .line2 {
            border: 1px solid;
            width: 100px;
            display: inline-block;
            align-items: center;
            justify-content: center;
        }
        .line1 {
            margin-top: 27px;
            margin-left: 185px;
        }
        .line2 {
            margin-top: 27px;
        }
        .wrapper p {
            display: inline-block;
            text-align: center;
        }
        .dont-have-account {
            margin-top: 30px;
            justify-content: center;
            text-align: center;
        }
        .dont-have-account p {
            display: inline-block;
            font-size: 18px;
        }
        .dont-have-account a {
            display: inline-block;
            margin-left: 10px;
        }
        .dont-have-account a:hover {
            text-decoration: underline;
        }
        .wrapper .user-type {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .wrapper .user-type label {
            margin: 0 15px;
            font-size: 20px;
            font-weight: normal;
        }
        .wrapper .user-type input[type="radio"] {
            margin-right: 10px;
        }
    </style>

    <body>
        <div class="wrapper">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <h1>LOGIN</h1> 

                <div class="user-type">
                    <label for="student">
                        <input type="radio" id="student" name="userType" value="student" required> Student
                    </label>
                    <label for="clerk">
                        <input type="radio" id="clerk" name="userType" value="clerk" required> Clerk
                    </label>
                    <label for="principal">
                        <input type="radio" id="principal" name="userType" value="principal" required> Principal
                    </label>
                </div>

                <label for="username"><i class="fa fa-user" aria-hidden="true"></i>Username</label><br>
                <input type="text" name="username" id="username" required ><br>

                <label for="password"><i class="fa fa-lock" aria-hidden="true"></i>Password</label><br>
                <input type="password" name="password" id="password" required ><br>

                <input type="submit" name="submit" value="LOGIN">
            </form>
        </div>
    </body>
</html>