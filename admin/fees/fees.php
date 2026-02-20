<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/Auth.php';
require_once '../../models/Student.php';
require_once '../../models/Fee.php';
require_once '../../models/StudentFee.php';
require_once '../../models/Grade.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !($auth->isAdmin())) {
    header('Location: ../../login.php');
    exit;
}

$studentModel = new Student();
$feeModel = new Fee();
$studentFeeModel = new StudentFee();
$gradeModel = new Grade();

$students = $studentModel->getAllStudentsWithParents();
$grades = $gradeModel->findAll();
$feeStructure = $feeModel->findAllWithGradeNames();
$feeSummary = $studentFeeModel->getFeeSummaryByGrade();

// Calculate summary values
$totalStudents = array_sum(array_column($feeSummary, 'total_students')) ?: 0;
$totalPaid = array_sum(array_column($feeSummary, 'total_paid')) ?: 0;
$totalPending = array_sum(array_column($feeSummary, 'total_pending')) ?: 0;
$paidCount = array_sum(array_column($feeSummary, 'paid_count')) ?: 0;
$pendingCount = array_sum(array_column($feeSummary, 'pending_count')) ?: 0;

// Handle actions
$notification = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'pay_fee':
                    $studentId = $_POST['student_id'];
                    $term = $_POST['term'];
                    
                    $result = $studentModel->processFeePayment($studentId, $term);
                    $notificationType = $result['graduated'] ?? false ? 'graduation' : 'success';
                    $notification = "
                        <div class='notification notification-{$notificationType}' style='margin-bottom: 1rem; padding: 1rem; border-radius: 0.5rem; background: " . ($notificationType === 'graduation' ? 'linear-gradient(135deg, #8b5cf6, #6366f1)' : 'linear-gradient(135deg, #10b981, #059669)') . "; color: white;'>
                            <div style='display: flex; align-items: center; gap: 0.5rem;'>
                                <svg fill='none' stroke='currentColor' viewBox='0 0 24 24' style='width: 20px; height: 20px;'>
                                    <path stroke-linecap='round' stroke-linejoin='round' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' />
                                </svg>
                                <span>" . htmlspecialchars($result['message']) . "</span>
                            </div>
                        </div>
                    ";
                    break;
                    
                case 'create_student_fees':
                    $studentId = $_POST['student_id'];
                    $amount = $_POST['amount'] ?? 500.00;
                    
                    $created = $studentFeeModel->createStudentFees($studentId, null, $amount);
                    if ($created) {
                        $notification = "
                            <div class='notification notification-success' style='margin-bottom: 1rem; padding: 1rem; border-radius: 0.5rem; background: linear-gradient(135deg, #10b981, #059669); color: white;'>
                                <div style='display: flex; align-items: center; gap: 0.5rem;'>
                                    <svg fill='none' stroke='currentColor' viewBox='0 0 24 24' style='width: 20px; height: 20px;'>
                                        <path stroke-linecap='round' stroke-linejoin='round' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' />
                                    </svg>
                                    <span>Fee records created successfully for student!</span>
                                </div>
                            </div>
                        ";
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $notification = "
            <div class='notification notification-error' style='margin-bottom: 1rem; padding: 1rem; border-radius: 0.5rem; background: linear-gradient(135deg, #ef4444, #dc2626); color: white;'>
                <div style='display: flex; align-items: center; gap: 0.5rem;'>
                    <svg fill='none' stroke='currentColor' viewBox='0 0 24 24' style='width: 20px; height: 20px;'>
                        <path stroke-linecap='round' stroke-linejoin='round' d='M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' />
                    </svg>
                    <span>Error: " . htmlspecialchars($e->getMessage()) . "</span>
                </div>
            </div>
        ";
    }
}

// Build the content HTML
ob_start();
?>

<section class="section">
    <div class="container">
        <div class="section-header" style="margin-bottom: 3rem;">
            <div class="section-badge">
                <div class="badge-dot"></div>
                <span class="badge-text">Administration</span>
            </div>
            <h1 class="section-title">
                Fee
                <span class="gradient">Management</span>
            </h1>
            <p class="section-description">
                Manage student fees, track academic progression, and view fee structure
            </p>
        </div>

        <?php echo $notification; ?>
        
        <!-- Summary Cards -->
        <div class="summary-card">
            <h3 style="margin: 0 0 1rem 0;">Fee Summary</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <div style="font-size: 2rem; font-weight: bold;"><?php echo $totalStudents; ?></div>
                    <div>Total Students</div>
                </div>
                <div class="summary-item">
                    <div style="font-size: 2rem; font-weight: bold; color: #10b981;">K<?php echo number_format($totalPaid, 2); ?></div>
                    <div>Total Paid</div>
                </div>
                <div class="summary-item">
                    <div style="font-size: 2rem; font-weight: bold; color: #f59e0b;">K<?php echo number_format($totalPending, 2); ?></div>
                    <div>Pending</div>
                </div>
                <div class="summary-item">
                    <div style="font-size: 2rem; font-weight: bold;"><?php echo $paidCount; ?></div>
                    <div>Paid Fees</div>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; gap: 1rem; flex-wrap: wrap;">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="new.php" class="btn btn-primary" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New Fee Structure
                </a>
                <button id="createAllFeesBtn" class="btn btn-secondary" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Create All Student Fees
                </button>
            </div>
        </div>

        <!-- Fee Structure Cards -->
        <h3 style="margin: 2rem 0 1rem 0; color: white;">Fee Structure</h3>
        <?php if (!empty($feeStructure)): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <?php foreach ($feeStructure as $fee): ?>
                    <div class="fee-card">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <div>
                                <h4 style="color: white; margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($fee['grade_name']); ?></h4>
                                <div style="color: var(--color-text); font-size: 0.875rem;"><?php echo htmlspecialchars($fee['term']); ?></div>
                            </div>
                            <div style="color: #10b981; font-weight: bold; font-size: 1.25rem;">
                                K<?php echo number_format($fee['amount'], 2); ?>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem;">
                            <span style="color: var(--color-text);">ID: <?php echo $fee['id']; ?></span>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="edit.php?id=<?php echo $fee['id']; ?>" class="btn btn-sm btn-secondary" style="padding: 0.25rem 0.5rem;">
                                    Edit
                                </a>
                                <form method="POST" action="delete.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $fee['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" style="padding: 0.25rem 0.5rem;" onclick="return confirm('Are you sure you want to delete this fee?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem; color: var(--color-text);">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 48px; height: 48px; margin: 0 auto 1rem; opacity: 0.5;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p>No fee structure defined yet. <a href="new.php" style="color: #3b82f6;">Add your first fee structure</a>.</p>
            </div>
        <?php endif; ?>

        <!-- Students Table -->
        <h3 style="margin: 2rem 0 1rem 0; color: white;">Students & Fee Progress</h3>
        <div class="card">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: var(--color-surface);">
                        <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Student</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Grade / Year</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Term Progress</th>
                        <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student):
                        // Build a keyed map of fee records by term number
                        $feeStatus  = $studentModel->getFeeStatus($student['id']);
                        $feeByTerm  = [];
                        foreach ($feeStatus as $f) {
                            $feeByTerm[(int)$f['term']] = $f;
                        }
                        $currentTerm   = (int)($student['current_term'] ?? 1);
                        $academicYear  = $student['academic_year'] ?? date('Y');
                        $paidCount     = count(array_filter($feeStatus, fn($f) => $f['paid']));
                        $totalFees     = count($feeStatus);
                        // Next unpaid term (for the Pay button)
                        $nextUnpaid    = null;
                        for ($t = 1; $t <= 3; $t++) {
                            if (isset($feeByTerm[$t]) && !$feeByTerm[$t]['paid']) {
                                $nextUnpaid = $feeByTerm[$t];
                                break;
                            }
                        }
                    ?>
                        <tr>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--color-border);">
                                <div style="font-weight: 600; color: white;"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></div>
                                <div style="color: var(--color-muted); font-size: 0.8rem;">ID: <?php echo $student['id']; ?></div>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--color-border);">
                                <div style="font-weight: 500;"><?php echo htmlspecialchars($student['grade']); ?></div>
                                <div style="color: var(--color-muted); font-size: 0.8rem;">Year <?php echo $academicYear; ?></div>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--color-border);">
                                <?php if (empty($feeStatus)): ?>
                                    <span style="color: var(--color-muted); font-size: 0.875rem;">No fee records yet</span>
                                <?php else: ?>
                                    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                        <?php for ($t = 1; $t <= 3; $t++):
                                            $termFee = $feeByTerm[$t] ?? null;
                                            $isPaid  = $termFee && $termFee['paid'];
                                            $amount  = $termFee ? number_format($termFee['amount'], 0) : '—';
                                        ?>
                                            <div style="
                                                display: flex; flex-direction: column; align-items: center;
                                                padding: 0.5rem 0.75rem; border-radius: 0.5rem; min-width: 72px;
                                                background: <?php echo $isPaid ? 'linear-gradient(135deg,#10b981,#059669)' : 'rgba(255,255,255,0.06)'; ?>;
                                                border: 1px solid <?php echo $isPaid ? 'transparent' : 'var(--color-border)'; ?>;
                                            ">
                                                <span style="font-size: 0.7rem; color: <?php echo $isPaid ? 'rgba(255,255,255,0.8)' : 'var(--color-muted)'; ?>;">Term <?php echo $t; ?></span>
                                                <span style="font-weight: 700; color: <?php echo $isPaid ? 'white' : 'var(--color-text)'; ?>; font-size: 0.85rem;">
                                                    <?php if ($isPaid): ?>
                                                        ✓ Paid
                                                    <?php elseif ($termFee): ?>
                                                        K<?php echo $amount; ?>
                                                    <?php else: ?>
                                                        —
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                            <?php if ($t < 3): ?>
                                                <span style="color: var(--color-muted); font-size: 0.75rem;">→</span>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                        <?php if ($paidCount === $totalFees && $totalFees > 0): ?>
                                            <span style="margin-left: 0.5rem; font-size: 0.8rem; color: #10b981; font-weight: 600;">🎓 Promoted!</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid var(--color-border);">
                                <?php if (empty($feeStatus)): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="create_student_fees">
                                        <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-secondary">
                                            + Create Fees
                                        </button>
                                    </form>
                                <?php elseif ($nextUnpaid): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="pay_fee">
                                        <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                        <input type="hidden" name="term" value="<?php echo $nextUnpaid['term']; ?>">
                                        <button type="submit" class="btn btn-sm btn-primary"
                                            style="background: linear-gradient(135deg,#3b82f6,#1d4ed8);">
                                            💳 Pay Term <?php echo $nextUnpaid['term']; ?>
                                            (K<?php echo number_format($nextUnpaid['amount'], 0); ?>)
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="fee-status-paid">✅ All Paid</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Fee Management - Bright Future School Admin</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../../public/css/styles.css?v=<?php echo md5_file(__DIR__ . '/../../public/css/styles.css'); ?>">
    
    <style>
        /* Fee Management Specific Styles */
        .fee-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--color-border);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .fee-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            border-color: var(--color-primary);
        }

        .fee-status-paid {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .fee-status-pending {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .summary-card {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .summary-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 0.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .summary-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            border-radius: 0.375rem;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
        }

        .btn-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px -2px rgba(239, 68, 68, 0.4);
        }
        
        /* Base styles */
        :root {
            --color-bg: #0a0f1e;
            --color-surface: #1a1f35;
            --color-primary: #3b82f6;
            --color-secondary: #2563eb;
            --color-accent: #60a5fa;
            --color-warning: #fbbf24;
            --color-text: #e5e7eb;
            --color-muted: #9ca3af;
            --color-border: #374151;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--color-bg);
            color: var(--color-text);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .section {
            padding: 4rem 0;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 9999px;
            padding: 0.5rem 1rem;
            margin-bottom: 1rem;
        }
        
        .badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--color-primary);
        }
        
        .badge-text {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .section-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .gradient {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .section-description {
            font-size: 1.125rem;
            color: var(--color-muted);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--color-border);
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            color: white;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--color-text);
            border: 1px solid var(--color-border);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--color-primary);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 1rem;
            background: var(--color-surface);
            border-bottom: 1px solid var(--color-border);
            font-weight: 600;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid var(--color-border);
        }
        
        tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        
        @media (max-width: 768px) {
            .section-title {
                font-size: 2rem;
            }
            
            .container {
                padding: 1rem;
            }
            
            th, td {
                padding: 0.75rem 0.5rem;
                font-size: 0.875rem;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header id="header">
        <div class="header-container">
            <div class="header-content">
                <!-- Logo -->
                <div class="logo" onclick="window.location.href='../../admin/index.php'">
                    <div class="logo-icon">BF</div>
                    <div class="logo-text">
                        <h1>BRIGHT FUTURE</h1>
                        <p>Primary School</p>
                    </div>
                </div>

                <!-- Navigation -->
                <nav id="nav">
                    <button data-section="dashboard" class="nav-btn" onclick="window.location.href='../../admin/index.php'">Dashboard</button>
                    <button data-section="students" class="nav-btn" onclick="window.location.href='../students/student.php'">Students</button>
                    <button data-section="fees" class="nav-btn active" onclick="window.location.href='fees.php'">Fees</button>
                    <button data-section="teachers" class="nav-btn" onclick="window.location.href='../teachers/teacher.php'">Teachers</button>
                    <button data-section="parents" class="nav-btn" onclick="window.location.href='../parents/parent.php'">Parents</button>
                </nav>

                <!-- Portal Button -->
                <a href="../../logout.php" class="portal-btn">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <?php echo $content; ?>
    </main>

    <!-- FOOTER -->
    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <div class="footer-logo-icon">BF</div>
                <div>
                    <div class="footer-logo-text">BRIGHT FUTURE</div>
                    <div class="footer-logo-subtext">Primary School</div>
                </div>
            </div>

            <div class="footer-copyright">
                &copy; <?php echo date('Y'); ?> Bright Future School. All rights reserved.
            </div>

            <div class="footer-socials">
                <a href="#" class="social-link" aria-label="Facebook">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" />
                    </svg>
                </a>
                <a href="#" class="social-link" aria-label="Twitter">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z" />
                    </svg>
                </a>
                <a href="#" class="social-link" aria-label="Instagram">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zm1.5-4.87h.01" />
                    </svg>
                </a>
                <a href="#" class="social-link" aria-label="LinkedIn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z" />
                    </svg>
                </a>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="../../public/js/api.js"></script>
    <script src="../../public/js/main.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Create all student fees button
            const createAllFeesBtn = document.getElementById('createAllFeesBtn');
            if (createAllFeesBtn) {
                createAllFeesBtn.addEventListener('click', function() {
                    if (confirm('This will create fee records for all students who don\'t have them. Continue?')) {
                        // Disable button and show loading state
                        createAllFeesBtn.disabled = true;
                        createAllFeesBtn.innerHTML = `
                            <svg class="animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; margin-right: 0.5rem;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Creating...
                        `;
                        
                        // Make AJAX request
                        fetch('bulk-create-fees.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Re-enable button
                            createAllFeesBtn.disabled = false;
                            createAllFeesBtn.innerHTML = `
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; margin-right: 0.5rem;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Create All Student Fees
                            `;
                            
                            if (data.success) {
                                // Show success notification
                                showNotification(data.message, 'success');
                                
                                // Optionally reload the page to show updated data
                                setTimeout(() => {
                                    location.reload();
                                }, 2000);
                            } else {
                                // Show error notification
                                showNotification(data.message || 'Failed to create student fees', 'error');
                            }
                        })
                        .catch(error => {
                            // Re-enable button
                            createAllFeesBtn.disabled = false;
                            createAllFeesBtn.innerHTML = `
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; margin-right: 0.5rem;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Create All Student Fees
                            `;
                            
                            showNotification('Network error: ' + error.message, 'error');
                        });
                    }
                });
            }
        });
        
        // Simple notification function
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem;
                border-radius: 0.5rem;
                color: white;
                z-index: 1000;
                max-width: 400px;
                background: ${type === 'success' ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #ef4444, #dc2626)'};
            `;
            
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                        ${type === 'success' ? 
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />' :
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
                        }
                    </svg>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        }
    </script>
</body>
</html>