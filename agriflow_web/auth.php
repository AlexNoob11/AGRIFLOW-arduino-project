<?php
session_start(); // Start the session to remember the user

$host = "localhost";
$user = "root";
$pass = "";
$db   = "agriflow_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    if ($action == "register") {
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (fullname, email, password) VALUES ('$fullname', '$email', '$hashed_pass')";
        
        if ($conn->query($sql) === TRUE) {
            // Registration success: Go back to index with a success message
            header("Location: index.php?status=registered");
            exit();
        } else {
            header("Location: index.php?status=error");
            exit();
        }
    } 
    
    else if ($action == "login") {
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Login success: Save user info to Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['fullname'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                header("Location: index.php?status=wrongpassword");
                exit();
            }
        } else {
            header("Location: index.php?status=usernotfound");
            exit();
        }
    }
}
$conn->close();
?>