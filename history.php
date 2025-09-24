<?php include 'db.php'; 
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];
// Fetch transactions
$stmt = $pdo->prepare("SELECT t.*, w.balance as current_balance FROM transactions t LEFT JOIN wallets w ON t.symbol = w.symbol AND t.user_id = w.user_id WHERE t.user_id=? ORDER BY t.created_at DESC LIMIT 50");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll();
// Calculate P/L (simplified: sum usd_value for buy/sell)
$total_pnl = 0;
foreach ($transactions as $t) {
    if ($t['type'] === 'sell') $total_pnl += $t['usd_value'];
    if ($t['type'] === 'buy') $total_pnl -= $t['usd_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - Binance Clone</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Dark theme */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #0d1117 0%, #1a1f2e 100%); color: #f0f0f0; }
        header { background: rgba(0,0,0,0.8); padding: 1rem 2rem; display: flex; justify-content: space-between; }
        .logo { color: #f0b90b; }
        nav a { color: #f0f0f0; margin: 0 1rem; text-decoration: none; transition: all 0.3s; }
        nav a:hover { color: #f0b90b; }
        .history-container { max-width: 1000px; margin: 2rem auto; padding: 0 2rem; }
        .pnl { text-align: center; padding: 2rem; background: rgba(0,0,0,0.5); border-radius: 15px; margin-bottom: 2rem; }
        .pnl-value { font-size: 2rem; color: <?php echo $total_pnl >= 0 ? '#00ff88' : '#ff6b6b'; ?>; }
        table { width: 100%; border-collapse: collapse; background: rgba(255,255,255,0.05); border-radius: 10px; overflow: hidden; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid rgba(240,185,11,0.1); }
        th { background: rgba(240,185,11,0.1); color: #f0b90b; }
        .buy { color: #00ff88; } .sell { color: #ff6b6b; }
        .chart-container { margin: 2rem 0; background: rgba(0,0,0,0.5); border-radius: 15px; padding: 1rem; }
        @media (max-width: 768px) { table { font-size: 0.8rem; } th, td { padding: 0.5rem; } }
    </style>
</head>
<body>
    <header>
        <div class="logo">Transaction History</div>
        <nav>
            <a href="#" onclick="redirect('dashboard.php')">Dashboard</a>
            <a href="#" onclick="redirect('trade.php')">Trade</a>
            <a href="#" onclick="logout()">Logout</a>
        </nav>
    </header>
    <div class="history-container">
        <div class="pnl">
            <h2>Profit/Loss Analysis</h2>
            <div class="pnl-value">$<?php echo number_format($total_pnl, 2); ?></div>
            <div><?php echo $total_pnl >= 0 ? '(+ ' . number_format(($total_pnl / 1000) * 100, 1) . '%)' : '( - ' . number_format((abs($total_pnl) / 1000) * 100, 1) . '%)'; ?></div>
        </div>
        <div class="chart-container">
            <canvas id="pnlChart"></canvas>
        </div>
        <table>
            <thead>
                <tr><th>Type</th><th>Symbol</th><th>Amount</th><th>USD Value</th><th>Status</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $t): ?>
                <tr>
                    <td class="<?php echo $t['type']; ?>"><?php echo $t['type']; ?></td>
                    <td><?php echo $t['symbol']; ?></td>
                    <td><?php echo number_format($t['amount'], 8); ?></td>
                    <td>$<?php echo number_format($t['usd_value'], 2); ?></td>
                    <td><?php echo $t['status']; ?></td>
                    <td><?php echo date('Y-m-d H:i', strtotime($t['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
        function redirect(url) { window.location.href = url; }
        function logout() { if(confirm('Logout?')) redirect('login.php?logout=1'); }
 
        // P/L Chart
        const ctx = document.getElementById('pnlChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Buy', 'Sell'], // Simplified
                datasets: [{ label: 'P/L', data: [<?php echo array_sum(array_column(array_filter($transactions, fn($t)=>$t['type']=='buy'), 'usd_value')) * -1; ?>, <?php echo array_sum(array_column(array_filter($transactions, fn($t)=>$t['type']=='sell'), 'usd_value')); ?>], backgroundColor: ['#ff6b6b', '#00ff88'] }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>
