<?php include 'db.php'; $prices = getCryptoPrices(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Binance Clone - Home</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Awesome Dark Theme CSS - Binance Inspired */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #0d1117 0%, #1a1f2e 100%); color: #f0f0f0; overflow-x: hidden; }
        header { background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 20px rgba(0,0,0,0.5); }
        .logo { font-size: 1.8rem; font-weight: bold; color: #f0b90b; text-shadow: 0 0 10px rgba(240,185,11,0.5); }
        nav a { color: #f0f0f0; text-decoration: none; margin: 0 1rem; padding: 0.5rem 1rem; border-radius: 5px; transition: all 0.3s; }
        nav a:hover { background: #f0b90b; color: #000; transform: scale(1.05); }
        .hero { text-align: center; padding: 4rem 2rem; background: linear-gradient(45deg, rgba(240,185,11,0.1), rgba(0,191,255,0.1)); }
        .hero h1 { font-size: 3rem; margin-bottom: 1rem; color: #f0b90b; text-shadow: 0 0 20px rgba(240,185,11,0.3); }
        .market-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; padding: 2rem; max-width: 1200px; margin: 0 auto; }
        .price-card { background: rgba(255,255,255,0.05); border-radius: 15px; padding: 1.5rem; text-align: center; transition: all 0.3s; border: 1px solid rgba(240,185,11,0.2); backdrop-filter: blur(10px); }
        .price-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(240,185,11,0.2); }
        .symbol { font-size: 1.5rem; font-weight: bold; color: #f0b90b; }
        .price { font-size: 2rem; color: #00ff00; margin: 0.5rem 0; }
        .change { font-size: 1rem; color: #00ff88; } /* Simulated positive change */
        .btn { background: linear-gradient(45deg, #f0b90b, #e6a700); color: #000; padding: 0.8rem 2rem; border: none; border-radius: 25px; cursor: pointer; font-weight: bold; transition: all 0.3s; margin: 1rem; }
        .btn:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(240,185,11,0.4); }
        .chart-container { max-width: 800px; margin: 2rem auto; background: rgba(0,0,0,0.5); border-radius: 15px; padding: 1rem; }
        footer { text-align: center; padding: 2rem; background: rgba(0,0,0,0.8); color: #888; }
        @media (max-width: 768px) { .hero h1 { font-size: 2rem; } .market-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <div class="logo">Binance Clone</div>
        <nav>
            <a href="#" onclick="redirect('login.php')">Login</a>
            <a href="#" onclick="redirect('signup.php')">Sign Up</a>
        </nav>
    </header>
    <section class="hero">
        <h1>Welcome to the Future of Trading</h1>
        <p>Buy, Sell & Track Cryptocurrencies in Real-Time</p>
        <button class="btn" onclick="redirect('signup.php')">Get Started</button>
    </section>
    <section class="market-grid">
        <?php foreach (['BTC' => $prices['BTC'] ?? 0, 'ETH' => $prices['ETH'] ?? 0, 'USDT' => $prices['USDT'] ?? 1] as $symbol => $price): ?>
        <div class="price-card">
            <div class="symbol"><?php echo $symbol; ?></div>
            <div class="price">$<?php echo number_format($price, 2); ?></div>
            <div class="change">+2.5% (24h)</div> <!-- Simulated -->
        </div>
        <?php endforeach; ?>
    </section>
    <section class="chart-container">
        <canvas id="priceChart"></canvas>
    </section>
    <footer>&copy; 2025 Binance Clone. All rights reserved.</footer>
    <script>
        // JS for redirection
        function redirect(url) { window.location.href = url; }
 
        // Real-time price tracking simulation (fetch every 10s)
        function updatePrices() {
            // Simulate API call for demo; in prod, AJAX to PHP endpoint
            setInterval(() => {
                // Fetch new prices via fetch('/api/prices.php') if separate endpoint
                console.log('Updating prices...');
            }, 10000);
        }
        updatePrices();
 
        // Chart.js for market trends
        const ctx = document.getElementById('priceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                datasets: [{ label: 'BTC Price', data: [40000, 45000, 42000, 50000, 48000], borderColor: '#f0b90b', tension: 0.1 }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: false } } }
        });
    </script>
</body>
</html>
