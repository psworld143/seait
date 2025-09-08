<?php
// Test script to verify Open Graph meta tags work with encrypted URLs
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/id_encryption.php';

// Get a sample post
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

// Generate encrypted ID
$encrypted_id = encrypt_id($post['id']);
$test_url = "https://" . $_SERVER['HTTP_HOST'] . "/news-detail.php?id=" . $encrypted_id;

// Simulate the same logic as news-detail.php
$og_image_url = '';
$og_image_width = 1200;
$og_image_height = 630;

// Clean and prepare description
$og_description = $post['content'];
$og_description = strip_tags($og_description);
$og_description = preg_replace('/\s+/', ' ', $og_description);
$og_description = trim($og_description);
$og_description = html_entity_decode($og_description, ENT_QUOTES, 'UTF-8');

if (strlen($og_description) > 200) {
    $og_description = substr($og_description, 0, 197) . '...';
}
$og_description = htmlspecialchars($og_description, ENT_QUOTES, 'UTF-8');

// Check if post has a featured image
if (!empty($post['image_url']) && trim($post['image_url']) !== '') {
    $image_path = trim($post['image_url']);
    
    if (strpos($image_path, 'http') !== 0) {
        $image_path = ltrim($image_path, '/');
        $og_image_url = "https://" . $_SERVER['HTTP_HOST'] . "/" . $image_path;
    } else {
        $og_image_url = $image_path;
    }
} else {
    $og_image_url = "https://" . $_SERVER['HTTP_HOST'] . "/assets/images/seait-logo.png";
}

// Prepare author name
$author_name = $post['author'] ?? ($post['first_name'] . ' ' . $post['last_name']);
$author_name = trim($author_name);
if (empty($author_name)) {
    $author_name = 'SEAIT';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encrypted URL Open Graph Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .container { max-width: 1000px; margin: 0 auto; }
        .test-section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .test-section h3 { margin-top: 0; color: #333; }
        .meta-tag { background: #fff; padding: 8px; margin: 5px 0; border-left: 3px solid #007cba; }
        .meta-tag code { background: #e8e8e8; padding: 2px 4px; border-radius: 3px; }
        .test-buttons { margin: 20px 0; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; background: #1877f2; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #166fe5; }
        .btn-success { background: #28a745; }
        .btn-warning { background: #ffc107; color: #000; }
        .preview-image { max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 8px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .url-box { background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 5px; word-break: break-all; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Encrypted URL Open Graph Test</h1>
        <p>This page tests Open Graph meta tags with encrypted URLs to ensure Facebook sharing works properly.</p>
        
        <div class="test-section">
            <h3>📊 Post Information</h3>
            <p><strong>Post ID:</strong> <?php echo $post['id']; ?></p>
            <p><strong>Encrypted ID:</strong> <code><?php echo htmlspecialchars($encrypted_id); ?></code></p>
            <p><strong>Title:</strong> <?php echo htmlspecialchars($post['title']); ?></p>
            <p><strong>Type:</strong> <?php echo ucfirst($post['type']); ?></p>
            <p><strong>Author:</strong> <?php echo htmlspecialchars($author_name); ?></p>
            <p><strong>Created:</strong> <?php echo date('F d, Y', strtotime($post['created_at'])); ?></p>
        </div>
        
        <div class="test-section">
            <h3>🔗 Test URLs</h3>
            <p><strong>Encrypted URL:</strong></p>
            <div class="url-box"><?php echo htmlspecialchars($test_url); ?></div>
            <p><strong>Simple URL (for comparison):</strong></p>
            <div class="url-box"><?php echo htmlspecialchars("https://" . $_SERVER['HTTP_HOST'] . "/news-detail.php?id=" . $post['id']); ?></div>
        </div>
        
        <div class="test-section">
            <h3>🖼️ Image Information</h3>
            <p><strong>Featured Image:</strong> 
                <?php if (!empty($post['image_url'])): ?>
                    <span class="success">✓ Has featured image</span><br>
                    <code><?php echo htmlspecialchars($post['image_url']); ?></code>
                <?php else: ?>
                    <span class="warning">⚠️ No featured image (using fallback)</span>
                <?php endif; ?>
            </p>
            <p><strong>OG Image URL:</strong> <code><?php echo htmlspecialchars($og_image_url); ?></code></p>
            <p><strong>Image Preview:</strong></p>
            <img src="<?php echo htmlspecialchars($og_image_url); ?>" alt="Preview" class="preview-image" style="max-width: 400px;">
        </div>
        
        <div class="test-section">
            <h3>📝 Generated Meta Tags</h3>
            <p>These are the exact meta tags that will be sent to Facebook:</p>
            
            <div class="meta-tag">
                <strong>og:title</strong><br>
                <code>&lt;meta property="og:title" content="<?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>"&gt;</code>
            </div>
            
            <div class="meta-tag">
                <strong>og:description</strong><br>
                <code>&lt;meta property="og:description" content="<?php echo $og_description; ?>"&gt;</code>
            </div>
            
            <div class="meta-tag">
                <strong>og:url</strong><br>
                <code>&lt;meta property="og:url" content="<?php echo htmlspecialchars($test_url, ENT_QUOTES, 'UTF-8'); ?>"&gt;</code>
            </div>
            
            <div class="meta-tag">
                <strong>og:image</strong><br>
                <code>&lt;meta property="og:image" content="<?php echo htmlspecialchars($og_image_url, ENT_QUOTES, 'UTF-8'); ?>"&gt;</code>
            </div>
        </div>
        
        <div class="test-buttons">
            <h3>🧪 Test Tools</h3>
            <a href="https://developers.facebook.com/tools/debug/?q=<?php echo urlencode($test_url); ?>" 
               target="_blank" class="btn">
               🔍 Test Encrypted URL in Facebook Debugger
            </a>
            <a href="<?php echo htmlspecialchars($test_url); ?>" 
               target="_blank" class="btn btn-success">
               📰 View Encrypted URL Post
            </a>
            <a href="javascript:void(0);" 
               onclick="shareToFacebook()" class="btn btn-warning">
               📱 Test Facebook Share
            </a>
        </div>
        
        <div class="test-section">
            <h3>📋 Testing Instructions</h3>
            <ol>
                <li><strong>Copy the encrypted URL:</strong> Use the encrypted URL shown above</li>
                <li><strong>Test in Facebook Debugger:</strong> Click "Test Encrypted URL in Facebook Debugger"</li>
                <li><strong>Scrape Again:</strong> In Facebook Debugger, click "Scrape Again" to refresh the cache</li>
                <li><strong>Check Results:</strong> Verify that the title, description, and image appear correctly</li>
                <li><strong>Test Sharing:</strong> Use the "Test Facebook Share" button to see the actual sharing experience</li>
            </ol>
        </div>
        
        <div class="test-section">
            <h3>🔧 Troubleshooting</h3>
            <ul>
                <li><strong>If Facebook shows only domain name:</strong> Use Facebook Debugger to refresh the cache</li>
                <li><strong>If image doesn't show:</strong> Check if the image URL is accessible publicly</li>
                <li><strong>If description is missing:</strong> Verify the post has content</li>
                <li><strong>If still not working:</strong> Check server error logs for bot access logs</li>
            </ul>
        </div>
        
        <div class="test-section">
            <h3>📊 Debug Information</h3>
            <p><strong>User Agent Detection:</strong> This page will show if you're accessing as a bot</p>
            <p><strong>Server Logs:</strong> Check your server error logs for bot access information</p>
            <p><strong>Current User Agent:</strong> <code><?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Not set'); ?></code></p>
        </div>
    </div>
    
    <script>
        function shareToFacebook() {
            const url = '<?php echo addslashes($test_url); ?>';
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
