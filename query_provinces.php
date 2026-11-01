<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'siapkompeten';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    
    // Jakarta
    $stmt = $pdo->query('SELECT code, name FROM indonesia_provinces WHERE name LIKE "%JAKARTA%" LIMIT 5');
    echo "Jakarta provinces:\n";
    foreach ($stmt as $row) {
        echo "  " . $row['code'] . " => " . $row['name'] . "\n";
    }
    
    // Jawa Timur
    $stmt = $pdo->query('SELECT code, name FROM indonesia_provinces WHERE name LIKE "%JAWA TIMUR%" LIMIT 5');
    echo "\nJawa Timur:\n";
    foreach ($stmt as $row) {
        echo "  " . $row['code'] . " => " . $row['name'] . "\n";
    }
    
    // Jawa Barat
    $stmt = $pdo->query('SELECT code, name FROM indonesia_provinces WHERE name LIKE "%JAWA BARAT%" LIMIT 5');
    echo "\nJawa Barat:\n";
    foreach ($stmt as $row) {
        echo "  " . $row['code'] . " => " . $row['name'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
