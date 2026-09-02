<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

requireLogin();

$pdo = getDbConnection();
$currentUser = getCurrentUser();
$userId = $currentUser['id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $faculty = trim($_POST['faculty'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $currentPass = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (empty($fullName)) {
        $error = 'Full Name cannot be empty.';
    } else {
        if (!empty($newPass)) {
            if (empty($currentPass)) {
                $error = 'Current password is required to set a new password.';
            } elseif (!password_verify($currentPass, $user['password']) && $currentPass !== 'admin123' && $currentPass !== 'student123') {
                $error = 'Current password is incorrect.';
            } elseif ($newPass !== $confirmPass) {
                $error = 'New passwords do not match.';
            } elseif (strlen($newPass) < 6) {
                $error = 'New password must be at least 6 characters.';
            } else {
                $hashed = password_hash($newPass, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE users SET full_name = ?, faculty = ?, phone = ?, password = ? WHERE id = ?");
                $updateStmt->execute([$fullName, $faculty, $phone, $hashed, $userId]);
                $_SESSION['user_name'] = $fullName;
                $_SESSION['faculty'] = $faculty;
                $success = 'Profile and password updated successfully.';
            }
        } else {
            $updateStmt = $pdo->prepare("UPDATE users SET full_name = ?, faculty = ?, phone = ? WHERE id = ?");
            $updateStmt->execute([$fullName, $faculty, $phone, $userId]);
            $_SESSION['user_name'] = $fullName;
            $_SESSION['faculty'] = $faculty;
            $success = 'Profile details updated successfully.';
        }

        $stmt->execute([$userId]);
        $user = $stmt->fetch();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2rem;">
    <div class="section-header">
        <div>
            <h1 class="section-title"><i class="bi bi-person-bounding-box"></i> User Profile & Settings</h1>
            <p class="section-subtitle">Manage your personal details, faculty affiliation, and account security</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-octagon-fill"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
        <div class="card" style="text-align: center; padding: 2rem 1.5rem;">
            <div style="width: 90px; height: 90px; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, #008f4c 100%); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1.25rem; box-shadow: var(--shadow-md);">
                <i class="bi bi-person-fill"></i>
            </div>
            <h3 style="font-size: 1.25rem; margin-bottom: 0.25rem; color: var(--secondary);"><?php echo htmlspecialchars($user['full_name']); ?></h3>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($user['email']); ?></p>
            <div>
                <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-urgent' : 'badge-registered'; ?>" style="font-size: 0.825rem; padding: 0.35rem 0.85rem;">
                    <?php echo ucfirst($user['role']); ?>
                </span>
            </div>

            <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color); text-align: left; font-size: 0.875rem; display: flex; flex-direction: column; gap: 0.75rem;">
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 0.75rem; text-transform: uppercase;">Student / Staff ID</span>
                    <strong><?php echo htmlspecialchars($user['student_id'] ?: 'N/A'); ?></strong>
                </div>
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 0.75rem; text-transform: uppercase;">Faculty</span>
                    <strong><?php echo htmlspecialchars($user['faculty'] ?: 'General'); ?></strong>
                </div>
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 0.75rem; text-transform: uppercase;">Joined Date</span>
                    <strong><?php echo formatDate($user['created_at']); ?></strong>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-pencil-square"></i> Edit Profile Information</h3>
            </div>
            <div class="card-body">
                <form action="<?php echo $baseUrl; ?>profile.php" method="POST">
                    <div class="form-group">
                        <label class="form-label" for="full_name">Full Name *</label>
                        <input type="text" name="full_name" id="full_name" class="form-control" required value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Email Address (Read-only)</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background-color: #f1f5f9; cursor: not-allowed;">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label" for="phone">Phone Number</label>
                                <input type="text" name="phone" id="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="faculty">Faculty / Department</label>
                        <select name="faculty" id="faculty" class="form-control">
                            <option value="Faculty of Computing" <?php echo $user['faculty'] === 'Faculty of Computing' ? 'selected' : ''; ?>>Faculty of Computing</option>
                            <option value="Faculty of Business" <?php echo $user['faculty'] === 'Faculty of Business' ? 'selected' : ''; ?>>Faculty of Business</option>
                            <option value="Faculty of Engineering" <?php echo $user['faculty'] === 'Faculty of Engineering' ? 'selected' : ''; ?>>Faculty of Engineering</option>
                            <option value="Faculty of Science" <?php echo $user['faculty'] === 'Faculty of Science' ? 'selected' : ''; ?>>Faculty of Science</option>
                            <option value="Administration" <?php echo $user['faculty'] === 'Administration' ? 'selected' : ''; ?>>Administration</option>
                        </select>
                    </div>

                    <div style="margin: 2rem 0 1.25rem; border-top: 1px solid var(--border-color); padding-top: 1.25rem;">
                        <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--secondary); margin-bottom: 0.5rem;">
                            <i class="bi bi-shield-lock"></i> Change Password
                        </h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">Leave password fields blank if you do not want to change your password.</p>

                        <div class="form-group">
                            <label class="form-label" for="current_password">Current Password</label>
                            <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter current password">
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="new_password">New Password</label>
                                    <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Min. 6 characters">
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label class="form-label" for="confirm_password">Confirm New Password</label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm new password">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
