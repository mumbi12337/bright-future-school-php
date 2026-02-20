<?php
require_once '../../includes/db.php';
require_once '../../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

// Show grades table
$grades = $pdo->query("SELECT * FROM grades ORDER BY name")->fetchAll();

// Show fees table with grade names
$fees = $pdo->query("
    SELECT f.*, g.name as grade_name 
    FROM fees f 
    JOIN grades g ON f.grade_id = g.id 
    ORDER BY g.name, f.term
")->fetchAll();

// Show students with their stored grade value
$students = $pdo->query("
    SELECT id, first_name, last_name, grade, academic_year
    FROM students 
    ORDER BY grade, last_name
")->fetchAll();

// Show student_fees
$studentFees = $pdo->query("
    SELECT sf.*, s.first_name, s.last_name, s.grade as student_grade, f.amount as fee_structure_amount
    FROM student_fees sf
    JOIN students s ON sf.student_id = s.id
    LEFT JOIN fees f ON sf.fee_id = f.id
    ORDER BY s.grade, s.last_name, sf.term
")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head><title>Fee Debug</title>
<style>
body { font-family: monospace; background: #111; color: #eee; padding: 2rem; }
table { border-collapse: collapse; margin-bottom: 2rem; width: 100%; }
th, td { border: 1px solid #444; padding: 0.5rem 1rem; text-align: left; }
th { background: #222; color: #60a5fa; }
h2 { color: #f59e0b; margin: 2rem 0 0.5rem; }
.mismatch { background: rgba(239,68,68,0.3); }
.ok { background: rgba(16,185,129,0.1); }
</style>
</head>
<body>

<h2>1. Grades Table</h2>
<table>
    <tr><th>id</th><th>name</th></tr>
    <?php foreach ($grades as $g): ?>
    <tr><td><?= $g['id'] ?></td><td><?= htmlspecialchars($g['name']) ?></td></tr>
    <?php endforeach; ?>
</table>

<h2>2. Fee Structure (fees table)</h2>
<table>
    <tr><th>id</th><th>grade_id</th><th>grade_name</th><th>term</th><th>amount</th></tr>
    <?php foreach ($fees as $f): ?>
    <tr><td><?= $f['id'] ?></td><td><?= $f['grade_id'] ?></td><td><?= htmlspecialchars($f['grade_name']) ?></td><td><?= htmlspecialchars($f['term']) ?></td><td>K<?= $f['amount'] ?></td></tr>
    <?php endforeach; ?>
</table>

<h2>3. Students (grade field value)</h2>
<table>
    <tr><th>id</th><th>name</th><th>grade (stored value)</th><th>matches grades table?</th></tr>
    <?php 
    $gradeNames = array_column($grades, 'name');
    foreach ($students as $s): 
        $matches = in_array($s['grade'], $gradeNames);
    ?>
    <tr class="<?= $matches ? 'ok' : 'mismatch' ?>">
        <td><?= $s['id'] ?></td>
        <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
        <td><?= htmlspecialchars($s['grade']) ?></td>
        <td><?= $matches ? '✅ YES' : '❌ NO - MISMATCH!' ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>4. Student Fees (stored vs fee structure)</h2>
<table>
    <tr><th>Student</th><th>Student Grade</th><th>Term</th><th>Stored Amount</th><th>Fee Structure Amount</th><th>Match?</th></tr>
    <?php foreach ($studentFees as $sf): 
        $amountMatch = $sf['amount'] == $sf['fee_structure_amount'];
    ?>
    <tr class="<?= $amountMatch ? 'ok' : 'mismatch' ?>">
        <td><?= htmlspecialchars($sf['first_name'] . ' ' . $sf['last_name']) ?></td>
        <td><?= htmlspecialchars($sf['student_grade']) ?></td>
        <td><?= $sf['term'] ?></td>
        <td>K<?= $sf['amount'] ?></td>
        <td><?= $sf['fee_structure_amount'] !== null ? 'K'.$sf['fee_structure_amount'] : 'NULL (no fee linked)' ?></td>
        <td><?= $amountMatch ? '✅' : '❌ WRONG' ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
