<?php include 'db.php'; 
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];
$prices = getCryptoPrices();
$symbol = $_GET['symbol'] ?? 'BTCUSDT';
$base = explode('USDT', $symbol)[0]; // e.g., BTC
if ($_POST) {
    $side = $_POST['side'];
    $type = $_POST['type'] ?? 'market';
    $amount = floatval($_POST['amount']);
    $price = ($type === 'market') ? ($prices[$base] ?? 0) : floatval($_POST['price']);
    if ($amount > 0 && $price > 0) {
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, type, side, symbol, amount, price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $type, $side, $base, $amount, $price]);
        // Simulate fill: update wallet
        $fiat_amount = $amount * $price;
        if ($side === 'buy') {
            // Deduct USDT, add crypto
            $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id=? AND symbol='USDT'")->execute([$fiat_amount, $user_id]);
            $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id=? AND symbol=?")->execute([$amount, $user_id, $base]);
        } else {
            // Add USDT, deduct crypto
            $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id=? AND symbol='USDT'")->execute([$fiat_amount, $user_id]);
            $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id=? AND symbol=?")->execute([$amount, $user_id, $base]);
        }
        // Add transaction
        $pdo->prepare("INSERT INTO transactions (user_id, type, symbol, amount, usd_value, status) VALUES (?, ?, ?, ?, ?, 'completed')")
            ->execute([$user_id, $side, $base, $amount, $fiat_amount]);
        echo "<script>alert('Order executed!'); location.reload();</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trade - Binance Clone</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Dark theme */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #0d1117 0%, #1a1f2e 100%); color: #f0f0f0; }
        header { background: rgba(0,0,0,0.8); padding: 1rem 2rem; display: flex; justify-content: space-between; }
        .logo { color: #f0b90b; }
        nav a { color: #f0f0f0; margin: 0 1rem; text-decoration: none; transition: all 0.3s; }
        nav a:hover { color: #f0b90b; }
        .trade-container { display: grid; grid-template-columns: 1fr 300px; gap: 2rem; max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }
        .chart-section { background: rgba(0,0,0,0.5); border-radius: 15px; padding: 1rem; height: 500px; }
        .order-section { background: rgba(255,255,255,0.05); border-radius: 15px; padding: 1.5rem; }
        select, input { width: 100%; padding: 0.5rem; margin: 0.5rem 0; border-radius: 5px; background: rgba(255,255,255,0.1); color: #f0f0f0; border: 1px solid rgba(240,185,11,0.2); }
        .btn-buy { background: linear-gradient(45deg, #00ff88, #00cc66); color: #000; width: 48%; padding: 0.8rem; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-sell { background: linear-gradient(45deg, #ff6b6b, #cc5252); color: #fff; width: 48%; padding: 0.8rem; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn:hover { transform: scale(1.02); }
        .current-price { font-size: 1.5rem; color: #00ff00; text-align: center; margin: 1rem 0; }
        .order-type { display: flex; gap: 1rem; }
        @media (max-width: 1024px) { .trade-container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <div class="logo">Trade <?php echo $symbol; ?></div>
        <nav>
            <a href="#" onclick="redirect('dashboard.php')">Dashboard</a>
            <a href="#" onclick="redirect('history.php')">History</a>
            <a href="#" onclick="logout()">Logout</a>
        </nav>
    </header>
    <div class="trade-container">
        <div class="chart-section">
            <div class="current-price">Current Price: $<?php echo number_format($prices[$base] ?? 0, 2); ?></div>
            <canvas id="tradeChart"></canvas>
        </div>
        <div class="order-section">
            <h3>Place Order</h3>
            <form method="POST">
                <select name="type">
                    <option value="market">Market Order</option>
                    <option value="limit">Limit Order</option>
                </select>
                <input type="number" name="amount" placeholder="Amount (<?php echo $base; ?>)" step="0.00000001" required>
                <?php if ($_POST['type'] === 'limit'): ?>
                <input type="number" name="price" placeholder="Price ($)" step="0.01" required>
                <?php endif; ?>
                <div class="order-type">
                    <button type="submit" name="side" value="buy" class="btn-buy">Buy</button>
                    <button type="submit" name="side" value="sell" class="btn-sell">Sell</button>
                </div>
            </form>
            <h4>Open Orders</h4>
            <!-- List open orders from DB -->
            <ul style="list-style: none;">
                <?php
                $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id=? AND status='open' ORDER BY created_at DESC LIMIT 5");
                $stmt->execute([$user_id]);
                foreach ($stmt->fetchAll() as $order): ?>
                <li style="padding: 0.5rem; border-bottom: 1px solid #333;"><?php echo $order['side']; ?> <?php echo $order['amount']; ?> <?php echo $order['symbol']; ?> @ $<?php echo $order['price']; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <script>
        function redirect(url) { window.location.href = url; }
        function logout() { if(confirm('Logout?')) redirect('login.php?logout=1'); }
 
        // Trading chart with real-time simulation
        const ctx = document.getElementById('tradeChart').getContext('2d');
        let chart = new Chart(ctx, {
            type: 'candlestick', // Simplified line for demo
            data: {
                labels: Array.from({length: 20}, (_, i) => i),
                datasets: [{ label: '<?php echo $base; ?>', data: Array.from({length: 20}, () => Math.random() * 10000 + 40000), borderColor: '#f0b90b', tension: 0 }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: false } } }
        });
 
        // Real-time price update
        setInterval(() => {
            // Simulate price change
            chart.data.datasets[0].data = chart.data.datasets[0].data.map(() => Math.random() * 10000 + 40000);
            chart.update();
        }, 5000);
    </script>
</body>
</html>
