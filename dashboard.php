<?php include 'db.php'; 
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM wallets WHERE user_id=?");
$stmt->execute([$user_id]);
$wallets = $stmt->fetchAll();
$prices = getCryptoPrices();
$total_usd = 0;
foreach ($wallets as $w) {
    $total_usd += ($w['balance'] * ($prices[$w['symbol']] ?? 0));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Binance Clone</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Awesome dark theme continued */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #0d1117 0%, #1a1f2e 100%); color: #f0f0f0; }
        header { background: rgba(0,0,0,0.8); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.5rem; color: #f0b90b; }
        nav a { color: #f0f0f0; margin: 0 1rem; text-decoration: none; padding: 0.5rem; border-radius: 5px; transition: all 0.3s; }
        nav a:hover { background: #f0b90b; color: #000; }
        .portfolio { text-align: center; padding: 2rem; background: rgba(0,0,0,0.5); margin: 2rem; border-radius: 15px; }
        .balance { font-size: 2.5rem; color: #00ff88; margin: 1rem 0; }
        .wallet-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; padding: 2rem; max-width: 1000px; margin: 0 auto; }
        .wallet-card { background: rgba(255,255,255,0.05); border-radius: 10px; padding: 1rem; text-align: center; transition: all 0.3s; border: 1px solid rgba(240,185,11,0.2); }
        .wallet-card:hover { transform: translateY(-3px); box-shadow: 0 5px 20px rgba(240,185,11,0.2); }
        .symbol { font-weight: bold; color: #f0b90b; }
        .bal { font-size: 1.2rem; color: #00ff00; }
        .usd { color: #888; }
        .btn { background: linear-gradient(45deg, #f0b90b, #e6a700); color: #000; padding: 0.5rem 1rem; border: none; border-radius: 5px; cursor: pointer; margin: 0.2rem; transition: all 0.3s; }
        .btn:hover { transform: scale(1.05); }
        .profit { color: #00ff88; } /* Simulated P/L */
        footer { text-align: center; padding: 1rem; background: rgba(0,0,0,0.8); }
        @media (max-width: 768px) { .wallet-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <div class="logo">Binance Clone Dashboard</div>
        <nav>
            <a href="#" onclick="redirect('trade.php')">Trade</a>
            <a href="#" onclick="redirect('history.php')">History</a>
            <a href="#" onclick="logout()">Logout</a>
        </nav>
    </header>
    <section class="portfolio">
        <h2>Portfolio Balance</h2>
        <div class="balance">$<?php echo number_format($total_usd, 2); ?></div>
        <div class="profit">+5.2% (Today)</div> <!-- Simulated -->
        <canvas id="portfolioChart" width="400" height="200"></canvas>
    </section>
    <section class="wallet-grid">
        <h3 style="text-align: center; grid-column: 1/-1; color: #f0b90b;">Wallets</h3>
        <?php foreach ($wallets as $w): $usd = $w['balance'] * ($prices[$w['symbol']] ?? 0); ?>
        <div class="wallet-card">
            <div class="symbol"><?php echo $w['symbol']; ?></div>
            <div class="bal"><?php echo number_format($w['balance'], 8); ?></div>
            <div class="usd">$<?php echo number_format($usd, 2); ?></div>
            <button class="btn" onclick="deposit('<?php echo $w['symbol']; ?>')">Deposit</button>
            <button class="btn" onclick="withdraw('<?php echo $w['symbol']; ?>')">Withdraw</button>
        </div>
        <?php endforeach; ?>
    </section>
    <footer>Secure & Real-Time Trading</footer>
    <script>
        function redirect(url) { window.location.href = url; }
        function logout() { if(confirm('Logout?')) window.location.href = 'login.php?logout=1'; }
 
        // Deposit/Withdraw forms (simulate or AJAX to PHP)
        function deposit(symbol) { alert(`Deposit ${symbol} - Simulate blockchain deposit`); /* Add form */ }
        function withdraw(symbol) { 
            let amount = prompt(`Withdraw ${symbol} amount:`);
            if (amount) { 
                // AJAX to process withdraw
                fetch('dashboard.php', { method: 'POST', body: new FormData({action: 'withdraw', symbol, amount}) })
                .then(() => location.reload());
            }
        }
 
        // Portfolio chart
        const ctx = document.getElementById('portfolioChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($wallets, 'symbol')); ?>,
                datasets: [{ data: <?php echo json_encode(array_map(fn($w) => $w['balance'] * ($prices[$w['symbol']] ?? 0), $wallets)); ?>, backgroundColor: ['#f0b90b', '#00ff88', '#007bff'] }]
            },
            options: { responsive: true }
        });
 
        // Real-time update
        setInterval(() => location.reload(), 30000); // Refresh every 30s for prices
    </script>
</body>
</html>
<?php
// Handle POST for deposit/withdraw (add here for simplicity)
if ($_POST['action'] === 'withdraw') {
    $symbol = $_POST['symbol'];
    $amount = floatval($_POST['amount']);
    $stmt = $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id=? AND symbol=? AND balance >= ?");
    if ($stmt->execute([$amount, $user_id, $symbol, $amount])) {
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, symbol, amount, status) VALUES (?, 'withdraw', ?, ?, 'completed')");
        $stmt->execute([$user_id, $symbol, $amount]);
    }
}
?>
