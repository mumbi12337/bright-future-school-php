<?php
require_once '../../includes/db.php';
require_once '../../includes/Auth.php';
require_once '../../models/Fee.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !($auth->isAdmin())) {
    header('Location: ../../login.php');
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $feeId = $_POST['id'];
    
    try {
        $feeModel = new Fee();
        
        // Check if fee exists
        $fee = $feeModel->findById($feeId);
        if (!$fee) {
            $error = "Fee not found.";
        } else {
            // Check if this fee is referenced by any student fees
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM student_fees WHERE fee_id = ?");
            $stmt->execute([$feeId]);
            $referencedCount = $stmt->fetchColumn();
            
            if ($referencedCount > 0) {
                $error = "Cannot delete this fee because it is referenced by {$referencedCount} student fee record(s). Please delete the student fee records first.";
            } else {
                // Safe to delete
                $result = $feeModel->delete($feeId);
                if ($result) {
                    $success = "Fee deleted successfully!";
                    // Redirect after successful deletion
                    header('Location: fees.php?message=' . urlencode($success));
                    exit;
                } else {
                    $error = "Failed to delete fee.";
                }
            }
        }
    } catch (Exception $e) {
        $error = "Error deleting fee: " . $e->getMessage();
    }
}

// If coming from redirect with message
$message = $_GET['message'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Fee - Admin Dashboard</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
</head>
<body>
    <?php include '../../includes/template.php'; ?>
    
    <section class="section">
        <div class="container">
            <div class="section-header" style="margin-bottom: 2rem;">
                <h1 class="section-title">
                    Delete
                    <span class="gradient">Fee</span>
                </h1>
                <p class="section-description">
                    Confirm deletion of fee structure
                </p>
            </div>

            <?php if ($message): ?>
                <div class="notification notification-success" style="margin-bottom: 2rem; padding: 1rem; border-radius: 0.5rem; background: linear-gradient(135deg, #10b981, #059669); color: white;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span><?= htmlspecialchars($message) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="notification notification-error" style="margin-bottom: 2rem; padding: 1rem; border-radius: 0.5rem; background: linear-gradient(135deg, #ef4444, #dc2626); color: white;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['id']) && !isset($error) && !$message): 
                $feeId = $_GET['id'];
                $feeModel = new Fee();
                $fee = $feeModel->findById($feeId);
                
                if ($fee):
                    // Get grade name
                    $stmt = $pdo->prepare("SELECT name FROM grades WHERE id = ?");
                    $stmt->execute([$fee['grade_id']]);
                    $grade = $stmt->fetch();
            ?>
                <div class="card" style="max-width: 600px; margin: 0 auto; padding: 2rem;">
                    <div style="margin-bottom: 2rem; padding: 1.5rem; background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; border-radius: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px; color: #ef4444;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <h3 style="color: #ef4444; margin: 0;">Confirm Deletion</h3>
                        </div>
                        <p style="color: var(--color-text); margin: 0;">
                            Are you sure you want to delete this fee structure? This action cannot be undone.
                        </p>
                    </div>

                    <div class="card" style="margin-bottom: 2rem; padding: 1.5rem; background: rgba(255, 255, 255, 0.05);">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <div>
                                <h4 style="color: white; margin: 0 0 0.5rem 0;"><?= htmlspecialchars($grade['name'] ?? 'Unknown Grade') ?></h4>
                                <div style="color: var(--color-text); font-size: 0.875rem;"><?= htmlspecialchars($fee['term']) ?></div>
                            </div>
                            <div style="color: #10b981; font-weight: bold; font-size: 1.5rem;">
                                K<?= number_format($fee['amount'], 2) ?>
                            </div>
                        </div>
                        <div style="color: var(--color-text); font-size: 0.875rem;">
                            Fee ID: <?= $fee['id'] ?>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                        <a href="fees.php" class="btn btn-secondary" style="text-decoration: none; padding: 0.75rem 1.5rem;">Cancel</a>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $fee['id'] ?>">
                            <button type="submit" class="btn btn-danger" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #ef4444, #dc2626); border: none; color: white; border-radius: 0.5rem; cursor: pointer;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; margin-right: 0.5rem;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete Fee
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="card" style="max-width: 600px; margin: 0 auto; padding: 2rem; text-align: center;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 48px; height: 48px; margin: 0 auto 1rem; color: #ef4444;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 style="color: white; margin-bottom: 1rem;">Fee Not Found</h3>
                    <p style="color: var(--color-text);">The fee you're trying to delete could not be found.</p>
                    <a href="fees.php" class="btn btn-primary" style="display: inline-block; margin-top: 1rem; text-decoration: none; padding: 0.75rem 1.5rem;">Back to Fee Management</a>
                </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <script src="../../public/js/main.js"></script>
</body>
</html>