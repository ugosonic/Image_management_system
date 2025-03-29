<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '../../config/database.php';
$db = new Database();
$pdo = $db->connect();

// Initialize variables
$isLoggedIn = false;
$usergroup = null;
$displayName = '';
$displayID = '';

// Base User class implementing the UserInterface
interface UserInterface {
    public function getDisplayName();
    public function getDisplayID();
}

class User implements UserInterface {
    protected $pdo;
    protected $id;
    protected $name;

    public function __construct($pdo, $id, $name) {
        $this->pdo = $pdo;
        $this->id = $id;
        $this->name = $name;
    }

    public function getDisplayName() {
        return $this->name ?: 'Unknown';
    }

    public function getDisplayID() {
        return $this->id ?: '---';
    }
}

// Patient class extending User
class Patient extends User {
    public function __construct($pdo, $patientId) {
        $stmt = $pdo->prepare("SELECT name FROM patients WHERE patient_id = :pid LIMIT 1");
        $stmt->execute([':pid' => $patientId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        parent::__construct($pdo, $patientId, $row['name'] ?? null);
    }
}

// Staff class extending User
class Staff extends User {
    public function __construct($pdo, $staffName) {
        $stmt = $pdo->prepare("SELECT staff_id FROM staff_registration WHERE name = :name LIMIT 1");
        $stmt->execute([':name' => $staffName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        parent::__construct($pdo, $row['staff_id'] ?? null, $staffName);
    }
}

// Determine logged-in status and user details
if (isset($_SESSION['usergroup'])) {
    $usergroup = $_SESSION['usergroup'];
    $isLoggedIn = true;

    if ($usergroup === 'Patient' && isset($_SESSION['patient_id'])) {
        $user = new Patient($pdo, $_SESSION['patient_id']);
    } elseif (($usergroup === 'Doctor' || $usergroup === 'Radiologist') && isset($_SESSION['name'])) {
        $user = new Staff($pdo, $_SESSION['name']);
    }

    if (isset($user)) {
        $displayName = $user->getDisplayName();
        $displayID = $user->getDisplayID();
    }
}

?>


<link rel="stylesheet" href="css/style.css">

<div id="sidebar-wrapper">
    <!-- Profile section -->
    <div id="sidebar-profile">
        <!-- A default profile picture from some online source -->
        <img src="https://static.vecteezy.com/system/resources/previews/021/548/095/non_2x/default-profile-picture-avatar-user-avatar-icon-person-icon-head-icon-profile-picture-icons-default-anonymous-user-male-and-female-businessman-photo-placeholder-social-network-avatar-portrait-free-vector.jpg" alt="Profile">
        <!-- Display name, user ID, usergroup from session or DB -->
        
        <div class="user-name"><?php echo htmlspecialchars($displayID ?: '---'); ?></div>
        <div class="user-id"><?php echo htmlspecialchars($displayName ?: 'Unknown'); ?></div>
        <div class="user-group"><?php echo htmlspecialchars($usergroup ?: ''); ?></div>
    </div>

    <ul class="sidebar-list">
        <!-- Always visible to all visitors -->
        <li>
            <a href="index.php" class="sidebar-item home">
                <i class="bi bi-house"></i>
                <span>Home</span>
            </a>
        </li>

        <?php if ($isLoggedIn): ?>
            <?php if ($usergroup === 'Patient'): ?>
                <!-- Patient-only links -->
                <li>
                    <a href="patient_dashboard.php" class="sidebar-item patient">
                        <i class="bi bi-person-square"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="view_own_details.php" class="sidebar-item patient">
                        <i class="bi bi-journal-medical"></i>
                        <span>My Records</span>
                    </a>
                </li>
                <li>
                    <a href="patient_Invoice.php" class="sidebar-item invoice">
                        <i class="bi bi-person-circle"></i>
                        <span>Patient Invoice</span>
                    </a>
                </li>
                <li>
                    <a href="pay_now.php" class="sidebar-item pay">
                        <i class="bi bi-cash-stack"></i>
                        <span>Pay Now</span>
                    </a>
                </li>

            <?php elseif ($usergroup === 'Doctor'): ?>
                <!-- Doctor-only links -->
                <li>
                    <a href="doctor_dashboard.php" class="sidebar-item doctor">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="patient_records.php" class="sidebar-item records">
                        <i class="bi bi-person-circle"></i>
                        <span>Patient Records</span>
                    </a>
                </li>
                <li>
                    <a href="consultation.php" class="sidebar-item doctor">
                        <i class="bi bi-chat-dots"></i>
                        <span>Create Consultation</span>
                    </a>
                </li>

            <?php elseif ($usergroup === 'Radiologist'): ?>
                <!-- Radiologist-only links -->
                <li>
                    <a href="radiologist_dashboard.php" class="sidebar-item radiologist">
                        <i class="bi bi-camera"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="create_test_category.php" class="sidebar-item radiologist">
                        <i class="bi bi-folder-plus"></i>
                        <span>Create Test Category</span>
                    </a>
                </li>
                <li>
                    <a href="patient_records.php" class="sidebar-item records">
                        <i class="bi bi-person-circle"></i>
                        <span>Patient Records</span>
                    </a>
                </li>
                <li>
                    <a href="view_all_requests.php" class="sidebar-item radiologist">
                        <i class="bi bi-camera"></i>
                        <span>View Requests</span>
                    </a>
                </li>
                <li>
                    <a href="view_uploaded_images.php" class="sidebar-item radiologist">
                        <i class="bi bi-image"></i>
                        <span>All Images</span>
                    </a>
                </li>
                <li>
                    <a href="settings.php" class="sidebar-item setting">
                        <i class="bi bi-tools"></i>
                        <span>Settings</span>
                    </a>
                </li>
            <?php endif; ?>

            <li>
                <a href="logout.php" class="sidebar-item logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li>
        <?php else: ?>
            <!-- Links for users who are not logged in -->
            <li>
                <a href="login.php" class="sidebar-item home">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Login</span>
                </a>
            </li>
            <li>
                <a href="register.php" class="sidebar-item home">
                    <i class="bi bi-people-fill"></i>
                    <span>Register</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</div>
