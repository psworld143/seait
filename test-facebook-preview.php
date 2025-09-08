<?php
// Simple test page to check Facebook Open Graph meta tags
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/id_encryption.php';

// Get a sample post for testing
$query = "SELECT p.*, u.first_name, u.last_name FROM posts p
          LEFT JOIN users u ON p.author_id = u.id
          WHERE p.status = 'approved'
          ORDER BY p.created_at DESC
          LIMIT 1";
$result = mysqli_query($conn, $query);
$post = mysqli_fetch_assoc($result);

if (!$post) {
    die("No posts found for testing");
}

// Simulate the same logic as news-detail.php
$og_image_url = '';
$og_image_width = 1200;
$og_image_height = 630;
$og_image_alt = htmlspecialchars($post['title']);

// Clean and prepare description
$og_description = strip_tags($post['content']);
$og_description = preg_replace('/\s+/', ' ', $og_description);
$og_description = trim($og_description);
if (strlen($og_description) > 300) {
    $og_description = substr($og_description, 0, 297) . '...';
}
$og_description = htmlspecialchars($og_description);

// Build current page URL
$current_url = "https://" . $_SERVER['HTTP_HOST'] . "/news-detail.php?id=" . encrypt_id($post['id']);

// Check if post has a featured image
if (!empty($post['image_url'])) {
    $image_path = $post['image_url'];
    
    // Ensure absolute URL for Facebook
    if (strpos($image_path, 'http') !== 0) {
        $image_path = ltrim($image_path, '/');
        $og_image_url = "https://" . $_SERVER['HTTP_HOST'] . "/" . $image_path;
    } else {
        $og_image_url = $image_path;
    }
    
    // Try to get actual image dimensions
    $local_path = str_replace("https://" . $_SERVER['HTTP_HOST'] . "/", "", $og_image_url);
    if (file_exists($local_path)) {
        $image_info = @getimagesize($local_path);
        if ($image_info !== false) {
            $og_image_width = $image_info[0];
            $og_image_height = $image_info[1];
        }
    }
    
    $og_image_alt = "Image for: " . htmlspecialchars($post['title']);
} else {
    // High-quality fallback to SEAIT logo
    $og_image_url = "https://" . $_SERVER['HTTP_HOST'] . "/assets/images/seait-logo.png";
    $og_image_alt = "SEAIT - South East Asian Institute of Technology, Inc.";
    
    // Get logo dimensions if available
    if (file_exists('assets/images/seait-logo.png')) {
        $image_info = @getimagesize('assets/images/seait-logo.png');
        if ($image_info !== false) {
            $og_image_width = $image_info[0];
            $og_image_height = $image_info[1];
        }
    }
}

// Ensure minimum Facebook recommended dimensions
if ($og_image_width < 1200 || $og_image_height < 630) {
    $og_image_width = max($og_image_width, 1200);
    $og_image_height = max($og_image_height, 630);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facebook Preview Test - <?php echo htmlspecialchars($post['title']); ?></title>
    
    <!-- Essential Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta property="og:description" content="<?php echo $og_description; ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($current_url); ?>">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="SEAIT - South East Asian Institute of Technology, Inc.">
    
    <!-- Image Meta Tags -->
    <meta property="og:image" content="<?php echo htmlspecialchars($og_image_url); ?>">
    <meta property="og:image:secure_url" content="<?php echo htmlspecialchars($og_image_url); ?>">
    <meta property="og:image:width" content="<?php echo $og_image_width; ?>">
    <meta property="og:image:height" content="<?php echo $og_image_height; ?>">
    <meta property="og:image:alt" content="<?php echo $og_image_alt; ?>">
    <meta property="og:image:type" content="image/jpeg">
    
    <!-- Additional Meta Tags -->
    <meta property="og:locale" content="en_US">
    <meta property="og:updated_time" content="<?php echo time(); ?>">
    
    <!-- Article Specific Meta Tags -->
    <meta property="article:published_time" content="<?php echo date('c', strtotime($post['created_at'])); ?>">
    <meta property="article:author" content="<?php echo htmlspecialchars($post['author'] ?? $post['first_name'] . ' ' . $post['last_name']); ?>">
    <meta property="article:section" content="<?php echo ucfirst($post['type']); ?>">
    
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .debug-info { background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .debug-info h3 { margin-top: 0; color: #333; }
        .debug-info code { background: #e8e8e8; padding: 2px 6px; border-radius: 3px; }
        .test-buttons { margin: 20px 0; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; background: #1877f2; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #166fe5; }
        .preview-image { max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 8px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Facebook Preview Test</h1>
        <p>This page helps you test and debug Facebook sharing for your SEAIT news posts.</p>
        
        <div class="debug-info">
            <h3>📊 Current Post Information</h3>
            <p><strong>Post ID:</strong> <?php echo $post['id']; ?></p>
            <p><strong>Title:</strong> <?php echo htmlspecialchars($post['title']); ?></p>
            <p><strong>Type:</strong> <?php echo ucfirst($post['type']); ?></p>
            <p><strong>Author:</strong> <?php echo htmlspecialchars($post['author'] ?? $post['first_name'] . ' ' . $post['last_name']); ?></p>
            <p><strong>Created:</strong> <?php echo date('F d, Y', strtotime($post['created_at'])); ?></p>
            <p><strong>Actual News URL:</strong> <code><?php echo htmlspecialchars($current_url); ?></code></p>
        </div>
        
        <div class="debug-info">
            <h3>🖼️ Image Information</h3>
            <p><strong>Featured Image URL:</strong> 
                <?php if (!empty($post['image_url'])): ?>
                    <code><?php echo htmlspecialchars($post['image_url']); ?></code>
                    <span class="success">✓ Has featured image</span>
                <?php else: ?>
                    <span class="warning">⚠️ No featured image (using fallback)</span>
                <?php endif; ?>
            </p>
            <p><strong>OG Image URL:</strong> <code><?php echo htmlspecialchars($og_image_url); ?></code></p>
            <p><strong>Image Dimensions:</strong> <?php echo $og_image_width; ?>x<?php echo $og_image_height; ?>px</p>
            <p><strong>Image Preview:</strong></p>
            <img src="<?php echo htmlspecialchars($og_image_url); ?>" alt="Preview" class="preview-image" style="max-width: 400px;">
        </div>
        
        <div class="debug-info">
            <h3>📝 Content Information</h3>
            <p><strong>Description Length:</strong> <?php echo strlen($og_description); ?> characters</p>
            <p><strong>Description Preview:</strong></p>
            <p style="background: #fff; padding: 10px; border-left: 3px solid #1877f2;">
                <?php echo $og_description; ?>
            </p>
        </div>
        
        <div class="test-buttons">
            <h3>🧪 Test Tools</h3>
            <a href="https://developers.facebook.com/tools/debug/?q=<?php echo urlencode($current_url); ?>" 
               target="_blank" class="btn">
               🔍 Test in Facebook Debugger
            </a>
            <a href="<?php echo htmlspecialchars($current_url); ?>" 
               target="_blank" class="btn" style="background: #28a745;">
               📰 View Actual News Post
            </a>
            <a href="javascript:void(0);" 
               onclick="shareToFacebook()" class="btn" style="background: #1877f2;">
               📱 Test Facebook Share
            </a>
        </div>
        
        <div class="debug-info">
            <h3>📋 Instructions</h3>
            <ol>
                <li><strong>Test Facebook Debugger:</strong> Click the "Test in Facebook Debugger" button above</li>
                <li><strong>Scrape URL:</strong> In the Facebook Debugger, click "Scrape Again" to refresh the cache</li>
                <li><strong>Check Results:</strong> Verify that the title, description, and image appear correctly</li>
                <li><strong>Test Sharing:</strong> Use the "Test Facebook Share" button to see the actual sharing experience</li>
            </ol>
        </div>
        
        <div class="debug-info">
            <h3>🔧 Troubleshooting</h3>
            <ul>
                <li><strong>No image showing:</strong> Check if the image URL is accessible publicly</li>
                <li><strong>Blurry image:</strong> Ensure image is at least 1200x630px</li>
                <li><strong>Old content showing:</strong> Use Facebook Debugger to refresh the cache</li>
                <li><strong>No preview at all:</strong> Check if your server allows external access to the page</li>
            </ul>
        </div>
    </div>
    
    <script>
        function shareToFacebook() {
            const url = '<?php echo addslashes($current_url); ?>';
            const encodedUrl = encodeURIComponent(url);
            const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`;
            
            const width = 626;
            const height = 436;
            const left = (screen.width - width) / 2;
            const top = (screen.height - height) / 2;
            
            window.open(
                facebookUrl,
                'facebook-share',
                `width=${width},height=${height},left=${left},top=${top},location=0,menubar=0,toolbar=0,status=0,scrollbars=1,resizable=1`
            );
        }
    </script>
</body>
</html>
