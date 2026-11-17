<?php
require_once 'C:/xampp 2/htdocs/MEERATH/config/database.php';
require_once 'C:/xampp 2/htdocs/MEERATH/includes/inheritance_calculator.php';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deceasedName = trim($_POST['deceased_name'] ?? '');
    $deceasedGender = $_POST['deceased_gender'] ?? '';
    $estate = $_POST['estate_value'] ?? '';
    $debts = $_POST['debts'] ?? '';
    $will = $_POST['will_amount'] ?? '';
    $funeralExpenses = $_POST['funeral_expenses'] ?? '0';
    $reference = $_POST['reference_mode'] ?? '';

    $allowedGenders = ['male', 'female'];
    $allowedReferences = ['sharia', 'sudanese_law'];

    if (
    $deceasedName === '' ||
    !in_array($deceasedGender, $allowedGenders, true) ||
    !is_numeric($estate) ||
    !is_numeric($debts) ||
    !is_numeric($will) ||
    !is_numeric($funeralExpenses) ||
    !in_array($reference, $allowedReferences, true) ||
    $estate < 0 ||
    $debts < 0 ||
    $will < 0 ||
    $funeralExpenses < 0 ||
    (
        (float)$funeralExpenses +
        (float)$debts +
        (float)$will > (float)$estate
    )
) {
    $error = 'يرجى إدخال بيانات صحيحة في جميع الحقول المطلوبة، والتأكد من أن الخصومات لا تتجاوز قيمة التركة.';

    } else {
        $heirNames = $_POST['heir_name'] ?? [];
        $relationships = $_POST['relationship'] ?? [];
        $genders = $_POST['heir_gender'] ?? [];
        $quantities = $_POST['quantity'] ?? [];

        $heirs = [];

        foreach ($heirNames as $i => $name) {
            $name = trim($name);
            $relationship = $relationships[$i] ?? '';
            $gender = $genders[$i] ?? '';
            $quantity = $quantities[$i] ?? '';

            if ($name === '' && $relationship === '') {
                continue;
            }

            if (
                $name === '' ||
                !in_array($gender, $allowedGenders, true) ||
                !is_numeric($quantity) ||
                (int)$quantity < 1 ||
                (int)$quantity > 100
            ) {
                $error = 'يرجى مراجعة بيانات أحد الورثة.';
                break;
            }

            $heirs[] = [
                'name' => $name,
                'relationship' => $relationship,
                'gender' => $gender,
                'quantity' => (int)$quantity
            ];
        }

        if (!$error && count($heirs) === 0) {
            $error = 'أدخل بيانات وارث واحد على الأقل.';
        }
        if (!$error && count($heirs) > 0) {

    $heirErrors = validateBasicHeirs($heirs);

    if (!empty($heirErrors)) {
        $error = implode(' ', $heirErrors);
    }
}

       if (!$error) {
    try {
        $conn->beginTransaction();

        $caseNumber = 'MR-' . date('Ymd') . '-' .
            strtoupper(bin2hex(random_bytes(3)));

        $sql = "INSERT INTO inheritance_cases
            (case_number, deceased_name, deceased_gender,
             estate_value, funeral_expenses, debts,
             will_amount, reference_mode)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $caseNumber,
            $deceasedName,
            $deceasedGender,
            $estate,
            $funeralExpenses,
            $debts,
            $will,
            $reference
        ]);

        $caseId = $conn->lastInsertId();

        $heirSql = "INSERT INTO heirs
            (case_id, heir_name, relationship, gender, quantity)
            VALUES (?, ?, ?, ?, ?)";

        $heirStmt = $conn->prepare($heirSql);

        foreach ($heirs as $heir) {
            $heirStmt->execute([
                $caseId,
                $heir['name'],
                $heir['relationship'],
                $heir['gender'],
                $heir['quantity']
            ]);
        }

        $conn->commit();

        $message = "تم حفظ الحالة بنجاح. رقم الحالة: " . 
        $caseNumber;

    } catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        $error = 'تعذر حفظ البيانات. تحقق من قاعدة البيانات وحاول مرة أخرى.';
    }
}
}
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدخال بيانات الميراث | ميراث</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<header class="header">
    <div class="container nav">
        <div class="logo">
            <span>⚖️</span>
            <div>
                <h2>ميراث MEERATH</h2>
                <small>نظام حساب المواريث الرقمي</small>
            </div>
        </div>
        <nav>
            <a href="../index.php">الرئيسية</a>
            <a href="calculator.php">حاسبة المواريث</a>
        </nav>
    </div>
</header>

<main class="container calculator-page">

    <h1>إدخال بيانات التركة والورثة</h1>
    <p>أدخل البيانات الأولية للحالة المراد دراستها.</p>

    <?php if ($message): ?>
        <div class="notice success"><?= h($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="notice error"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="calculator.php">

        <section class="form-section">
            <h2>أولاً: بيانات المتوفى والتركة</h2>

            <label>اسم المتوفى</label>
            <input type="text" name="deceased_name" required>

            <label>جنس المتوفى</label>
            <select name="deceased_gender" required>
                <option value="">اختر الجنس</option>
                <option value="male">ذكر</option>
                <option value="female">أنثى</option>
            </select>

            <label>قيمة التركة</label>
            <input type="number" name="estate_value"
                   min="0" step="0.01" required>
            <div class="mb-3">
    <label for="funeral_expenses" class="form-label">
        نفقات تجهيز المتوفى
    </label>

    <input
        type="number"
        name="funeral_expenses"
        id="funeral_expenses"
        class="form-control"
        min="0"
        step="0.01"
        value="0"
        required
    >
            </div>

            <label>الديون</label>
            <input type="number" name="debts"
                   min="0" step="0.01" value="0" required>

            <label>قيمة الوصية المذكورة (إن وجدت)</label>
            <input type="number" name="will_amount"
                   min="0" step="0.01" value="0" required>

            <label>مرجعية دراسة الحالة</label>
            <select name="reference_mode" required>
                <option value="">اختر المرجعية</option>
                <option value="sharia">أحكام الشريعة الإسلامية</option>
                <option value="sudanese_law">القانون السوداني</option>
            </select>
        </section>

        <section class="form-section">
            <h2>ثانياً: بيانات الورثة</h2>

            <div id="heirs-container">
                <div class="heir-row">
                    <label>اسم الوارث</label>
                    <input type="text" name="heir_name[]" required>

                    <label>صلة القرابة</label>
                    <select name="relationship[]" required>
                        <option value="">اختر صلة القرابة</option>
                        <option value="father">الأب</option>
                        <option value="mother">الأم</option>
                        <option value="husband">الزوج</option>
                        <option value="wife">الزوجة</option>
                        <option value="son">الابن</option>
                        <option value="daughter">البنت</option>
                        <option value="brother">الأخ</option>
                        <option value="sister">الأخت</option>
                        <option value="grandfather">الجد</option>
                        <option value="grandmother">الجدة</option>
                    </select>

                    <label>جنس الوارث</label>
                    <select name="heir_gender[]" required>
                        <option value="male">ذكر</option>
                        <option value="female">أنثى</option>
                    </select>

                    <label>العدد</label>
                    <input type="number" name="quantity[]"
                           min="1" max="100" value="1" required>
                </div>
            </div>

            <button type="button" class="btn"
                    onclick="addHeir()">
                + إضافة وارث آخر
            </button>
        </section>

        <button type="submit" class="btn submit-btn">
            حفظ بيانات الحالة
        </button>

    </form>
</main>

<footer>
    <p>جميع الحقوق محفوظة © 2026 - مشروع ميراث</p>
</footer>

<script>
function addHeir() {
    const container = document.getElementById('heirs-container');
    const firstRow = container.querySelector('.heir-row');
    const newRow = firstRow.cloneNode(true);

    newRow.querySelectorAll('input').forEach(input => {
        input.value = input.name === 'quantity[]' ? '1' : '';
    });

    newRow.querySelectorAll('select').forEach(select => {
        select.selectedIndex = 0;
    });

    container.appendChild(newRow);
}
</script>

</body>
</html>