<?php
require_once("../dbConnect.php");
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: ../welcome.php"); // Redirect to login page if not logged in
    exit;
}

$clerkId = $_GET['clerkid'];

// Use a prepared statement to prevent SQL injection
$stmt = $dbCon->prepare("UPDATE clerk SET STATUS = 'inactive' WHERE CLERKID = ?");
$stmt->bind_param("i", $clerkId);

if ($stmt->execute()) {
    $message = "Clerk deleted successfully.";
} else {
    $message = "Error deleting clerk: " . $stmt->error;
}

$stmt->close();
$dbCon->close();

header("Location: listofclerk.php?message=" . urlencode($message));
exit;
?>
