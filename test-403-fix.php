<?php
// Test script to check if 403 error is fixed for Facebook scraper
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$is_facebook_bot = preg_match('/facebookexternalhit/i', $user_agent);

echo '<h1>403 Fix Test</h1>';
echo '<p><strong>Current User Agent:</strong> ' . htmlspecialchars($user_agent) . '</p>';
echo '<p><strong>Is Facebook Bot:</strong> ' . ($is_facebook_bot ? 'YES' : 'NO') . '</p>';

if ($is_facebook_bot) {
    echo '<p style="color: green;">✅ Facebook scraper detected - this page should be accessible!</p>';
} else {
    echo '<p style="color: blue;">ℹ️ Regular browser - simulating Facebook scraper test...</p>';
}

// Test the news-detail.php with a sample encrypted ID
$test_url = "https://" . $_SERVER['HTTP_HOST'] . "/news-detail.php?id=9k0cZo0u5QpVRbw0UXOaek0R78Rh-A-O0HuraqT_zEw=";

echo '<h2>Test Facebook Scraper Access</h2>';
echo '<p><strong>Test URL:</strong> <code>' . htmlspecialchars($test_url) . '</code></p>';

// Simulate Facebook scraper request
$context = stream_context_create([
    'http' => [
        'user_agent' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
        'timeout' => 30,
        'follow_location' => false,
        'ignore_errors' => true
    ]
]);

// Get headers first
$headers = @get_headers($test_url, 1, $context);

if ($headers === false) {
    echo '<p style="color: red;">❌ Could not fetch headers</p>';
} else {
    $status_line = $headers[0];
    $status_code = '';
    if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $status_line, $matches)) {
        $status_code = $matches[1];
    }
    
    echo '<p><strong>Status Code:</strong> ' . $status_code . '</p>';
    echo '<p><strong>Status Line:</strong> ' . htmlspecialchars($status_line) . '</p>';
    
    if ($status_code == '200') {
        echo '<p style="color: green;">✅ SUCCESS! Facebook scraper can access the page (200 OK)</p>';
    } elseif ($status_code == '403') {
        echo '<p style="color: red;">❌ STILL BLOCKED! Facebook scraper is getting 403 Forbidden</p>';
        echo '<p>This means the .htaccess changes may not have taken effect yet, or there are other restrictions.</p>';
    } else {
        echo '<p style="color: orange;">⚠️ Unexpected status code: ' . $status_code . '</p>';
    }
}

echo '<h2>Next Steps</h2>';
if ($status_code == '200') {
    echo '<p style="color: green;">✅ The fix is working! You can now test in Facebook Debugger.</p>';
    echo '<p><a href="https://developers.facebook.com/tools/debug/?q=' . urlencode($test_url) . '" target="_blank">Test in Facebook Debugger</a></p>';
} else {
    echo '<p style="color: red;">❌ The fix is not working yet. Try these steps:</p>';
    echo '<ol>';
    echo '<li>Wait 5-10 minutes for server changes to take effect</li>';
    echo '<li>Clear any server cache if you have caching enabled</li>';
    echo '<li>Check if your hosting provider has additional restrictions</li>';
    echo '<li>Contact your hosting provider if the issue persists</li>';
    echo '</ol>';
}

echo '<h2>Debug Information</h2>';
echo '<p><strong>Server Software:</strong> ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '</p>';
echo '<p><strong>Document Root:</strong> ' . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . '</p>';
echo '<p><strong>Script Name:</strong> ' . ($_SERVER['SCRIPT_NAME'] ?? 'Unknown') . '</p>';

// Check if .htaccess file exists and is readable
if (file_exists('.htaccess')) {
    echo '<p style="color: green;">✅ .htaccess file exists</p>';
    $htaccess_content = file_get_contents('.htaccess');
    if (strpos($htaccess_content, 'facebookexternalhit') !== false) {
        echo '<p style="color: green;">✅ .htaccess contains Facebook scraper rules</p>';
    } else {
        echo '<p style="color: red;">❌ .htaccess does not contain Facebook scraper rules</p>';
    }
} else {
    echo '<p style="color: red;">❌ .htaccess file not found</p>';
}

// Check if robots.txt exists
if (file_exists('robots.txt')) {
    echo '<p style="color: green;">✅ robots.txt file exists</p>';
    $robots_content = file_get_contents('robots.txt');
    if (strpos($robots_content, 'facebookexternalhit') !== false) {
        echo '<p style="color: green;">✅ robots.txt allows Facebook scraper</p>';
    } else {
        echo '<p style="color: red;">❌ robots.txt does not allow Facebook scraper</p>';
    }
} else {
    echo '<p style="color: red;">❌ robots.txt file not found</p>';
}
?>
