<?php
// Database connection using PDO for security
$host = 'localhost'; // Adjust if hosted (e.g., Heroku endpoint)
$dbname = 'dbousvlu1egtnp';
$username = 'uhpdlnsnj1voi';
$password = 'rowrmxvbu3z5';
 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
 
// Function to fetch real-time prices from CoinGecko API
function getCryptoPrices($symbols = ['bitcoin', 'ethereum', 'tether']) {
    global $pdo;
    $ids = implode(',', $symbols);
    $url = "https://api.coingecko.com/api/v3/simple/price?ids=$ids&vs_currencies=usd";
    $json = @file_get_contents($url);
    if ($json === false) {
        return ['error' => 'API unavailable'];
    }
    $data = json_decode($json, true);
    $prices = [];
    foreach ($data as $coin => $info) {
        $symbol = strtoupper(substr($coin, 0, 3)); // BTC for bitcoin, etc.
        $prices[$symbol] = $info['usd'];
    }
    $prices['USDT'] = 1.00; // Stablecoin
    return $prices;
}
 
// Function to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}
 
// Function to verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
 
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
