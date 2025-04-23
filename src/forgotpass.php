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
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="output.css">
    <link rel="icon" href="Logo.png" type="image/png">
</head>
<body class="bg-gray-200 flex justify-center">
    <div class="  mt-[2cm] border-1 bg-white border-white shadow-lg shadow-gray-300 rounded-xl h-[15cm] w-[12cm]   ">
       <h1 class="text-xl font-bold pl-8 pt-8 pb-8">
        Change Password
       </h1>
<form action = "" method="POST">


       <label for="email" class="pl-8 ">Your Email</label>
       <br>
        <input class="pl-4 border-gray-200 border-1 h-[1.2cm] rounded-xl w-[10cm] ml-9 mb-4 " type="email" id="email" name="email" required placeholder="Enter your email">

       <label for="pwd" class="pl-8 ">New Password</label>
<form action="" method="POST">
        <input class="pl-4 border-gray-200 border-1 h-[1.2cm] rounded-xl mb-4 w-[10cm] ml-9 " type="password" id="pwd" name="pwd" required placeholder="Enter your password"   pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$" oninvalid="this.setCustomValidity('Password must contain at least 8 characters, including uppercase, lowercase, and a special character.')"
        oninput="this.setCustomValidity('')" >

        <label for="pwd" class="pl-8 ">Confirm Password</label>
        <br>
        <input class="pl-4 border-gray-200 border-1 h-[1.2cm] rounded-xl w-[10cm] ml-9  mb-7" type="password" id="pwd" name="pwd" required placeholder="Confirm your password"   pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$" oninvalid="this.setCustomValidity('Password must contain at least 8 characters, including uppercase, lowercase, and a special character.')"
        oninput="this.setCustomValidity('')">

      <div class="pl-9 pt-10">
        
          <input  type="checkbox" id="T and C" name="T and C" value="T and C" required>
   <label for="T and C"> I acept the <a class="text-blue-800" href = "">Terms and Conditions</a> </label>
      </div>

      <label for = "Change Password">
          <button class="bg-blue-800  text-white rounded-xl h-[1.2cm] w-[10cm] ml-9 mt-4 hover:cursor-pointer hover:shadow-2xl" value = "Change Password">
         
          Change Password
          
        </button>
      </label>
    </form>
    </div>
    
</body>
</html>    
<?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];
        $newPassword = $_POST['pwd'];
        $confirmPassword = $_POST['pwd'];
    
        if ($newPassword !== $confirmPassword) {
            echo "<script>alert('Passwords do not match!');</script>";
        } else {
            $email = mysqli_real_escape_string($conn, $email);
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
            $query = "SELECT * FROM users WHERE email = '$email'";
            $result = mysqli_query($conn, $query);
    
            if (mysqli_num_rows($result) > 0) {
                $updateQuery = "UPDATE users SET password = '$hashedPassword' WHERE email = '$email'";
                if (mysqli_query($conn, $updateQuery)) {
                  echo "<script>window.location.href = 'passchanged.php';</script>";
                  echo "<script>
                    setTimeout(function() {
                      window.location.href = 'index.php';
                    }, 3000);
                  </script>";
                } else {
                  echo "<script>alert('Error updating password.');</script>";
                }
            } else {
                echo "<script>alert('Email not found in the database.');</script>";
            }
        }
    }
    ?>
    </form>
