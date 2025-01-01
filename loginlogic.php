<?php
include('connexionDB.php');

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (empty($email) || empty($password) || empty($role)) {
        header("Location: login.php?message=All fields are required.");
        exit();
    }

    if ($role === 'client') {
        $query = "SELECT * FROM client WHERE email = '$email'";
        $result = mysqli_query($mysqlconnect, $query);

        if ($result) {
            $client = mysqli_fetch_assoc($result);

            if ($client) {
                if ($password == $client['password']) {
                    $_SESSION['client'] = $client['id'];  // Store client ID in session
                    $_SESSION['role'] = 'client';  // Store role in session
                    $_SESSION['email'] = $client['email'];  // Store email in session
                    header("Location: client_dashboard.php?message=Client login successful");
                    exit();
                } else {
                    header("Location: login.php?message=Incorrect password for client");
                    exit();
                }
            } else {
                header("Location: login.php?message=Client not found");
                exit();
            }
        }
    } elseif ($role === 'admin') {
        $query = "SELECT * FROM admin WHERE email = '$email'";
        $result = mysqli_query($mysqlconnect, $query);

        if ($result) {
            $admin = mysqli_fetch_assoc($result);

            if ($admin) {
                if ($password == $admin['password']) {
                    $_SESSION['user_id'] = $admin['id'];  // Store admin ID in session
                    $_SESSION['role'] = 'admin';  // Store role in session
                    $_SESSION['email'] = $admin['email'];  // Store email in session
                    header("Location: admin_dashboard.php?message=Admin login successful");
                    exit();
                } else {
                    header("Location: login.php?message=Incorrect password for admin");
                    exit();
                }
            } else {
                header("Location: login.php?message=Admin not found");
                exit();
            }
        }
    } else {
        header("Location: login.php?message=Invalid role selected");
        exit();
    }
}

if (isset($result)) {
    mysqli_free_result($result);
}
mysqli_close($mysqlconnect);
?>
