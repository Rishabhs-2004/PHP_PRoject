<!DOCTYPE html>
<html>
<head>
    <title>Simple Signup</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Create Account</h2>
            <form method="POST" action="process_signup.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Full Name:</label>
                    <input type="text" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label>Age:</label>
                    <input type="number" name="age" min="13" max="120" required>
                </div>
                
                <div class="form-group">
                    <label>Profile Picture:</label>
                    <input type="file" name="profile_picture" accept="image/*">
                </div>
                
                <button type="submit" class="btn">Sign Up</button>
            </form>
            
            <p style="text-align: center; margin-top: 20px;">
                Already have account? <a href="login_simple.php">Login here</a>
            </p>
        </div>
    </div>
</body>
</html>