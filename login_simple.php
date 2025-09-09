<!DOCTYPE html>
<html>
<head>
    <title>Simple Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Login</h2>
            <form method="POST" action="process_login.php">
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                
                <button type="submit" class="btn">Login</button>
            </form>
            
            <p style="text-align: center; margin-top: 20px;">
                Don't have account? <a href="signup_simple.php">Sign up here</a>
            </p>
        </div>
    </div>
</body>
</html>