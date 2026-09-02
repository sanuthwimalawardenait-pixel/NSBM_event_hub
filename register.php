<?php
$pageTitle = 'Student Registration';
require_once __DIR__ . '/config/database.php';

if (isLoggedIn()) {
    header('Location: ' . getBaseUrl() . 'index.php');
    exit;
}

$error = '';
$fullName = '';
$studentId = '';
$email = '';
$faculty = '';
$phone = '';
$redirect = isset($_GET['redirect']) ? trim($_GET['redirect']) : (isset($_POST['redirect']) ? trim($_POST['redirect']) : '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $studentId = trim($_POST['student_id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $faculty = trim($_POST['faculty'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $redirect = trim($_POST['redirect'] ?? '');

    if (empty($fullName) || empty($studentId) || empty($email) || empty($faculty) || empty($password)) {
        $error = 'Please fill in all mandatory fields.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match. Please re-enter.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } else {
        $pdo = getDbConnection();
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR student_id = ?");
        $checkStmt->execute([$email, $studentId]);

        if ($checkStmt->fetch()) {
            $error = 'An account with this email address or Student ID already exists.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $pdo->prepare("
                INSERT INTO users (full_name, student_id, email, password, role, faculty, phone)
                VALUES (?, ?, ?, ?, 'student', ?, ?)
            ");
            $insertStmt->execute([$fullName, $studentId, $email, $hashedPassword, $faculty, $phone]);

            $newUserId = $pdo->lastInsertId();
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_name'] = $fullName;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'student';
            $_SESSION['student_id'] = $studentId;
            $_SESSION['faculty'] = $faculty;

            setFlashMessage('success', 'Welcome to NSBM EventHub! Your student account has been created successfully.');
            
            if (!empty($redirect) && strpos($redirect, 'http://') === false && strpos($redirect, 'https://') === false && strpos($redirect, '//') !== 0) {
                $baseUrl = getBaseUrl();
                if (strpos($redirect, $baseUrl) === 0) {
                    $target = $redirect;
                } else {
                    $target = $baseUrl . ltrim($redirect, '/');
                }
                header('Location: ' . $target);
            } else {
                header('Location: ' . getBaseUrl() . 'events.php');
            }
            exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 580px;">
        <div class="auth-header">
            <div class="auth-logo">
                <img src="<?php echo $baseUrl; ?>assets/images/logo.jpeg" alt="NSBM EventHub Logo" style="height: 64px; max-width: 100%; object-fit: contain; margin: 0 auto; display: block; border-radius: var(--radius-sm);">
            </div>
            <h2 class="auth-title">Create Student Account</h2>
            <p class="auth-subtitle">Register with your NSBM credentials to join university events</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <div id="passwordMismatchError" class="alert alert-danger" style="display: none;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="bi bi-exclamation-octagon-fill"></i>
                <span>Passwords do not match. Please verify both passwords.</span>
            </div>
        </div>

        <form action="<?php echo $baseUrl; ?>register.php" method="POST" id="studentRegisterForm">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
            <div class="form-group">
                <label class="form-label" for="full_name">Full Name *</label>
                <input type="text" name="full_name" id="full_name" class="form-control" placeholder="e.g. Kamal Perera" required value="<?php echo htmlspecialchars($fullName); ?>">
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="student_id">Student ID / Batch No *</label>
                        <input type="text" name="student_id" id="student_id" class="form-control" placeholder="e.g. NSBM-2024-0129" required value="<?php echo htmlspecialchars($studentId); ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="faculty">Faculty *</label>
                        <select name="faculty" id="faculty" class="form-control" required>
                            <option value="">Select Faculty</option>
                            <option value="Faculty of Computing" <?php echo $faculty === 'Faculty of Computing' ? 'selected' : ''; ?>>Faculty of Computing</option>
                            <option value="Faculty of Business" <?php echo $faculty === 'Faculty of Business' ? 'selected' : ''; ?>>Faculty of Business</option>
                            <option value="Faculty of Engineering" <?php echo $faculty === 'Faculty of Engineering' ? 'selected' : ''; ?>>Faculty of Engineering</option>
                            <option value="Faculty of Science" <?php echo $faculty === 'Faculty of Science' ? 'selected' : ''; ?>>Faculty of Science</option>
                            <option value="Faculty of Postgraduate Studies" <?php echo $faculty === 'Faculty of Postgraduate Studies' ? 'selected' : ''; ?>>Faculty of Postgraduate Studies</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="email">Student Email *</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="student.name@students.nsbm.ac.lk" required value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="text" name="phone" id="phone" class="form-control" placeholder="+94 7X XXX XXXX" value="<?php echo htmlspecialchars($phone); ?>">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="reg_password">Password *</label>
                        <input type="password" name="password" id="reg_password" class="form-control" placeholder="Min. 6 characters" required>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="reg_confirm_password">Confirm Password *</label>
                        <input type="password" name="confirm_password" id="reg_confirm_password" class="form-control" placeholder="Re-enter password" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 1rem;">
                <i class="bi bi-person-check-fill"></i> Complete Registration
            </button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="<?php echo $baseUrl; ?>login.php">Sign In</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
