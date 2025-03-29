<?php
require_once '../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = new User();
    $userData = $user->authenticate($email, $password);

    if ($userData) {
        session_start();

        // Store common session details
        $_SESSION['patient_id'] = $userData['id'];
        $_SESSION['usergroup'] = $userData['usergroup'];
        $_SESSION['name'] = $userData['name'];

        // Redirect based on user group
        switch ($userData['usergroup']) {
            case 'Patient':
                header('Location: /ims/public/patient_dashboard.php');
                break;
            case 'Doctor':
                header('Location: /ims/public/doctor_dashboard.php');
                break;
            case 'Radiologist':
                header('Location: /ims/public/radiologist_dashboard.php');
                break;
            default:
                // Unknown user group, redirect to error page
                header('Location: /ims/public/login.php?error=invalid_usergroup');
                break;
        }
        exit();
    } else {
        // Invalid credentials
        header('Location: /ims/public/login.php?error=invalid_credentials');
        exit();
    }
}
?>
