<?php include 'db.php'; 
$error = ''; $success = '';
if ($_POST) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    if (strlen($password) < 6) { $error = 'Password too short'; }
    else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() == 0) {
            $hashed = hashPassword($password);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed])) {
                $user_id = $pdo->lastInsertId();
                // Insert default wallets
                $default_wallets = [['BTC', 0], ['ETH', 0], ['USDT', 1000]]; // Start with $1000 USDT
                $stmt = $pdo->prepare("INSERT INTO wallets (user_id, symbol, balance) VALUES (?, ?, ?)");
                foreach ($default_wallets as $w) { $stmt->execute([$user_id, $w[0], $w[1]]); }
                $success = 'Account created! Redirecting...';
                echo "<script>setTimeout(() => redirect('login.php'), 2000);</script>";
            } else { $error = 'Signup failed'; }
        } else { $error = 'User exists'; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Binance Clone</title>
    <style>
        /* Reusing awesome dark theme */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #0d1117 0%, #1a1f2e 100%); color: #f0f0f0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .form-container { background: rgba(255,255,255,0.05); backdrop-filter: blur(20px); padding: 2rem; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); width: 100%; max-width: 400px; border: 1px solid rgba(240,185,11,0.2); }
        h2 { text-align: center; margin-bottom: 1.5rem; color: #f0b90b; text-shadow: 0 0 10px rgba(240,185,11,0.3); }
        input { width: 100%; padding: 0.8rem; margin: 0.5rem 0; border: none; border-radius: 8px; background: rgba(255,255,255,0.1); color: #f0f0f0; transition: all 0.3s; }
        input:focus { outline: none; box-shadow: 0 0 10px rgba(240,185,11,0.5); }
        .btn { width: 100%; background: linear-gradient(45deg, #f0b90b, #e6a700); color: #000; padding: 0.8rem; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; margin-top: 1rem; transition: all 0.3s; }
        .btn:hover { transform: scale(1.02); box-shadow: 0 5px 15px rgba(240,185,11,0.4); }
        .error { color: #ff6b6b; text-align: center; margin: 1rem 0; background: rgba(255,107,107,0.1); padding: 0.5rem; border-radius: 5px; }
        .success { color: #00ff88; text-align: center; margin: 1rem 0; background: rgba(0,255,136,0.1); padding: 0.5rem; border-radius: 5px; }
        .back-link { text-align: center; margin-top: 1rem; color: #888; }
        .back-link a { color: #f0b90b; text-decoration: none; }
        @media (max-width: 480px) { .form-container { margin: 1rem; padding: 1.5rem; } }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Create Account</h2>
        <?php if ($error) echo "<div class='error'>$error</div>"; ?>
        <?php if ($success) echo "<div class='success'>$success</div>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password (min 6 chars)" required>
            <button type="submit" class="btn">Sign Up</button>
        </form>
        <div class="back-link"><a href="#" onclick="redirect('index.php')">Back to Home</a></div>
    </div>
    <script>
        function redirect(url) { window.location.href = url; }
    </script>
</body>
</html>
