<?php
require_once '../models/Staff.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $title = $_POST['title'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $usergroup = $_POST['usergroup'];
    $password = $_POST['password'];

    $staff = new Staff();
    if ($staff->register($name, $title, $email, $phone, $usergroup, $password)) {
        header("Location: /public/staff_registration.php?success=1");
        exit();
    } else {
        header("Location: /public/staff_registration.php?error=1");
        exit();
    }
}
?>
