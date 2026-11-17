<?php

require_once 'includes/inheritance_calculator.php';

$heirs = [
    [
        'relationship' => 'father',
        'gender' => 'male',
        'quantity' => 1
    ],
    [
        'relationship' => 'mother',
        'gender' => 'female',
        'quantity' => 1
    ],
    [
        'relationship' => 'son',
        'gender' => 'male',
        'quantity' => 2
    ],
    [
        'relationship' => 'daughter',
        'gender' => 'female',
        'quantity' => 2
    ]
];

$results = calculateBasicInheritance($heirs, 80000);

echo '<pre>';
print_r($results);
echo '</pre>';