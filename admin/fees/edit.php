<?php
require_once '../../includes/db.php';
require_once '../../includes/Auth.php';
require_once '../../models/Fee.php';
require_once '../../models/Grade.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !($auth->isAdmin())) {
    header('Location: ../../login.php');
    exit;
}

$gradeModel = new Grade();
$grades = $gradeModel->findAll();

$message = '';
$messageType = '';
$fee = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feeId = $_POST['id'] ?? null;
    $gradeId = trim($_POST['grade_id'] ?? '');
    $term = trim($_POST['term'] ?? '');
    $amount = trim($_POST['amount'] ?? '');

    if (empty($gradeId) || empty($term) || empty($amount)) {
        $message = 'All fields are required.';
        $messageType = 'error';
    } else {
        $feeModel = new Fee();
        
        try {
            $result = $feeModel->update($feeId, [
                'grade_id' => $gradeId,
                'term' => $term,
                'amount' => floatval($amount)
            ]);

            if ($result) {
                $message = 'Fee updated successfully!';
                $messageType = 'success';
                // Redirect to fees list after successful update
                header('Location: fees.php?message=' . urlencode($message));
                exit;
            } else {
                $message = 'Failed to update fee.';
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $message = 'Error updating fee: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Load existing fee data
if (isset($_GET['id'])) {
    $feeId = $_GET['id'];
    $feeModel = new Fee();
    $fee = $feeModel->findById($feeId);
    
    if (!$fee) {
        $message = 'Fee not found.';
        $messageType = 'error';
    }
} else {
    $message = 'No fee ID specified.';
    $messageType = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Fee - Admin Dashboard</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
</head>
<body>
    <?php include '../../includes/template.php'; ?>
    
    <section class="section">
        <div class="container">
            <div class="section-header" style="margin-bottom: 2rem;">
                <h1 class="section-title">
                    Edit
                    <span class="gradient">Fee</span>
                </h1>
                <p class="section-description">
                    Update fee structure details
                </p>
            </div>

            <?php if ($message): ?>
                <div class="notification notification-<?= $messageType ?>" style="margin-bottom: 2rem; padding: 1rem; border-radius: 0.5rem; background: <?= $messageType === 'success' ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #ef4444, #dc2626)' ?>; color: white;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                            <?php if ($messageType === 'success'): ?>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <?php else: ?>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <?php endif; ?>
                        </svg>
                        <span><?= htmlspecialchars($message) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($fee): ?>
            <div class="card" style="max-width: 600px; margin: 0 auto; padding: 2rem;">
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $fee['id'] ?>">
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label for="grade_id" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Grade</label>
                        <select id="grade_id" name="grade_id" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--color-border); background: var(--color-surface); color: white;">
                            <option value="">Select Grade</option>
                            <?php foreach ($grades as $grade): ?>
                                <option value="<?= $grade['id'] ?>" <?= $fee['grade_id'] == $grade['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($grade['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label for="term" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Term</label>
                        <select id="term" name="term" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--color-border); background: var(--color-surface); color: white;">
                            <option value="">Select Term</option>
                            <option value="Term 1" <?= $fee['term'] == 'Term 1' ? 'selected' : '' ?>>Term 1</option>
                            <option value="Term 2" <?= $fee['term'] == 'Term 2' ? 'selected' : '' ?>>Term 2</option>
                            <option value="Term 3" <?= $fee['term'] == 'Term 3' ? 'selected' : '' ?>>Term 3</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 2rem;">
                        <label for="amount" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Amount (K)</label>
                        <input type="number" id="amount" name="amount" step="0.01" min="0" value="<?= htmlspecialchars($fee['amount'] ?? '') ?>" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--color-border); background: var(--color-surface); color: white;">
                    </div>

                    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                        <a href="fees.php" class="btn btn-secondary" style="text-decoration: none; padding: 0.75rem 1.5rem;">Cancel</a>
                        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem;">Update Fee</button>
                    </div>
                </form>
            </div>
            <?php else: ?>
                <div class="card" style="max-width: 600px; margin: 0 auto; padding: 2rem; text-align: center;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 48px; height: 48px; margin: 0 auto 1rem; color: #ef4444;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 style="color: white; margin-bottom: 1rem;">Fee Not Found</h3>
                    <p style="color: var(--color-text);">The fee you're trying to edit could not be found.</p>
                    <a href="fees.php" class="btn btn-primary" style="display: inline-block; margin-top: 1rem; text-decoration: none; padding: 0.75rem 1.5rem;">Back to Fee Management</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="../../public/js/main.js"></script>
</body>
</html>