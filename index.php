<?php
session_start();

// Always show welcome page first - user can choose signup/login
?>
<!DOCTYPE html>
<html>
<head>
    <title>SocialVerse - Welcome</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        
        .welcome-container {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 25px;
            padding: 50px;
            text-align: center;
            color: white;
            max-width: 500px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        
        .welcome-container h1 {
            font-size: 3em;
            margin-bottom: 20px;
        }
        
        .welcome-container p {
            font-size: 1.2em;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.4s ease;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            font-size: 16px;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        
        .btn.signup {
            background: linear-gradient(45deg, #f093fb, #f5576c);
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <h1><i class="fas fa-users"></i> SocialVerse</h1>
        <p>Connect, Share, Inspire - Your Ultimate Social Experience</p>
        
        <div>
            <a href="signup_simple.php" class="btn signup">
                <i class="fas fa-user-plus"></i> Create New Account
            </a>
            <a href="login_simple.php" class="btn">
                <i class="fas fa-sign-in-alt"></i> Already Have Account? Login
            </a>
        </div>
        
        <?php if (isset($_SESSION['user_id'])): ?>
        <div style="margin-top: 20px;">
            <a href="ultimate.php" class="btn" style="background: linear-gradient(45deg, #27ae60, #2ecc71);">
                <i class="fas fa-rocket"></i> Enter SocialVerse
            </a>
            <br><br>
            <a href="logout.php" class="btn" style="background: linear-gradient(45deg, #e74c3c, #c0392b); font-size: 14px; padding: 10px 20px;">
                <i class="fas fa-sign-out-alt"></i> Switch Account
            </a>
        </div>
        <?php else: ?>
        <div style="margin-top: 30px; font-size: 0.9em; opacity: 0.7;">
            <p>✨ AI-Powered Social Network</p>
            <p>🚀 Modern Design & Features</p>
            <p>💫 Real-time Interactions</p>
        </div>
        <?php endif; ?>
    </div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>
</html>
?>