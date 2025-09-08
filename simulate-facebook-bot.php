<?php
// Simulate Facebook bot to test Open Graph meta tags
$test_url = $_GET['url'] ?? '';

if (empty($test_url)) {
    echo '<h1>Facebook Bot Simulator</h1>';
    echo '<p>Enter a URL to test how Facebook\'s scraper sees it:</p>';
    echo '<form method="GET">';
    echo '<input type="url" name="url" placeholder="https://home.seait-edu.ph/news-detail.php?id=..." style="width: 500px; padding: 10px;">';
    echo '<button type="submit" style="padding: 10px 20px;">Test URL</button>';
    echo '</form>';
    exit();
}

// Set Facebook bot user agent
$context = stream_context_create([
    'http' => [
        'user_agent' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
        'timeout' => 30
    ]
]);

// Fetch the page content
$content = @file_get_contents($test_url, false, $context);

if ($content === false) {
    echo '<h1>Error</h1>';
    echo '<p>Could not fetch the URL. Please check if the URL is correct and accessible.</p>';
    echo '<p><a href="simulate-facebook-bot.php">Try another URL</a></p>';
    exit();
}

// Extract Open Graph meta tags
$og_tags = [];
$pattern = '/<meta\s+(?:property|name)=["\'](og:|twitter:)([^"\']+)["\']\s+content=["\']([^"\']+)["\'][^>]*>/i';
preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

foreach ($matches as $match) {
    $og_tags[$match[1] . $match[2]] = $match[3];
}

// Extract title
$title_pattern = '/<title[^>]*>([^<]+)<\/title>/i';
preg_match($title_pattern, $content, $title_match);
$page_title = $title_match[1] ?? 'No title found';

// Extract description
$desc_pattern = '/<meta\s+name=["\']description["\']\s+content=["\']([^"\']+)["\'][^>]*>/i';
preg_match($desc_pattern, $content, $desc_match);
$page_description = $desc_match[1] ?? 'No description found';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facebook Bot Simulator Results</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .container { max-width: 1000px; margin: 0 auto; }
        .result-section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .result-section h3 { margin-top: 0; color: #333; }
        .meta-tag { background: #fff; padding: 8px; margin: 5px 0; border-left: 3px solid #007cba; }
        .meta-tag code { background: #e8e8e8; padding: 2px 4px; border-radius: 3px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .preview-image { max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 8px; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; background: #1877f2; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #166fe5; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 Facebook Bot Simulator Results</h1>
        <p><strong>Tested URL:</strong> <code><?php echo htmlspecialchars($test_url); ?></code></p>
        
        <div class="result-section">
            <h3>📄 Page Information</h3>
            <p><strong>Page Title:</strong> <?php echo htmlspecialchars($page_title); ?></p>
            <p><strong>Page Description:</strong> <?php echo htmlspecialchars($page_description); ?></p>
        </div>
        
        <div class="result-section">
            <h3>🔍 Open Graph Meta Tags Found</h3>
            <?php if (empty($og_tags)): ?>
                <p class="error">❌ No Open Graph meta tags found!</p>
            <?php else: ?>
                <?php foreach ($og_tags as $property => $content): ?>
                    <div class="meta-tag">
                        <strong><?php echo htmlspecialchars($property); ?></strong><br>
                        <code><?php echo htmlspecialchars($content); ?></code>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="result-section">
            <h3>🖼️ Preview Image</h3>
            <?php if (isset($og_tags['og:image'])): ?>
                <p><strong>Image URL:</strong> <code><?php echo htmlspecialchars($og_tags['og:image']); ?></code></p>
                <img src="<?php echo htmlspecialchars($og_tags['og:image']); ?>" alt="Preview" class="preview-image" style="max-width: 400px;" onerror="this.parentElement.innerHTML+='<p class=\'error\'>❌ Image failed to load</p>'">
            <?php else: ?>
                <p class="error">❌ No preview image found</p>
            <?php endif; ?>
        </div>
        
        <div class="result-section">
            <h3>📊 Facebook Sharing Preview</h3>
            <div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; background: #f8f9fa;">
                <h4 style="margin: 0 0 5px 0; color: #1877f2;"><?php echo htmlspecialchars($og_tags['og:title'] ?? $page_title); ?></h4>
                <p style="margin: 0 0 5px 0; color: #666; font-size: 14px;"><?php echo htmlspecialchars($og_tags['og:description'] ?? $page_description); ?></p>
                <p style="margin: 0; color: #999; font-size: 12px;"><?php echo htmlspecialchars(parse_url($test_url, PHP_URL_HOST)); ?></p>
                <?php if (isset($og_tags['og:image'])): ?>
                    <img src="<?php echo htmlspecialchars($og_tags['og:image']); ?>" alt="Preview" style="width: 100%; max-width: 400px; height: auto; margin-top: 10px; border-radius: 4px;" onerror="this.style.display='none'">
                <?php endif; ?>
            </div>
        </div>
        
        <div class="result-section">
            <h3>🔧 Test Tools</h3>
            <a href="https://developers.facebook.com/tools/debug/?q=<?php echo urlencode($test_url); ?>" 
               target="_blank" class="btn">
               🔍 Test in Facebook Debugger
            </a>
            <a href="<?php echo htmlspecialchars($test_url); ?>" 
               target="_blank" class="btn">
               📰 View Original Page
            </a>
            <a href="simulate-facebook-bot.php" class="btn">
               🔄 Test Another URL
            </a>
        </div>
        
        <div class="result-section">
            <h3>📋 Analysis</h3>
            <ul>
                <?php if (isset($og_tags['og:title'])): ?>
                    <li class="success">✅ og:title found</li>
                <?php else: ?>
                    <li class="error">❌ og:title missing</li>
                <?php endif; ?>
                
                <?php if (isset($og_tags['og:description'])): ?>
                    <li class="success">✅ og:description found</li>
                <?php else: ?>
                    <li class="error">❌ og:description missing</li>
                <?php endif; ?>
                
                <?php if (isset($og_tags['og:image'])): ?>
                    <li class="success">✅ og:image found</li>
                <?php else: ?>
                    <li class="error">❌ og:image missing</li>
                <?php endif; ?>
                
                <?php if (isset($og_tags['og:url'])): ?>
                    <li class="success">✅ og:url found</li>
                <?php else: ?>
                    <li class="error">❌ og:url missing</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>
