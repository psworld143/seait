<?php
// Test script to verify Facebook scraper can access your pages
$test_url = $_GET['url'] ?? '';

if (empty($test_url)) {
    echo '<h1>Facebook Scraper Access Test</h1>';
    echo '<p>Enter a URL to test if Facebook\'s scraper can access it:</p>';
    echo '<form method="GET">';
    echo '<input type="url" name="url" placeholder="https://home.seait-edu.ph/news-detail.php?id=..." style="width: 500px; padding: 10px;">';
    echo '<button type="submit" style="padding: 10px 20px;">Test Access</button>';
    echo '</form>';
    exit();
}

// Test with Facebook's user agent
$context = stream_context_create([
    'http' => [
        'user_agent' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
        'timeout' => 30,
        'follow_location' => false, // Don't follow redirects
        'ignore_errors' => true
    ]
]);

// Get response headers first
$headers = @get_headers($test_url, 1, $context);

if ($headers === false) {
    echo '<h1>Error</h1>';
    echo '<p>Could not fetch headers for the URL.</p>';
    exit();
}

// Extract status code
$status_line = $headers[0];
$status_code = '';
if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $status_line, $matches)) {
    $status_code = $matches[1];
}

// Fetch the page content
$content = @file_get_contents($test_url, false, $context);

echo '<h1>Facebook Scraper Access Test Results</h1>';
echo '<p><strong>Tested URL:</strong> <code>' . htmlspecialchars($test_url) . '</code></p>';

echo '<h2>Response Information</h2>';
echo '<p><strong>Status Code:</strong> ' . $status_code . '</p>';
echo '<p><strong>Status Line:</strong> ' . htmlspecialchars($status_line) . '</p>';

if ($status_code == '200') {
    echo '<p style="color: green;">✅ <strong>SUCCESS:</strong> Facebook scraper can access this page!</p>';
} elseif ($status_code == '403') {
    echo '<p style="color: red;">❌ <strong>BLOCKED:</strong> Facebook scraper is being blocked (403 Forbidden)</p>';
    echo '<p>This is likely due to robots.txt or .htaccess restrictions.</p>';
} elseif ($status_code == '404') {
    echo '<p style="color: orange;">⚠️ <strong>NOT FOUND:</strong> Page not found (404)</p>';
} else {
    echo '<p style="color: red;">❌ <strong>ERROR:</strong> Unexpected status code: ' . $status_code . '</p>';
}

echo '<h2>Response Headers</h2>';
echo '<pre>';
foreach ($headers as $key => $value) {
    if (is_array($value)) {
        echo htmlspecialchars($key) . ': ' . htmlspecialchars(implode(', ', $value)) . "\n";
    } else {
        echo htmlspecialchars($key) . ': ' . htmlspecialchars($value) . "\n";
    }
}
echo '</pre>';

if ($content !== false && $status_code == '200') {
    echo '<h2>Content Preview</h2>';
    echo '<p><strong>Content Length:</strong> ' . strlen($content) . ' bytes</p>';
    
    // Check for Open Graph tags
    $og_tags = [];
    $pattern = '/<meta\s+(?:property|name)=["\'](og:|twitter:)([^"\']+)["\']\s+content=["\']([^"\']+)["\'][^>]*>/i';
    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $og_tags[$match[1] . $match[2]] = $match[3];
    }
    
    if (!empty($og_tags)) {
        echo '<p style="color: green;">✅ <strong>Open Graph tags found:</strong></p>';
        echo '<ul>';
        foreach ($og_tags as $property => $content) {
            echo '<li><strong>' . htmlspecialchars($property) . ':</strong> ' . htmlspecialchars($content) . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p style="color: red;">❌ <strong>No Open Graph tags found</strong></p>';
    }
    
    // Show first 500 characters of content
    echo '<h3>Content Preview (first 500 characters)</h3>';
    echo '<pre>' . htmlspecialchars(substr($content, 0, 500)) . '...</pre>';
} else {
    echo '<h2>Content</h2>';
    echo '<p style="color: red;">❌ Could not fetch page content</p>';
}

echo '<h2>Next Steps</h2>';
if ($status_code == '200' && !empty($og_tags)) {
    echo '<p style="color: green;">✅ Everything looks good! Facebook should be able to scrape your page and show proper previews.</p>';
    echo '<p><a href="https://developers.facebook.com/tools/debug/?q=' . urlencode($test_url) . '" target="_blank">Test in Facebook Debugger</a></p>';
} elseif ($status_code == '403') {
    echo '<p style="color: red;">❌ Facebook scraper is being blocked. Check your robots.txt and .htaccess files.</p>';
    echo '<p>Make sure you have the updated robots.txt and .htaccess files I provided.</p>';
} else {
    echo '<p>Please check the issues above and try again.</p>';
}

echo '<p><a href="test-facebook-access.php">Test Another URL</a></p>';
?>
