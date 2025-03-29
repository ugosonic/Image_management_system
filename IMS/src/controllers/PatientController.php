<?php
require_once '../models/Patient.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $title = $_POST['title'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $dob = $_POST['dob'];
    $condition = $_POST['condition'];
    $password = $_POST['password'];

    $patient = new Patient();
    if ($patient->register($name, $title, $email, $phone, $address, $dob, $condition, $password)) {
        header("Location: /IMS/public/patient_registration.php?success=1");
        exit();
        
        
    } else {
        // Redirect on error
        header("Location: /IMS/public/patient_registration.php?error=1");
        exit();
    }
}
?>
