<?php
require_once 'C:/xampp 2/htdocs/MEERATH/config/database.php';

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$caseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$selectedCase = null;
$heirs = [];
$error = '';

try {
    if ($caseId) {
        // عرض تفاصيل حالة محددة
        $stmt = $conn->prepare(
            "SELECT * FROM inheritance_cases WHERE id = ?"
        );
        $stmt->execute([$caseId]);
        $selectedCase = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($selectedCase) {
            $stmt = $conn->prepare(
                "SELECT * FROM heirs WHERE case_id = ? ORDER BY id ASC"
            );
            $stmt->execute([$caseId]);
            $heirs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error = 'الحالة المطلوبة غير موجودة.';
        }
    } else {
        // عرض جميع الحالات
        $stmt = $conn->query(
            "SELECT * FROM inheritance_cases ORDER BY id DESC LIMIT 100"
        );
        $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error = 'تعذر تحميل البيانات من قاعدة البيانات.';
}
$netEstate = null;

if ($selectedCase) {
    $estateValue = (float)$selectedCase['estate_value'];
    $funeralExpenses = (float)($selectedCase['funeral_expenses'] ?? 0);
    $debtsValue = (float)$selectedCase['debts'];
    $willValue = (float)$selectedCase['will_amount'];

    $netEstate = $estateValue
        - $funeralExpenses
        - $debtsValue
        - $willValue;
}
$relationships = [
    'father' => 'الأب',
    'mother' => 'الأم',
    'husband' => 'الزوج',
    'wife' => 'الزوجة',
    'son' => 'الابن',
    'daughter' => 'البنت',
    'brother' => 'الأخ',
    'sister' => 'الأخت',
    'grandfather' => 'الجد',
    'grandmother' => 'الجدة'
];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الحالات المحفوظة | ميراث</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<header class="header">
    <div class="container nav">
        <div class="logo">
            <span>⚖️</span>
            <div>
                <h2>ميراث MEERATH</h2>
                <small>إدارة حالات الميراث</small>
            </div>
        </div>

        <nav>
            <a href="../index.php">الرئيسية</a>
            <a href="calculator.php">إضافة حالة</a>
            <a href="cases.php">الحالات المحفوظة</a>
        </nav>
    </div>
</header>

<main class="container calculator-page">

<?php if ($error): ?>

    <div class="notice error"><?= h($error) ?></div>

<?php elseif ($selectedCase): ?>

   <h1>تفاصيل حالة الميراث</h1>

<section class="form-section">
    <h2>بيانات المتوفى والتركة</h2>

    <p><strong>رقم الحالة:</strong>
        <?= h($selectedCase['case_number']) ?></p>

    <p><strong>اسم المتوفى:</strong>
        <?= h($selectedCase['deceased_name']) ?></p>

    <p><strong>الجنس:</strong>
        <?= $selectedCase['deceased_gender'] === 'male' ? 'ذكر' : 'أنثى' ?></p>

    <p><strong>قيمة التركة:</strong>
        <?= number_format((float)$selectedCase['estate_value'], 2) ?></p>

    <p><strong>نفقات تجهيز المتوفى:</strong>
        <?= number_format((float)($selectedCase['funeral_expenses'] ?? 0), 2) ?></p>

    <p><strong>الديون:</strong>
        <?= number_format((float)$selectedCase['debts'], 2) ?></p>

    <p><strong>قيمة الوصية المدخلة:</strong>
        <?= number_format((float)$selectedCase['will_amount'], 2) ?></p>

    <p><strong>صافي التركة الحسابي:</strong>
    <?= number_format((float)$netEstate, 2) ?>
    </p>

    <p><strong>المرجعية المختارة:</strong>
        <?= $selectedCase['reference_mode'] === 'sharia'
            ? 'الشريعة الإسلامية'
            : 'القانون السوداني' ?></p>
</section>

    <section class="form-section">
        <h2>الورثة المسجلون</h2>

        <div style="overflow-x:auto">
            <table class="cases-table">
                <thead>
                    <tr>
                        <th>اسم الوارث</th>
                        <th>صلة القرابة</th>
                        <th>الجنس</th>
                        <th>العدد</th>
                    </tr>
                </thead>

                <tbody>
    <a href="cases.php" class="btn">العودة إلى الحالات</a>

<?php else: ?>

    <h1>الحالات المحفوظة</h1>
    <p>قائمة الحالات المسجلة في نظام ميراث.</p>

    <section class="form-section">

        <?php if (empty($cases)): ?>

            <p>لا توجد حالات محفوظة حتى الآن.</p>

        <?php else: ?>

            <div style="overflow-x:auto">
                <table class="cases-table">
                    <thead>
                        <tr>
                            <th>رقم الحالة</th>
                            <th>اسم المتوفى</th>
                            <th>قيمة التركة</th>
                            <th>تاريخ التسجيل</th>
                            <th>التفاصيل</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($cases as $case): ?>
                        <tr>
                            <td><?= h($case['case_number']) ?></td>
                            <td><?= h($case['deceased_name']) ?></td>
                            <td><?= number_format((float)$case['estate_value'], 2) ?></td>
                            <td><?= h($case['created_at']) ?></td>
                            <td>
                                <a class="details-link"
                                   href="cases.php?id=<?= (int)$case['id'] ?>">
                                    عرض التفاصيل
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </section>

    <a href="calculator.php" class="btn">إضافة حالة جديدة</a>

<?php endif; ?>

</main>

<footer>
    <p>جميع الحقوق محفوظة © 2026 - مشروع ميراث</p>
</footer>

</body>
</html>