<?php
if (isset($_GET['success'])) echo "<p>Registration successful!</p>";
if (isset($_GET['error'])) echo "<p>Error during registration. Please try again.</p>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="form-container">
        <h1>Patient Registration</h1>
        <form action="../src/controllers/PatientController.php" method="POST">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone">

            <label for="address">Address:</label>
            <textarea id="address" name="address" required></textarea>

            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" required>

            <label for="condition">Medical Condition (optional):</label>
            <input type="text" id="condition" name="condition">

            <button type="submit">Register</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
