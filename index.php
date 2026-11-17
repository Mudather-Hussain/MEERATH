<?php
session_start();

if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$lang = $_SESSION['lang'] ?? 'ar';
$isArabic = $lang === 'ar';

$dir = $isArabic ? 'rtl' : 'ltr';

$content = [
    'ar' => [
        'brand' => 'ميراث',
        'subtitle' => 'نظام حساب المواريث الرقمي',
        'home' => 'الرئيسية',
        'about' => 'عن النظام',
        'calculator' => 'حاسبة المواريث',
        'hero_title' => 'احسب الميراث بسهولة ودقة',
        'hero_text' => 'نظام إلكتروني يساعدك على إدخال بيانات التركة والورثة وعرض نتائج الحساب بصورة منظمة.',
        'start' => 'ابدأ حساب الميراث',
        'learn' => 'تعرف على النظام',
        'features_title' => 'مميزات النظام',
        'feature1' => 'واجهة سهلة الاستخدام',
        'feature1_text' => 'تصميم بسيط يساعد المستخدم على إدخال البيانات بسهولة.',
        'feature2' => 'دعم لغتين',
        'feature2_text' => 'التبديل بين اللغتين العربية والإنجليزية.',
        'feature3' => 'نتائج منظمة',
        'feature3_text' => 'عرض بيانات الورثة والنتائج بصورة واضحة.',
        'notice' => 'تنبيه: النتائج أداة مساعدة، ويجب مراجعتها مع مختص شرعي أو قانوني.',
        'footer' => 'جميع الحقوق محفوظة © 2026 - مشروع ميراث'
    ],
    'en' => [
        'brand' => 'MEERATH',
        'subtitle' => 'Digital Inheritance System',
        'home' => 'Home',
        'about' => 'About',
        'calculator' => 'Inheritance Calculator',
        'hero_title' => 'Calculate Inheritance Easily',
        'hero_text' => 'A digital system that helps you enter estate and heirs information and view organized results.',
        'start' => 'Start Calculation',
        'learn' => 'Learn More',
        'features_title' => 'System Features',
        'feature1' => 'Easy to Use',
        'feature1_text' => 'A simple interface for entering information.',
        'feature2' => 'Bilingual Support',
        'feature2_text' => 'Switch between Arabic and English.',
        'feature3' => 'Organized Results',
        'feature3_text' => 'Display heirs information and results clearly.',
        'notice' => 'Notice: Results are provided as assistance and should be reviewed by a qualified legal or religious specialist.',
        'footer' => 'All Rights Reserved © 2026 - MEERATH Project'
    ]
];

$t = $content[$lang];
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['brand'] ?> | <?= $t['subtitle'] ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<header class="header">
    <div class="container nav">
        <div class="logo">
            <span>⚖️</span>
            <div>
                <h2><?= $t['brand'] ?></h2>
                <small><?= $t['subtitle'] ?></small>
            </div>
        </div>

        <nav>
            <a href="index.php"><?= $t['home'] ?></a>
            <a href="#about"><?= $t['about'] ?></a>
            <a href="pages/calculator.php"><?= $t['calculator'] ?></a>
            <a href="pages/cases.php">الحالات المحفوظة</a>
        </nav>

        <div class="language">
            <a href="?lang=ar">العربية</a>
            |
            <a href="?lang=en">English</a>
        </div>
    </div>
</header>

<main>
    <section class="hero">
        <div class="container hero-content">
            <div>
                <span class="tag">MEERATH SYSTEM</span>
                <h1><?= $t['hero_title'] ?></h1>
                <p><?= $t['hero_text'] ?></p>

                <a class="btn" href="#features"><?= $t['start'] ?></a>
                <a class="btn secondary" href="#about"><?= $t['learn'] ?></a>
            </div>

            <div class="hero-icon">⚖️</div>
        </div>
    </section>

    <section class="features container" id="features">
        <h2><?= $t['features_title'] ?></h2>

        <div class="cards">
            <div class="card">
                <div class="card-icon">✓</div>
                <h3><?= $t['feature1'] ?></h3>
                <p><?= $t['feature1_text'] ?></p>
            </div>

            <div class="card">
                <div class="card-icon">🌐</div>
                <h3><?= $t['feature2'] ?></h3>
                <p><?= $t['feature2_text'] ?></p>
            </div>

            <div class="card">
                <div class="card-icon">📊</div>
                <h3><?= $t['feature3'] ?></h3>
                <p><?= $t['feature3_text'] ?></p>
            </div>
        </div>
    </section>

    <section class="about container" id="about">
        <p><?= $t['notice'] ?></p>
    </section>
</main>

<footer>
    <p><?= $t['footer'] ?></p>
</footer>

</body>
</html>