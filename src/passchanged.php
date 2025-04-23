<?php
session_start();
include "connection.php";
$conn = mysqli_connect($servername, $username, $password, $dbname);
//To change the password of the user
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
} else {
    mysqli_set_charset($conn, "utf8mb4");
}

$email = $_SESSION['email'] ?? null;
if (isset($_SESSION['enrollment_id'])) {
    $enrollment_id = $_SESSION['enrollment_id'];
} else {
    $enrollment_id = null;
}
$enrollment_id = $_SESSION['enrollment_id'] ?? null;
// redirect the user to login page afeter 3 seconds
if (isset($_SESSION['email']) || isset($_SESSION['enrollment_id'])) {
    header("refresh:2;url=index.php");
} else {
    header("Location: login.php");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="output.css">
    <link rel="icon" href="Logo.png" type="image/png">
</head>
<body class="bg-gray-200 flex justify-center">
    <div class="bg-white w-1/3 mt-45 rounded-xl h-[10cm] shadow-gray-300 shadow-xl p-5">
        <h1 class="text-3xl text-center font-bold text-gray-800">
            Password Changed Succesfully
        </h1>
        <hr class="mt-5">
        <img src="success1.png" alt="success" class="mx-auto mt-5 rounded-full h-[3cm] w-[3cm] hover:cursor-pointer">
        <p class="text-center text-gray-700 mt-10">
            Your password has been changed successfully. Please login with your new password.
        </p>
    </div>
</body>
</html>