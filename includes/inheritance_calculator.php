<?php

/**
 * MEERATH - Inheritance Calculator
 * محرك حساب المواريث
 */

function validateBasicHeirs(array $heirs): array
{
    $errors = [];

    $allowedRelationships = [
        'father',
        'mother',
        'husband',
        'wife',
        'son',
        'daughter',
        'brother',
        'sister',
        'grandfather',
        'grandmother'
    ];

    $expectedGenders = [
        'father' => 'male',
        'mother' => 'female',
        'husband' => 'male',
        'wife' => 'female',
        'son' => 'male',
        'daughter' => 'female',
        'brother' => 'male',
        'sister' => 'female',
        'grandfather' => 'male',
        'grandmother' => 'female'
    ];

    foreach ($heirs as $index => $heir) {

        $relationship = $heir['relationship'] ?? '';
        $gender = $heir['gender'] ?? '';
        $quantity = (int)($heir['quantity'] ?? 0);

        if (!in_array(
            $relationship,
            $allowedRelationships,
            true
        )) {
            $errors[] = 'صلة قرابة غير مدعومة.';
            continue;
        }

        if ($gender !== $expectedGenders[$relationship]) {
            $errors[] = 'الجنس لا يتوافق مع صلة القرابة.';
        }

        if ($quantity < 1 || $quantity > 100) {
            $errors[] = 'عدد الورثة غير صحيح.';
        }

        if (
            in_array(
                $relationship,
                ['father', 'mother', 'husband',
                 'grandfather', 'grandmother'],
                true
            ) && $quantity > 1
        ) {
            $errors[] = 'لا يمكن إدخال أكثر من شخص واحد لهذه الصلة.';
        }
    }

    return $errors;
}
/**
 * حساب الميراث للحالة الأساسية:
 * الأب + الأم + الأبناء (ذكور وإناث)
 *
 * هذه النسخة الأولية لا تدعم بقية تركيبات الورثة.
 */

function calculateBasicInheritance(
    array $heirs,
    float $netEstate
): array {

    if ($netEstate < 0) {
        throw new InvalidArgumentException(
            'صافي التركة لا يمكن أن يكون سالباً.'
        );
    }

    $counts = [
        'father' => 0,
        'mother' => 0,
        'son' => 0,
        'daughter' => 0
    ];

    foreach ($heirs as $heir) {
        $relationship = $heir['relationship'];

        if (!array_key_exists($relationship, $counts)) {
            throw new InvalidArgumentException(
                'هذه الحالة غير مدعومة في النسخة الحالية.'
            );
        }

        $counts[$relationship] += (int)$heir['quantity'];
    }

    if (
        $counts['father'] !== 1 ||
        $counts['mother'] !== 1 ||
        $counts['son'] < 1
    ) {
        throw new InvalidArgumentException(
            'هذه التركيبة لم تُعتمد بعد في محرك الحساب.'
        );
    }

    $results = [];

    // نصيب الأب: السدس
    $results[] = [
        'relationship' => 'father',
        'type' => 'individual',
        'numerator' => 1,
        'denominator' => 6,
        'percentage' => 100 / 6,
        'amount' => round($netEstate / 6, 2),
        'reason' => 'السدس لوجود فرع وارث ذكر.'
    ];

    // نصيب الأم: السدس
    $results[] = [
        'relationship' => 'mother',
        'type' => 'individual',
        'numerator' => 1,
        'denominator' => 6,
        'percentage' => 100 / 6,
        'amount' => round($netEstate / 6, 2),
        'reason' => 'السدس لوجود الفرع الوارث.'
    ];

    // الباقي للأبناء للذكر مثل حظ الأنثيين
    $childrenRemainder = $netEstate * (4 / 6);

    $totalUnits =
        ($counts['son'] * 2) +
        $counts['daughter'];

    $unitValue = $childrenRemainder / $totalUnits;

    if ($counts['son'] > 0) {
        $results[] = [
            'relationship' => 'son',
            'type' => 'group',
            'quantity' => $counts['son'],
            'numerator' => 4 * $counts['son'],
            'denominator' => 3 * $totalUnits,
            'percentage' => (
            100 * 2 * $counts['son'] / $totalUnits * 4 / 6
            ),
            'amount' => round(
                $unitValue * 2 * $counts['son'],
                2
            ),
            'reason' => 'الباقي تعصيباً، للذكر مثل حظ الأنثيين.'
        ];
    }

    if ($counts['daughter'] > 0) {
        $results[] = [
            'relationship' => 'daughter',
            'type' => 'group',
            'quantity' => $counts['daughter'],
            'numerator' => 2 * $counts['daughter'],
            'denominator' => 3 * $totalUnits,
            'percentage' => (
             100 * $counts['daughter'] / $totalUnits * 4 / 6
            ),
            'amount' => round(
                $unitValue * $counts['daughter'],
                2
            ),
            'reason' => 'الباقي تعصيباً مع الابن، للذكر مثل حظ الأنثيين.'
        ];
    }
    // حساب نصيب كل ابن بصورة منفصلة
for ($i = 1; $i <= $counts['son']; $i++) {
    $results[] = [
        'relationship' => 'son',
        'type' => 'individual',
        'display_name' => 'الابن رقم ' . $i,
        'quantity' => 1,
        'numerator' => 4,
        'denominator' => 3 * $totalUnits,
        'percentage' => (
            100 * 2 / $totalUnits * 4 / 6
        ),
        'amount' => round($unitValue * 2, 2),
        'reason' => 'نصيبه الفردي: سهمان مقابل سهم للبنت.'
    ];
}

// حساب نصيب كل بنت بصورة منفصلة
for ($i = 1; $i <= $counts['daughter']; $i++) {
    $results[] = [
        'relationship' => 'daughter',
        'type' => 'individual',
        'display_name' => 'البنت رقم ' . $i,
        'quantity' => 1,
        'numerator' => 2,
        'denominator' => 3 * $totalUnits,
        'percentage' => (
            100 * 1 / $totalUnits * 4 / 6
        ),
        'amount' => round($unitValue, 2),
        'reason' => 'نصيبها الفردي: سهم واحد.'
    ];
}
    return $results;
}