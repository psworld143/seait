
<?php
// Direct test for Facebook scraper access
header('Content-Type: text/html; charset=utf-8');

$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$is_facebook_bot = preg_match('/facebookexternalhit/i', $user_agent);

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Facebook Scraper Test</title></head><body>\n";
echo "<h1>Facebook Scraper Access Test</h1>\n";
echo "<p><strong>User Agent:</strong> " . htmlspecialchars($user_agent) . "</p>\n";
echo "<p><strong>Is Facebook Bot:</strong> " . ($is_facebook_bot ? 'YES' : 'NO') . "</p>\n";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
echo "<p><strong>Request URI:</strong> " . htmlspecialchars($_SERVER['REQUEST_URI']) . "</p>\n";

if ($is_facebook_bot) {
    echo "<div style='background: green; color: white; padding: 10px; margin: 10px 0;'>";
    echo "<h2>✅ Facebook Scraper Detected - Access Granted</h2>";
    echo "<p>This page is accessible to Facebook's scraper.</p>";
    echo "</div>";
    
    // Test Open Graph meta tags
    echo "<h2>Open Graph Meta Tags Test</h2>";
    echo "<meta property='og:title' content='Facebook Scraper Test - SEAIT'>";
    echo "<meta property='og:description' content='This is a test page to verify Facebook scraper access to SEAIT website.'>";
    echo "<meta property='og:url' content='http://home.seait-edu.ph/test-facebook-direct.php'>";
    echo "<meta property='og:image' content='http://home.seait-edu.ph/assets/images/seait-logo.png'>";
    echo "<meta property='og:type' content='website'>";
    echo "<p>✅ Open Graph meta tags generated successfully</p>";
} else {
    echo "<div style='background: orange; color: white; padding: 10px; margin: 10px 0;'>";
    echo "<h2>⚠️ Regular Browser Access</h2>";
    echo "<p>This is a regular browser, not Facebook's scraper.</p>";
    echo "</div>";
}

echo "<h2>Test Links</h2>";
echo "<p><a href='news-detail.php?id=9k0cZo0u5QpVRbw0UXOaek0R78Rh-A-O0HuraqT_zEw='>Test News Detail Page</a></p>";
echo "<p><a href='test-403-fix.php'>Test 403 Fix</a></p>";

echo "</body></html>";
?>
