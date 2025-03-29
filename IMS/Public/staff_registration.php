<?php
if (isset($_GET['success'])) echo "<p>Registration successful!</p>";
if (isset($_GET['error'])) echo "<p>Error during registration. Please try again.</p>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Registration</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="form-container">
        <h1>Staff Registration</h1>
        <form action="../src/controllers/StaffController.php" method="POST">
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

            <label for="usergroup">User Group:</label>
            <select id="usergroup" name="usergroup" required>
                
                <option value="Doctor">Doctor</option>
                <option value="Radiologist">Radiologist</option>
            </select>

            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
