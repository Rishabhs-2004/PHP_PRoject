<?php
session_start();
require_once 'classes/User.php';
require_once 'classes/Post.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login_simple.php');
    exit;
}

$user = new User();
$post = new Post();
$database = new Database();
$conn = $database->connect();

$user_data = $user->getUserById($_SESSION['user_id']);
$action = $_GET['action'] ?? 'dashboard';

// Advanced AI features
$mood_suggestions = [
    'happy' => ['😊', '🎉', '✨', '🌟', '💫'],
    'excited' => ['🚀', '⚡', '🔥', '💥', '🎯'],
    'peaceful' => ['🌸', '🍃', '🌊', '☁️', '🕊️'],
    'creative' => ['🎨', '✏️', '💡', '🌈', '🎭'],
    'grateful' => ['🙏', '💝', '❤️', '🌺', '☀️']
];

$ai_prompts = [
    "What made you smile today? Share the joy! 😊",
    "Describe your perfect day in 3 words! ✨",
    "What's your current mood? Let's express it! 🎭",
    "Share a photo that tells a story! 📸",
    "What are you grateful for right now? 🙏",
    "Drop your favorite quote of the day! 💭",
    "What's cooking? Show us your meal! 🍕",
    "Weekend vibes - what's your plan? 🌟"
];

// Handle post creation with advanced AI
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_post'])) {
    $description = trim($_POST['description']);
    $mood = $_POST['mood'] ?? 'happy';
    
    if (!empty($description)) {
        // AI Enhancement: Add mood-based emojis
        if (isset($mood_suggestions[$mood])) {
            $emojis = $mood_suggestions[$mood];
            $description .= ' ' . $emojis[array_rand($emojis)];
        }
        
        // AI Enhancement: Add hashtags
        $hashtags = ['#SocialLife', '#Sharing', '#Community', '#Life', '#Moments'];
        $description .= ' ' . $hashtags[array_rand($hashtags)];
        
        $post->user_id = $_SESSION['user_id'];
        $post->description = $description;
        
        $image_filename = null;
        if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
            $file_extension = pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION);
            $image_filename = uniqid() . '.' . $file_extension;
            move_uploaded_file($_FILES['post_image']['tmp_name'], 'uploads/' . $image_filename);
        }
        
        $post->image = $image_filename;
        $post->createPost();
        header('Location: ultimate.php?action=profile&success=post_created');
        exit;
    }
}

// Handle reactions with AJAX-like behavior
if (isset($_GET['react'])) {
    $post_id = (int)$_GET['post_id'];
    $reaction = $_GET['react'];
    
    if (in_array($reaction, ['like', 'dislike'])) {
        $counts = $post->updateReaction($_SESSION['user_id'], $post_id, $reaction);
        header('Location: ultimate.php?action=' . ($_GET['from'] ?? 'dashboard') . '&success=reacted');
        exit;
    }
}

// Other handlers remain same
if (isset($_GET['delete_post'])) {
    $post_id = (int)$_GET['delete_post'];
    $post->deletePost($post_id, $_SESSION['user_id']);
    header('Location: ultimate.php?action=profile&success=post_deleted');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_name = trim($_POST['full_name']);
    $new_age = (int)$_POST['age'];
    
    if ($user->updateProfile($_SESSION['user_id'], $new_name, $new_age)) {
        $_SESSION['user_name'] = $new_name;
        header('Location: ultimate.php?action=profile&success=profile_updated');
        exit;
    }
}

// Get data based on action
if ($action == 'profile') {
    $user_posts = $post->getUserPosts($_SESSION['user_id']);
} elseif ($action == 'search') {
    $search_results = [];
    $search_query = '';
    
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search_query = trim($_GET['search']);
        $query = "SELECT id, full_name, email, profile_picture, age, created_at 
                  FROM users 
                  WHERE full_name LIKE :search OR email LIKE :search
                  AND id != :current_user_id
                  ORDER BY full_name";
        
        $stmt = $conn->prepare($query);
        $search_param = '%' . $search_query . '%';
        $stmt->bindParam(':search', $search_param);
        $stmt->bindParam(':current_user_id', $_SESSION['user_id']);
        $stmt->execute();
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    // Dashboard data
    $query = "SELECT p.*, u.full_name, u.profile_picture 
              FROM posts p 
              JOIN users u ON p.user_id = u.id 
              ORDER BY p.created_at DESC LIMIT 15";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $all_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $user_count_query = "SELECT COUNT(*) as total_users FROM users";
    $user_stmt = $conn->prepare($user_count_query);
    $user_stmt->execute();
    $total_users = $user_stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
    
    $post_count_query = "SELECT COUNT(*) as total_posts FROM posts";
    $post_stmt = $conn->prepare($post_count_query);
    $post_stmt->execute();
    $total_posts = $post_stmt->fetch(PDO::FETCH_ASSOC)['total_posts'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ultimate Social Network</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        

        
        .main-header {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 25px;
            border-radius: 20px;
            margin-bottom: 25px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            color: white;
        }
        
        .navigation {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 25px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        
        .nav-btn {
            background: linear-gradient(45deg, rgba(255,255,255,0.2), rgba(255,255,255,0.1));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 15px 25px;
            text-decoration: none;
            border-radius: 30px;
            margin: 0 8px;
            display: inline-block;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .nav-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .nav-btn:hover::before {
            left: 100%;
        }
        
        .nav-btn:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        
        .nav-btn.active {
            background: linear-gradient(45deg, #f093fb, #f5576c);
            box-shadow: 0 10px 30px rgba(240, 147, 251, 0.4);
        }
        
        .content-section {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 25px;
            padding: 35px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .profile-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.1));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 40px;
            border-radius: 25px;
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .profile-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: rotate 4s linear infinite;
        }
        
        @keyframes rotate {
            100% { transform: rotate(360deg); }
        }
        
        .profile-avatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            margin: 0 auto 25px;
            background: linear-gradient(45deg, #f093fb, #f5576c, #4facfe, #00f2fe);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 56px;
            font-weight: bold;
            border: 4px solid rgba(255,255,255,0.3);
            position: relative;
            z-index: 1;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .mood-selector {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .mood-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 10px 15px;
            border-radius: 20px;
            margin: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .mood-btn:hover, .mood-btn.active {
            background: linear-gradient(45deg, #f093fb, #f5576c);
            transform: scale(1.1);
        }
        
        .ai-suggestion-box {
            background: linear-gradient(45deg, rgba(52, 152, 219, 0.3), rgba(46, 204, 113, 0.3));
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 25px;
            border-radius: 20px;
            margin-bottom: 25px;
            position: relative;
        }
        
        .post-form {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
        }
        
        .post {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .post::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.6s;
        }
        
        .post:hover::before {
            left: 100%;
        }
        
        .post:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        
        .success-message {
            background: linear-gradient(45deg, #27ae60, #2ecc71);
            color: white;
            padding: 18px;
            border-radius: 15px;
            margin: 15px 0;
            text-align: center;
            animation: slideInDown 0.6s ease;
            box-shadow: 0 10px 30px rgba(39, 174, 96, 0.3);
        }
        
        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.1));
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, #f093fb, #f5576c);
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .stat-card:hover::before {
            opacity: 0.1;
        }
        
        .stat-card:hover {
            transform: scale(1.08) rotate(2deg);
        }
        
        .stat-number {
            font-size: 3em;
            font-weight: bold;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }
        
        .btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            transition: all 0.4s ease;
            transform: translate(-50%, -50%);
        }
        
        .btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 15px;
            font-size: 16px;
            transition: all 0.3s;
            margin-bottom: 20px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            color: white;
        }
        
        .form-group input::placeholder, .form-group textarea::placeholder {
            color: rgba(255,255,255,0.7);
        }
        
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none;
            border-color: #f093fb;
            box-shadow: 0 0 20px rgba(240, 147, 251, 0.3);
        }
        
        .reaction-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            margin: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .reaction-btn:hover {
            background: linear-gradient(45deg, #f093fb, #f5576c);
            transform: scale(1.1);
        }
        
        .typing-effect {
            overflow: hidden;
            border-right: 2px solid;
            white-space: nowrap;
            animation: typing 3s steps(40, end), blink-caret 0.75s step-end infinite;
        }
        
        @keyframes typing {
            from { width: 0; }
            to { width: 100%; }
        }
        
        @keyframes blink-caret {
            from, to { border-color: transparent; }
            50% { border-color: white; }
        }
    </style>
</head>
<body>


    <div class="container">
        <div class="main-header">
            <h1 style="font-size: 3em; margin-bottom: 10px;"><i class="fas fa-users"></i> SocialVerse</h1>
            <p class="typing-effect" style="font-size: 1.2em;">Connect, Share, Inspire - Hey <?php echo htmlspecialchars($user_data['full_name']); ?>! 🌟</p>
        </div>

        <div class="navigation">
            <a href="ultimate.php?action=dashboard" class="nav-btn <?php echo $action == 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> Smart Dashboard
            </a>
            <a href="ultimate.php?action=profile" class="nav-btn <?php echo $action == 'profile' ? 'active' : ''; ?>">
                <i class="fas fa-user-astronaut"></i> My Universe
            </a>
            <a href="ultimate.php?action=search" class="nav-btn <?php echo $action == 'search' ? 'active' : ''; ?>">
                <i class="fas fa-search-plus"></i> Discover Souls
            </a>
            <a href="logout.php" class="nav-btn" style="background: linear-gradient(45deg, #e74c3c, #c0392b);">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                <?php 
                switch($_GET['success']) {
                    case 'post_created': echo '🚀 Your post launched into the social universe!'; break;
                    case 'post_deleted': echo '🌟 Post removed from your timeline!'; break;
                    case 'profile_updated': echo '✨ Profile enhanced with cosmic energy!'; break;
                    case 'reacted': echo '💫 Your reaction sent to the cosmos!'; break;
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="content-section">
            <?php if ($action == 'profile'): ?>
                <!-- ULTIMATE PROFILE SECTION -->
                <div class="profile-card">
                    <div class="profile-avatar" style="background: none; padding: 0;">
                        <?php if ($user_data['profile_picture'] && $user_data['profile_picture'] != 'default.jpg' && file_exists('uploads/' . $user_data['profile_picture'])): ?>
                            <img src="uploads/<?php echo $user_data['profile_picture']; ?>" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; background: linear-gradient(45deg, #f093fb, #f5576c, #4facfe, #00f2fe); display: flex; align-items: center; justify-content: center; font-size: 56px; font-weight: bold; color: white; border-radius: 50%;">
                                <?php echo strtoupper(substr($user_data['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h1 style="font-size: 3em; margin-bottom: 15px; position: relative; z-index: 1;"><?php echo htmlspecialchars($user_data['full_name']); ?></h1>
                    <div style="background: rgba(255,255,255,0.2); padding: 20px; border-radius: 20px; margin: 25px 0; backdrop-filter: blur(10px); position: relative; z-index: 1;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 20px; text-align: center;">
                            <div>
                                <div style="font-size: 2em; font-weight: bold;"><?php echo count($user_posts ?? []); ?></div>
                                <div style="font-size: 1em; opacity: 0.9;">Posts</div>
                            </div>
                            <div>
                                <div style="font-size: 2em; font-weight: bold;"><?php echo array_sum(array_column($user_posts ?? [], 'likes')); ?></div>
                                <div style="font-size: 1em; opacity: 0.9;">Likes</div>
                            </div>
                            <div>
                                <div style="font-size: 2em; font-weight: bold;"><?php echo $user_data['age']; ?></div>
                                <div style="font-size: 1em; opacity: 0.9;">Age</div>
                            </div>
                        </div>
                    </div>
                    <button class="btn" onclick="openEditModal()" style="background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.3); position: relative; z-index: 1;">
                        <i class="fas fa-magic"></i> Enhance Profile
                    </button>
                </div>

                <!-- AI SUGGESTION BOX -->
                <div class="ai-suggestion-box">
                    <h4><i class="fas fa-robot"></i> AI Creative Prompt</h4>
                    <p id="aiSuggestion" style="font-size: 1.1em; margin: 15px 0;"><?php echo $ai_prompts[array_rand($ai_prompts)]; ?></p>
                    <button onclick="getNewPrompt()" class="btn" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-sync-alt"></i> New Inspiration
                    </button>
                </div>

                <!-- MOOD-BASED POST CREATION -->
                <div class="post-form">
                    <h4 style="color: white; margin-bottom: 20px;"><i class="fas fa-feather-alt"></i> Express Your Vibe</h4>
                    
                    <div class="mood-selector">
                        <p style="color: white; margin-bottom: 15px;">Choose your mood:</p>
                        <button type="button" class="mood-btn" onclick="selectMood('happy')" data-mood="happy">😊 Happy</button>
                        <button type="button" class="mood-btn" onclick="selectMood('excited')" data-mood="excited">🚀 Excited</button>
                        <button type="button" class="mood-btn" onclick="selectMood('peaceful')" data-mood="peaceful">🌸 Peaceful</button>
                        <button type="button" class="mood-btn" onclick="selectMood('creative')" data-mood="creative">🎨 Creative</button>
                        <button type="button" class="mood-btn" onclick="selectMood('grateful')" data-mood="grateful">🙏 Grateful</button>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="mood" id="selectedMood" value="happy">
                        <div class="form-group">
                            <textarea name="description" placeholder="Share your cosmic thoughts... ✨" required rows="4"></textarea>
                        </div>
                        <div class="form-group">
                            <input type="file" name="post_image" accept="image/*">
                        </div>
                        <button type="submit" name="create_post" class="btn">
                            <i class="fas fa-rocket"></i> Launch Post
                        </button>
                    </form>
                </div>

                <!-- POSTS DISPLAY -->
                <h3 style="color: white; margin-bottom: 25px;"><i class="fas fa-newspaper"></i> Your Cosmic Timeline (<?php echo count($user_posts ?? []); ?>)</h3>
                <?php if (!empty($user_posts)): ?>
                    <?php foreach ($user_posts as $user_post): ?>
                        <div class="post">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <small style="color: rgba(255,255,255,0.8);"><i class="fas fa-clock"></i> <?php echo date('M d, Y \a\t g:i A', strtotime($user_post['created_at'])); ?></small>
                                <a href="ultimate.php?action=profile&delete_post=<?php echo $user_post['id']; ?>" onclick="return confirm('Delete this cosmic post?')" style="background: #e74c3c; color: white; padding: 8px 15px; border-radius: 20px; text-decoration: none;">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                            <p style="font-size: 1.2em; line-height: 1.8; margin-bottom: 20px; color: white;"><?php echo nl2br(htmlspecialchars($user_post['description'])); ?></p>
                            <?php if ($user_post['image']): ?>
                                <img src="uploads/<?php echo $user_post['image']; ?>" style="width: 100%; max-width: 500px; border-radius: 20px; margin-bottom: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                            <?php endif; ?>
                            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                                <a href="ultimate.php?action=profile&react=like&post_id=<?php echo $user_post['id']; ?>&from=profile" class="reaction-btn">
                                    <i class="fas fa-heart"></i> <?php echo $user_post['likes']; ?>
                                </a>
                                <a href="ultimate.php?action=profile&react=dislike&post_id=<?php echo $user_post['id']; ?>&from=profile" class="reaction-btn">
                                    <i class="fas fa-thumbs-down"></i> <?php echo $user_post['dislikes']; ?>
                                </a>
                                <button class="reaction-btn" onclick="sharePost(<?php echo $user_post['id']; ?>)">
                                    <i class="fas fa-share-alt"></i> Share
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="post">
                        <div style="text-align: center; color: white; padding: 50px;">
                            <i class="fas fa-rocket" style="font-size: 4em; margin-bottom: 25px; opacity: 0.5;"></i>
                            <h3>Ready for Launch!</h3>
                            <p style="margin-top: 15px; opacity: 0.8;">Create your first cosmic post and start your social journey!</p>
                        </div>
                    </div>
                <?php endif; ?>

            <?php elseif ($action == 'dashboard'): ?>
                <!-- ULTIMATE DASHBOARD -->
                <h2 style="color: white; margin-bottom: 30px;"><i class="fas fa-chart-line"></i> Cosmic Analytics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $total_users ?? 0; ?></div>
                        <p><i class="fas fa-users"></i> Cosmic Souls</p>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $total_posts ?? 0; ?></div>
                        <p><i class="fas fa-newspaper"></i> Universe Posts</p>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo array_sum(array_column($all_posts ?? [], 'likes')); ?></div>
                        <p><i class="fas fa-heart"></i> Love Energy</p>
                    </div>
                </div>

                <h3 style="color: white; margin-bottom: 25px;"><i class="fas fa-globe-americas"></i> Universal Feed</h3>
                <?php if (!empty($all_posts)): ?>
                    <?php foreach ($all_posts as $feed_post): ?>
                        <div class="post">
                            <div style="display: flex; align-items: center; margin-bottom: 20px;">
                                <div style="width: 60px; height: 60px; border-radius: 50%; margin-right: 20px; overflow: hidden;">
                                    <?php if ($feed_post['profile_picture'] && $feed_post['profile_picture'] != 'default.jpg' && file_exists('uploads/' . $feed_post['profile_picture'])): ?>
                                        <img src="uploads/<?php echo $feed_post['profile_picture']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <div style="width: 100%; height: 100%; background: linear-gradient(45deg, #f093fb, #f5576c); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 20px;">
                                            <?php echo strtoupper(substr($feed_post['full_name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <strong style="color: white; font-size: 1.2em;"><?php echo htmlspecialchars($feed_post['full_name']); ?></strong><br>
                                    <small style="color: rgba(255,255,255,0.7);"><?php echo date('M d, Y \a\t g:i A', strtotime($feed_post['created_at'])); ?></small>
                                </div>
                            </div>
                            <p style="margin-bottom: 20px; color: white; font-size: 1.1em; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($feed_post['description'])); ?></p>
                            <?php if ($feed_post['image']): ?>
                                <img src="uploads/<?php echo $feed_post['image']; ?>" style="width: 100%; max-width: 500px; border-radius: 20px; margin-bottom: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                            <?php endif; ?>
                            <div style="display: flex; gap: 15px;">
                                <a href="ultimate.php?action=dashboard&react=like&post_id=<?php echo $feed_post['id']; ?>&from=dashboard" class="reaction-btn">
                                    <i class="fas fa-heart"></i> <?php echo $feed_post['likes']; ?>
                                </a>
                                <a href="ultimate.php?action=dashboard&react=dislike&post_id=<?php echo $feed_post['id']; ?>&from=dashboard" class="reaction-btn">
                                    <i class="fas fa-thumbs-down"></i> <?php echo $feed_post['dislikes']; ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            <?php elseif ($action == 'search'): ?>
                <!-- ULTIMATE SEARCH -->
                <h2 style="color: white; margin-bottom: 30px;"><i class="fas fa-search-plus"></i> Discover People</h2>
                <form method="GET" style="margin-bottom: 35px;">
                    <input type="hidden" name="action" value="search">
                    <div style="display: flex; gap: 20px;">
                        <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search_query ?? ''); ?>" style="flex: 1; padding: 20px; border: 2px solid rgba(255,255,255,0.3); border-radius: 30px; font-size: 18px; background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); color: white;">
                        <button type="submit" class="btn" style="padding: 20px 40px;"><i class="fas fa-search"></i> Discover</button>
                    </div>
                </form>

                <?php if (isset($search_results)): ?>
                    <?php if (empty($search_results) && !empty($search_query)): ?>
                        <div class="post">
                            <div style="text-align: center; color: white; padding: 50px;">
                                <i class="fas fa-user-slash" style="font-size: 4em; margin-bottom: 25px; opacity: 0.5;"></i>
                                <h3>No Users Found</h3>
                                <p style="margin-top: 15px; opacity: 0.8;">No users found for "<?php echo htmlspecialchars($search_query); ?>"</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($search_results as $found_user): ?>
                            <div class="post">
                                <div style="display: flex; align-items: center; gap: 25px;">
                                    <div style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden;">
                                        <?php if ($found_user['profile_picture'] && $found_user['profile_picture'] != 'default.jpg' && file_exists('uploads/' . $found_user['profile_picture'])): ?>
                                            <img src="uploads/<?php echo $found_user['profile_picture']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <div style="width: 100%; height: 100%; background: linear-gradient(45deg, #f093fb, #f5576c, #4facfe); display: flex; align-items: center; justify-content: center; color: white; font-size: 28px; font-weight: bold;">
                                                <?php echo strtoupper(substr($found_user['full_name'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <h3 style="color: white; font-size: 1.5em; margin-bottom: 8px;"><?php echo htmlspecialchars($found_user['full_name']); ?></h3>
                                        <p style="color: rgba(255,255,255,0.8); margin-bottom: 5px;"><?php echo htmlspecialchars($found_user['email']); ?></p>
                                        <small style="color: rgba(255,255,255,0.6);">Age: <?php echo $found_user['age']; ?> | Joined <?php echo date('M Y', strtotime($found_user['created_at'])); ?></small>
                                    </div>
                                    <button class="btn" onclick="connectUser(<?php echo $found_user['id']; ?>, '<?php echo htmlspecialchars($found_user['full_name']); ?>')" style="background: linear-gradient(45deg, #27ae60, #2ecc71); padding: 15px 25px;">
                                        <i class="fas fa-user-plus"></i> Connect
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="post">
                        <div style="text-align: center; color: white; padding: 50px;">
                            <i class="fas fa-users" style="font-size: 4em; margin-bottom: 25px; opacity: 0.5;"></i>
                            <h3>Explore the Universe</h3>
                            <p style="margin-top: 15px; opacity: 0.8;">Search above to discover amazing people in our community!</p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const aiPrompts = <?php echo json_encode($ai_prompts); ?>;
        
        function getNewPrompt() {
            const randomPrompt = aiPrompts[Math.floor(Math.random() * aiPrompts.length)];
            document.getElementById('aiSuggestion').textContent = randomPrompt;
        }

        function selectMood(mood) {
            document.querySelectorAll('.mood-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            document.getElementById('selectedMood').value = mood;
        }

        function sharePost(postId) {
            if (navigator.share) {
                navigator.share({
                    title: 'Amazing Post!',
                    text: 'Check out this post from SocialVerse',
                    url: window.location.href
                });
            } else {
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('🚀 Post link copied to clipboard!');
                });
            }
        }
        
        function connectUser(userId, userName) {
            // Show connection success message
            alert('🤝 Connection request sent to ' + userName + '! Feature will be fully implemented soon.');
            
            // Change button text temporarily
            event.target.innerHTML = '<i class="fas fa-check"></i> Request Sent';
            event.target.style.background = 'linear-gradient(45deg, #95a5a6, #7f8c8d)';
            event.target.disabled = true;
        }

        // Auto-hide success messages with animation
        setTimeout(() => {
            const successMsg = document.querySelector('.success-message');
            if (successMsg) {
                successMsg.style.opacity = '0';
                successMsg.style.transform = 'translateY(-30px)';
                setTimeout(() => successMsg.style.display = 'none', 300);
            }
        }, 5000);


    </script>
</body>
</html>