<?php
include('connexionDB.php');

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';      
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        header("Location: signup.php?message=All fields are required.");
        exit();
    }

    $query = "SELECT * FROM client WHERE email = '$email' UNION SELECT * FROM admin WHERE email = '$email'";
    $result = mysqli_query($mysqlconnect, $query);

    if (mysqli_num_rows($result) > 0) {
        header(header: "Location: signup.php?message=Email is already registered.");
        exit();
    }

    $query = "INSERT INTO client (nom, email, password) VALUES ('$name', '$email', '$password')";
    $insertResult = mysqli_query($mysqlconnect, $query);

    if ($insertResult) {
        $clientId = mysqli_insert_id($mysqlconnect);  

        $_SESSION['client'] = $clientId; 
        $_SESSION['role'] = 'client';    
        $_SESSION['email'] = $email;     

        header("Location: client_dashboard.php");
        exit();
    } else {
        header("Location: signup.php?message=Error during signup. Please try again.");
        exit();
    }

    mysqli_close($mysqlconnect);
}
?>
