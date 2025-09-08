<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/id_encryption.php';

// Check if this is a Facebook scraper or similar bot
$is_bot = false;
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
if (preg_match('/facebookexternalhit|twitterbot|linkedinbot|whatsapp|telegram/i', $user_agent)) {
    $is_bot = true;
}

if (!isset($_GET['id'])) {
    if ($is_bot) {
        // For bots, show a basic page instead of redirecting
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><title>Page Not Found - SEAIT</title></head><body><h1>Page Not Found</h1></body></html>';
        exit();
    }
    header("Location: index.php");
    exit();
}

$post_id = safe_decrypt_id($_GET['id']);

if (!$post_id) {
    if ($is_bot) {
        // For bots, show a basic page instead of redirecting
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><title>Page Not Found - SEAIT</title></head><body><h1>Page Not Found</h1></body></html>';
        exit();
    }
    header("Location: index.php");
    exit();
}

$query = "SELECT p.*, u.first_name, u.last_name FROM posts p
          LEFT JOIN users u ON p.author_id = u.id
          WHERE p.id = ? AND p.status = 'approved'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $post_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$post = mysqli_fetch_assoc($result)) {
    if ($is_bot) {
        // For bots, show a basic page instead of redirecting
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><title>Page Not Found - SEAIT</title></head><body><h1>Page Not Found</h1></body></html>';
        exit();
    }
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - SEAIT</title>
    <!-- Favicon Configuration -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link rel="icon" type="image/png" href="assets/images/seait-logo.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link rel="shortcut icon" type="image/png" href="assets/images/seait-logo.png">
    <link rel="apple-touch-icon" type="image/png" href="assets/images/seait-logo.png">
    <link rel="apple-touch-icon-precomposed" type="image/png" href="assets/images/seait-logo.png">
    <meta name="msapplication-TileImage" content="assets/images/seait-logo.png">
    <meta name="msapplication-TileColor" content="#FF6B35">




    <!-- Open Graph Meta Tags for Social Media Sharing -->
    <?php
    // Enhanced Facebook sharing with better image handling
    $og_image_url = '';
    $og_image_width = 1200;
    $og_image_height = 630;
    $og_image_alt = htmlspecialchars($post['title']);
    
    // Clean and prepare description - more robust cleaning
    $og_description = $post['content'];
    $og_description = strip_tags($og_description);
    $og_description = preg_replace('/\s+/', ' ', $og_description); // Remove extra whitespace
    $og_description = trim($og_description);
    $og_description = html_entity_decode($og_description, ENT_QUOTES, 'UTF-8'); // Decode HTML entities
    
    // Limit description length for better Facebook display
    if (strlen($og_description) > 200) {
        $og_description = substr($og_description, 0, 197) . '...';
    }
    $og_description = htmlspecialchars($og_description, ENT_QUOTES, 'UTF-8');
    
    // Build current page URL - ensure it's the exact URL being shared
    $current_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    // Check if post has a featured image
    if (!empty($post['image_url']) && trim($post['image_url']) !== '') {
        $image_path = trim($post['image_url']);
        
        // Ensure absolute URL for Facebook
        if (strpos($image_path, 'http') !== 0) {
            $image_path = ltrim($image_path, '/');
            $og_image_url = "https://" . $_SERVER['HTTP_HOST'] . "/" . $image_path;
        } else {
            $og_image_url = $image_path;
        }
        
        // Try to get actual image dimensions for better quality
        $local_path = str_replace("https://" . $_SERVER['HTTP_HOST'] . "/", "", $og_image_url);
        $local_path = str_replace("http://" . $_SERVER['HTTP_HOST'] . "/", "", $local_path);
        
        if (file_exists($local_path)) {
            $image_info = @getimagesize($local_path);
            if ($image_info !== false) {
                $og_image_width = $image_info[0];
                $og_image_height = $image_info[1];
            }
        }
        
        $og_image_alt = "Featured image for: " . htmlspecialchars($post['title']);
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
    
    // Prepare author name
    $author_name = $post['author'] ?? ($post['first_name'] . ' ' . $post['last_name']);
    $author_name = trim($author_name);
    if (empty($author_name)) {
        $author_name = 'SEAIT';
    }
    ?>
    
    <!-- Essential Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:description" content="<?php echo $og_description; ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($current_url, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="SEAIT - South East Asian Institute of Technology, Inc.">
    
    <!-- Image Meta Tags -->
    <meta property="og:image" content="<?php echo htmlspecialchars($og_image_url, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image:secure_url" content="<?php echo htmlspecialchars($og_image_url, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image:width" content="<?php echo $og_image_width; ?>">
    <meta property="og:image:height" content="<?php echo $og_image_height; ?>">
    <meta property="og:image:alt" content="<?php echo htmlspecialchars($og_image_alt, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image:type" content="image/jpeg">
    
    <!-- Additional Meta Tags -->
    <meta property="og:locale" content="en_US">
    <meta property="og:updated_time" content="<?php echo time(); ?>">
    
    <!-- Article Specific Meta Tags -->
    <meta property="article:published_time" content="<?php echo date('c', strtotime($post['created_at'])); ?>">
    <meta property="article:author" content="<?php echo htmlspecialchars($author_name, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="article:section" content="<?php echo ucfirst($post['type']); ?>">
    <meta property="article:tag" content="SEAIT,<?php echo ucfirst($post['type']); ?>,Education,News">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:description" content="<?php echo $og_description; ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($og_image_url, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:image:alt" content="<?php echo htmlspecialchars($og_image_alt, ENT_QUOTES, 'UTF-8'); ?>">
    
    <!-- Standard Meta Tags -->
    <meta name="description" content="<?php echo $og_description; ?>">
    <meta name="author" content="<?php echo htmlspecialchars($author_name, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="robots" content="index, follow">
    
    <!-- Debug Information (Hidden Comments) -->
    <!-- 
    DEBUG INFO FOR FACEBOOK SHARING:
    Post ID: <?php echo $post_id; ?>
    Post Title: <?php echo htmlspecialchars($post['title']); ?>
    Post Type: <?php echo $post['type']; ?>
    Author: <?php echo htmlspecialchars($author_name); ?>
    Image URL: <?php echo htmlspecialchars($post['image_url'] ?? 'No featured image'); ?>
    OG Image URL: <?php echo htmlspecialchars($og_image_url); ?>
    OG Image Dimensions: <?php echo $og_image_width; ?>x<?php echo $og_image_height; ?>
    Current URL: <?php echo htmlspecialchars($current_url); ?>
    Description Length: <?php echo strlen($og_description); ?> chars
    User Agent: <?php echo htmlspecialchars($user_agent); ?>
    Is Bot: <?php echo $is_bot ? 'Yes' : 'No'; ?>
    Facebook Debugger: https://developers.facebook.com/tools/debug/?q=<?php echo urlencode($current_url); ?>
    -->


    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'seait-orange': '#FF6B35',
                        'seait-dark': '#2C3E50',
                        'seait-light': '#FFF8F0'
                    },
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <script src="assets/js/dark-mode.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .prose {
            max-width: none;
        }
        .prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
            color: #2C3E50;
            font-weight: 600;
            margin-top: 1.5em;
            margin-bottom: 0.5em;
        }
        .prose h1 { font-size: 2.5rem; }
        .prose h2 { font-size: 2rem; }
        .prose h3 { font-size: 1.75rem; }
        .prose h4 { font-size: 1.5rem; }
        .prose p {
            margin-bottom: 1.5em;
            line-height: 1.8;
            font-size: 1.1rem;
        }
        .prose ul, .prose ol {
            margin-bottom: 1.5em;
            padding-left: 1.5em;
        }
        .prose li {
            margin-bottom: 0.75em;
            line-height: 1.7;
        }
        .prose blockquote {
            border-left: 4px solid #FF6B35;
            padding-left: 1.5rem;
            margin: 2rem 0;
            font-style: italic;
            color: #6B7280;
            font-size: 1.1rem;
        }
        .prose table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
        }
        .prose table th, .prose table td {
            border: 1px solid #E5E7EB;
            padding: 1rem;
            text-align: left;
        }
        .prose table th {
            background-color: #F9FAFB;
            font-weight: 600;
        }
        .prose a {
            color: #FF6B35;
            text-decoration: underline;
        }
        .prose a:hover {
            color: #EA580C;
        }
        .prose img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 2rem 0;
        }
        .prose code {
            background-color: #F3F4F6;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .prose pre {
            background-color: #1F2937;
            color: #F9FAFB;
            padding: 1.5rem;
            border-radius: 8px;
            overflow-x: auto;
            margin: 2rem 0;
        }

        /* Active navbar link styles */
        .navbar-link-active {
            color: #FF6B35 !important;
            font-weight: 600;
        }
        .navbar-link-active:hover {
            color: #FF6B35 !important;
        }

        /* Enhanced image styles for news detail - Landscape */
        .article-header-image {
            background-size: cover !important;
            background-position: center center !important;
            background-repeat: no-repeat !important;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
            image-rendering: high-quality;
            -webkit-backface-visibility: hidden;
            backface-visibility: hidden;
            transform: translateZ(0);
            -webkit-transform: translateZ(0);
            aspect-ratio: 16/9 !important;
            width: 100% !important;
        }

        /* Responsive landscape image sizing */
        @media (min-width: 768px) {
            .article-header-image {
                aspect-ratio: 21/9 !important;
                min-height: 60vh !important;
            }
        }

        @media (min-width: 1024px) {
            .article-header-image {
                aspect-ratio: 21/9 !important;
                min-height: 70vh !important;
            }
        }

        @media (min-width: 1280px) {
            .article-header-image {
                aspect-ratio: 21/9 !important;
                min-height: 80vh !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50 dark-mode" data-theme="light">
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-12">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Article Header -->
            <?php
                $hasImage = !empty($post['image_url']);
                $imagePath =  htmlspecialchars($post['image_url']);
                $headerStyle = $hasImage
                    ? "background: linear-gradient(rgba(44,62,80,0.3), rgba(44,62,80,0.3)), url('{$imagePath}') center center / cover no-repeat;"
                    : "";
            ?>
            <!-- DEBUG: image_url = <?php echo $post['image_url']; ?>, hasImage = <?php echo $hasImage ? 'true' : 'false'; ?>, imagePath = <?php echo $imagePath; ?> -->
            <div class="<?php echo $hasImage ? 'article-header-image' : 'bg-gradient-to-r from-seait-orange to-orange-600'; ?> text-white relative" style="<?php echo $headerStyle; ?> min-height:400px; min-height:50vh;">
                <div class="absolute left-0 bottom-0 z-10 max-w-3xl w-full p-4 md:p-6">
                    <div class="flex items-center space-x-4 mb-2">
                        <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-medium">
                            <?php echo ucfirst($post['type']); ?>
                        </span>
                        <span class="text-xs opacity-90">
                            <?php echo date('F d, Y', strtotime($post['created_at'])); ?>
                        </span>
                    </div>
                    <h1 class="text-xl md:text-2xl lg:text-3xl font-bold mb-1"><?php echo htmlspecialchars($post['title']); ?></h1>
                    <p class="text-sm md:text-base opacity-90 mb-0">
                        <?php echo htmlspecialchars($post['author'] ?? $post['first_name'] . ' ' . $post['last_name']); ?>
                    </p>
                </div>
            </div>

                         <!-- Article Content -->
             <div class="p-8 md:p-12">
                 <div class="prose prose-lg md:prose-xl max-w-none">
                     <?php echo $post['content']; ?>
                 </div>
                 
                                   <!-- Additional Images Section -->
                  <?php 
                  $additional_images = [];
                  if (!empty($post['additional_image_url'])) {
                      $decoded = json_decode($post['additional_image_url'], true);
                      if (json_last_error() === JSON_ERROR_NONE) {
                          $additional_images = $decoded;
                      } else {
                          $additional_images = [$post['additional_image_url']];
                      }
                  }
                  
                  // Debug: Check what's in the additional_image_url field
                  echo "<!-- DEBUG: additional_image_url = " . htmlspecialchars($post['additional_image_url']) . " -->";
                  echo "<!-- DEBUG: additional_images count = " . count($additional_images) . " -->";
                  echo "<!-- DEBUG: additional_images = " . htmlspecialchars(json_encode($additional_images)) . " -->";
                  ?>
                  <div class="mt-8 border-t border-gray-200 pt-8">
                      <div class="flex items-center justify-between mb-4">
                          <h3 class="text-xl font-semibold text-seait-dark">Additional Images</h3>
                          <?php if (!empty($additional_images)): ?>
                          <button onclick="openImageGallery(0)" class="bg-seait-orange hover:bg-orange-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                              <i class="fas fa-images"></i>
                              <span>View All Images (<?php echo count($additional_images); ?>)</span>
                          </button>
                          <?php else: ?>
                          <div class="text-gray-500 text-sm">
                              <i class="fas fa-info-circle"></i>
                              <span>No additional images available</span>
                          </div>
                          <?php endif; ?>
                      </div>
                      <?php if (!empty($additional_images)): ?>
                      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                          <?php foreach ($additional_images as $index => $image_url): ?>
                          <div class="relative group">
                              <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                   alt="<?php echo htmlspecialchars($post['title']); ?> - Image <?php echo $index + 1; ?>" 
                                   class="w-full h-40 object-cover rounded-lg shadow-md transition-all duration-200 hover:shadow-lg border-2 border-gray-100">
                              <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-200 rounded-lg flex items-center justify-center">
                                  <i class="fas fa-image text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200 text-lg"></i>
                              </div>
                              <div class="absolute bottom-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
                                  <?php echo htmlspecialchars(substr($post['title'], 0, 20)); ?><?php echo strlen($post['title']) > 20 ? '...' : ''; ?>
                              </div>
                          </div>
                          <?php endforeach; ?>
                      </div>
                      <?php else: ?>
                      <div class="text-center py-8 text-gray-500">
                          <i class="fas fa-images text-4xl mb-4"></i>
                          <p>No additional images for this post</p>
                      </div>
                      <?php endif; ?>
                  </div>
                 
                                   <!-- Hidden data for JavaScript -->
                  <script>
                      window.imageGalleryData = {
                          images: <?php echo json_encode($additional_images); ?>,
                          title: <?php echo json_encode($post['title']); ?>,
                          totalImages: <?php echo count($additional_images); ?>
                      };
                      
                      // Debug logging
                      console.log('Image gallery data loaded:', window.imageGalleryData);
                      console.log('Additional images count:', <?php echo count($additional_images); ?>);
                      console.log('Additional images:', <?php echo json_encode($additional_images); ?>);
                      
                      // Enhanced Image Gallery with Navigation Controls - Define functions immediately
                      let currentImageIndex = 0;
                      let imageGalleryData = null;

                      function openImageGallery(startIndex = 0) {
                          console.log('openImageGallery called with startIndex:', startIndex);
                          console.log('window.imageGalleryData:', window.imageGalleryData);
                          
                          if (!window.imageGalleryData) {
                              console.error('Image gallery data not found');
                              return;
                          }

                          imageGalleryData = window.imageGalleryData;
                          currentImageIndex = startIndex;
                          console.log('Gallery opened with image index:', currentImageIndex);
                          
                          // Remove any existing modal
                          const existingModal = document.querySelector('.image-gallery-modal');
                          if (existingModal) {
                              existingModal.remove();
                          }
                          
                          // Create modal HTML with navigation controls
                          const modalHTML = `
                              <div class="image-gallery-modal fixed inset-0 bg-black bg-opacity-95 flex items-center justify-center z-50" onclick="closeImageGallery()">
                                  <div class="relative w-full h-full flex items-center justify-center p-4" onclick="event.stopPropagation()">
                                      <!-- Close button -->
                                      <button onclick="closeImageGallery()" class="absolute top-4 right-4 bg-white text-gray-800 rounded-full w-10 h-10 flex items-center justify-center hover:bg-gray-200 transition z-10 shadow-lg">
                                          <i class="fas fa-times"></i>
                                      </button>
                                      
                                      <!-- Previous button -->
                                      <button onclick="navigateImage(-1)" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white text-gray-800 rounded-full w-12 h-12 flex items-center justify-center hover:bg-gray-200 transition z-10 shadow-lg ${currentImageIndex === 0 ? 'opacity-50 cursor-not-allowed' : ''}">
                                          <i class="fas fa-chevron-left"></i>
                                      </button>
                                      
                                      <!-- Next button -->
                                      <button onclick="navigateImage(1)" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white text-gray-800 rounded-full w-12 h-12 flex items-center justify-center hover:bg-gray-200 transition z-10 shadow-lg ${currentImageIndex === imageGalleryData.totalImages - 1 ? 'opacity-50 cursor-not-allowed' : ''}">
                                          <i class="fas fa-chevron-right"></i>
                                      </button>
                                      
                                      <!-- Image container -->
                                      <div class="max-w-6xl max-h-full flex flex-col items-center">
                                          <div class="text-white text-center mb-4">
                                              <h3 class="text-xl font-semibold">${imageGalleryData.title}</h3>
                                              <p class="text-sm opacity-75">Image ${currentImageIndex + 1} of ${imageGalleryData.totalImages}</p>
                                          </div>
                                          <img src="${imageGalleryData.images[currentImageIndex]}" 
                                               alt="${imageGalleryData.title} - Image ${currentImageIndex + 1}" 
                                               class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl transition-opacity duration-300">
                                      </div>
                                      
                                      <!-- Thumbnail navigation -->
                                      <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 max-w-full overflow-x-auto px-4">
                                          ${imageGalleryData.images.map((img, index) => `
                                              <img src="${img}" 
                                                   alt="Thumbnail ${index + 1}" 
                                                   class="w-16 h-16 object-cover rounded cursor-pointer border-2 transition-all duration-200 ${index === currentImageIndex ? 'border-white' : 'border-transparent hover:border-gray-300'}"
                                                   onclick="goToImage(${index})">
                                          `).join('')}
                                      </div>
                                  </div>
                              </div>
                          `;
                          
                          // Add modal to page
                          document.body.insertAdjacentHTML('beforeend', modalHTML);
                          
                          // Add keyboard listeners
                          document.addEventListener('keydown', handleGalleryKeydown);
                      }
                      
                      function navigateImage(direction) {
                          if (!imageGalleryData) return;
                          
                          const newIndex = currentImageIndex + direction;
                          if (newIndex >= 0 && newIndex < imageGalleryData.totalImages) {
                              currentImageIndex = newIndex;
                              updateGalleryDisplay();
                          }
                      }
                      
                      function goToImage(index) {
                          if (!imageGalleryData || index < 0 || index >= imageGalleryData.totalImages) return;
                          
                          currentImageIndex = index;
                          updateGalleryDisplay();
                      }
                      
                      function updateGalleryDisplay() {
                          if (!imageGalleryData) return;
                          
                          const modal = document.querySelector('.image-gallery-modal');
                          if (!modal) return;
                          
                          // Update main image
                          const mainImage = modal.querySelector('img[class*="max-w-full"]');
                          if (mainImage) {
                              mainImage.src = imageGalleryData.images[currentImageIndex];
                              mainImage.alt = `${imageGalleryData.title} - Image ${currentImageIndex + 1}`;
                          }
                          
                          // Update counter
                          const counter = modal.querySelector('p[class*="text-sm opacity-75"]');
                          if (counter) {
                              counter.textContent = `Image ${currentImageIndex + 1} of ${imageGalleryData.totalImages}`;
                          }
                          
                          // Update navigation buttons
                          const prevButton = modal.querySelector('button[onclick="navigateImage(-1)"]');
                          const nextButton = modal.querySelector('button[onclick="navigateImage(1)"]');
                          
                          if (prevButton) {
                              prevButton.className = `absolute left-4 top-1/2 transform -translate-y-1/2 bg-white text-gray-800 rounded-full w-12 h-12 flex items-center justify-center hover:bg-gray-200 transition z-10 shadow-lg ${currentImageIndex === 0 ? 'opacity-50 cursor-not-allowed' : ''}`;
                          }
                          
                          if (nextButton) {
                              nextButton.className = `absolute right-4 top-1/2 transform -translate-y-1/2 bg-white text-gray-800 rounded-full w-12 h-12 flex items-center justify-center hover:bg-gray-200 transition z-10 shadow-lg ${currentImageIndex === imageGalleryData.totalImages - 1 ? 'opacity-50 cursor-not-allowed' : ''}`;
                          }
                          
                          // Update thumbnails
                          const thumbnails = modal.querySelectorAll('img[class*="w-16 h-16"]');
                          thumbnails.forEach((thumb, index) => {
                              thumb.className = `w-16 h-16 object-cover rounded cursor-pointer border-2 transition-all duration-200 ${index === currentImageIndex ? 'border-white' : 'border-transparent hover:border-gray-300'}`;
                          });
                      }
                      
                      function handleGalleryKeydown(e) {
                          if (!imageGalleryData) return;
                          
                          switch(e.key) {
                              case 'Escape':
                                  closeImageGallery();
                                  break;
                              case 'ArrowLeft':
                                  if (currentImageIndex > 0) {
                                      navigateImage(-1);
                                  }
                                  break;
                              case 'ArrowRight':
                                  if (currentImageIndex < imageGalleryData.totalImages - 1) {
                                      navigateImage(1);
                                  }
                                  break;
                          }
                      }
                      
                      function closeImageGallery() {
                          const modal = document.querySelector('.image-gallery-modal');
                          if (modal) {
                              modal.remove();
                          }
                          // Remove keyboard listener
                          document.removeEventListener('keydown', handleGalleryKeydown);
                      }
                  </script>
             </div>

            <!-- Article Footer -->
            <div class="border-t border-gray-200 p-8 md:p-12 bg-gray-50">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between space-y-4 md:space-y-0">
                    <div class="flex flex-col md:flex-row items-start md:items-center space-y-2 md:space-y-0 md:space-x-6">
                        <div class="flex items-center space-x-2 text-gray-600">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Published on <?php echo date('F d, Y', strtotime($post['created_at'])); ?></span>
                        </div>
                        <div class="flex items-center space-x-2 text-gray-600">
                            <i class="fas fa-user"></i>
                            <span><?php echo htmlspecialchars($post['author'] ?? $post['first_name'] . ' ' . $post['last_name']); ?></span>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <a href="javascript:void(0);"
                           onclick="shareToFacebook('<?php echo htmlspecialchars($post['title']); ?>', '<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>')"
                           class="text-gray-600 hover:text-seait-orange transition p-2 rounded-full hover:bg-gray-100"
                           title="Share on Facebook">
                            <i class="fab fa-facebook text-lg"></i>
                        </a>
                        <a href="javascript:void(0);"
                           onclick="copyToClipboard('<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>')"
                           class="text-gray-600 hover:text-seait-orange transition p-2 rounded-full hover:bg-gray-100"
                           title="Copy URL">
                            <i class="fas fa-link text-lg"></i>
                        </a>
                        <a href="javascript:void(0);"
                           onclick="showShareOptions('<?php echo htmlspecialchars($post['title']); ?>', '<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>')"
                           class="text-gray-600 hover:text-seait-orange transition p-2 rounded-full hover:bg-gray-100"
                           title="More sharing options">
                            <i class="fas fa-share-alt text-lg"></i>
                        </a>
                        <a href="javascript:void(0);"
                           onclick="debugFacebookShare('<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>')"
                           class="text-gray-600 hover:text-seait-orange transition p-2 rounded-full hover:bg-gray-100"
                           title="Debug Facebook Share Preview">
                            <i class="fas fa-bug text-lg"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode(htmlspecialchars($post['title'])); ?>"
                           target="_blank"
                           class="text-gray-600 hover:text-seait-orange transition p-2 rounded-full hover:bg-gray-100"
                           onclick="window.open(this.href, 'twitter-share', 'width=580,height=296'); return false;">
                            <i class="fab fa-twitter text-lg"></i>
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>"
                           target="_blank"
                           class="text-gray-600 hover:text-seait-orange transition p-2 rounded-full hover:bg-gray-100"
                           onclick="window.open(this.href, 'linkedin-share', 'width=580,height=296'); return false;">
                            <i class="fab fa-linkedin text-lg"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Posts -->
        <div class="mt-12">
            <h2 class="text-2xl md:text-3xl font-bold text-seait-dark mb-6">Related Posts</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php
                $related_query = "SELECT * FROM posts WHERE status = 'approved' AND type = ? AND id != ? ORDER BY created_at DESC LIMIT 4";
                $stmt = mysqli_prepare($conn, $related_query);
                mysqli_stmt_bind_param($stmt, "si", $post['type'], $post_id);
                mysqli_stmt_execute($stmt);
                $related_result = mysqli_stmt_get_result($stmt);

                while($related = mysqli_fetch_assoc($related_result)):
                ?>
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-lg hover:shadow-xl transition">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-2 text-seait-dark">
                            <a href="news-detail.php?id=<?php echo encrypt_id($related['id']); ?>" class="hover:text-seait-orange transition">
                                <?php echo htmlspecialchars($related['title']); ?>
                            </a>
                        </h3>
                        <div class="text-gray-600 mb-4 text-sm">
                            <?php
                            $content = strip_tags($related['content']);
                            echo htmlspecialchars(substr($content, 0, 120)) . '...';
                            ?>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">
                                <?php echo date('M d, Y', strtotime($related['created_at'])); ?>
                            </span>
                            <span class="px-2 py-1 text-xs bg-seait-orange text-white rounded">
                                <?php echo ucfirst($related['type']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Include FAB Inquiry System -->
    <?php include 'includes/fab-inquiry.php'; ?>

    <script>
        // Active navbar link functionality for news detail page
        function updateActiveNavLink() {
            const navLinks = document.querySelectorAll('a[href^="index.php#"]');

            // Remove active class from all links
            navLinks.forEach(link => {
                link.classList.remove('navbar-link-active');
            });

            // Highlight News link for news detail page
            const newsLink = document.querySelector('a[href="index.php#news"]');
            if (newsLink) {
                newsLink.classList.add('navbar-link-active');
            }
        }

        // Update active link on page load
        document.addEventListener('DOMContentLoaded', updateActiveNavLink);

        // Enhanced Facebook sharing function for clear preview images
        function shareToFacebook(title, url) {
            try {
                // Encode the URL properly
                const encodedUrl = encodeURIComponent(url);

                // Create Facebook share URL
                const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`;

                // Log sharing info for debugging
                console.log('Facebook Share Info:', {
                    title: title,
                    url: url,
                    ogImage: '<?php echo htmlspecialchars($og_image_url); ?>',
                    dimensions: '<?php echo $og_image_width; ?>x<?php echo $og_image_height; ?>'
                });

                // Open Facebook share dialog with optimal dimensions
                const width = 626;
                const height = 436;
                const left = (screen.width - width) / 2;
                const top = (screen.height - height) / 2;

                const popup = window.open(
                    facebookUrl,
                    'facebook-share',
                    `width=${width},height=${height},left=${left},top=${top},location=0,menubar=0,toolbar=0,status=0,scrollbars=1,resizable=1`
                );

                // Check if popup was blocked
                if (!popup || popup.closed || typeof popup.closed === 'undefined') {
                    console.warn('Facebook popup blocked, showing alternatives');
                    showShareOptions(title, url);
                } else {
                    popup.focus();
                }
            } catch (error) {
                console.error('Facebook sharing error:', error);
                showShareOptions(title, url);
            }
        }

        // Function to copy URL to clipboard
        function copyToClipboard(url) {
            navigator.clipboard.writeText(url).then(() => {
                alert('URL copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy URL: ', err);
                alert('Failed to copy URL. Please copy it manually: ' + url);
            });
        }

        // Function to show more sharing options (e.g., copy URL, share on Twitter, LinkedIn)
        function showShareOptions(title, url) {
            const options = [
                { name: 'Copy URL', action: () => copyToClipboard(url) },
                { name: 'Share on Twitter', action: () => {
                    window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`, '_blank');
                }},
                { name: 'Share on LinkedIn', action: () => {
                    window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`, '_blank');
                }}
            ];

            const optionsHtml = options.map(option => `
                <button onclick="option.action();" class="w-full text-left px-4 py-2 hover:bg-gray-100">
                    ${option.name}
                </button>
            `).join('');

            const optionsModal = `
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
                        <h3 class="text-lg font-bold mb-4 text-seait-dark">Share This Post</h3>
                        <div class="space-y-2">
                            ${optionsHtml}
                        </div>
                        <button onclick="closeShareOptions()" class="mt-4 w-full text-seait-orange bg-white border border-seait-orange hover:bg-seait-orange hover:text-white transition py-2 px-4 rounded-md">
                            Close
                        </button>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', optionsModal);
            document.querySelector('.fixed.inset-0.bg-black.bg-opacity-50').addEventListener('click', closeShareOptions);
        }

        // Function to close the share options modal
        function closeShareOptions() {
            const modal = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-50');
            if (modal) {
                modal.remove();
            }
        }

        // Debug function for Facebook sharing preview
        function debugFacebookShare(url) {
            const debugUrl = `https://developers.facebook.com/tools/debug/?q=${encodeURIComponent(url)}`;
            
            console.log('Facebook Debug Info:', {
                pageUrl: url,
                debugUrl: debugUrl,
                ogImage: '<?php echo htmlspecialchars($og_image_url); ?>',
                ogTitle: '<?php echo htmlspecialchars($post['title']); ?>',
                dimensions: '<?php echo $og_image_width; ?>x<?php echo $og_image_height; ?>'
            });

            // Show debug modal
            const debugModal = `
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" onclick="closeDebugModal()">
                    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold text-seait-dark">Facebook Share Preview Debug</h3>
                            <button onclick="closeDebugModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-blue-800 mb-2">Current Page Info</h4>
                                <div class="text-sm space-y-2">
                                    <div><strong>Title:</strong> <?php echo htmlspecialchars($post['title']); ?></div>
                                    <div><strong>URL:</strong> <code class="bg-white px-2 py-1 rounded">${url}</code></div>
                                    <div><strong>Image:</strong> <code class="bg-white px-2 py-1 rounded break-all"><?php echo htmlspecialchars($og_image_url); ?></code></div>
                                    <div><strong>Dimensions:</strong> <?php echo $og_image_width; ?> x <?php echo $og_image_height; ?> pixels</div>
                                </div>
                            </div>

                            <div class="bg-green-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-green-800 mb-2">Preview Image</h4>
                                <img src="<?php echo htmlspecialchars($og_image_url); ?>" 
                                     alt="Facebook Preview" 
                                     class="w-full max-w-md mx-auto rounded border shadow-sm"
                                     onerror="this.parentElement.innerHTML='<div class=\\'text-red-600\\'>❌ Image failed to load</div>'">
                            </div>

                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-yellow-800 mb-2">📋 Tips for Clear Facebook Previews</h4>
                                <ul class="text-sm space-y-1 text-yellow-700">
                                    <li>• Images should be at least 1200x630px for best quality</li>
                                    <li>• Use high-resolution, clear images without too much text</li>
                                    <li>• Facebook caches previews - use the debugger to refresh</li>
                                    <li>• JPEG format often works better than PNG for photos</li>
                                </ul>
                            </div>

                            <div class="bg-red-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-red-800 mb-2">🔧 Facebook Debugger Tool</h4>
                                <p class="text-sm text-red-700 mb-3">Use Facebook's official tool to see exactly how your page appears and refresh the cache:</p>
                                <div class="space-y-2">
                                    <button onclick="window.open('${debugUrl}', '_blank')" 
                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded transition">
                                        🔍 Open Facebook Debugger
                                    </button>
                                    <button onclick="window.open('/test-facebook-preview.php', '_blank')" 
                                            class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded transition">
                                        🧪 Open Test Page
                                    </button>
                                </div>
                                <p class="text-xs text-red-600 mt-2">
                                    <strong>Important:</strong> After opening the debugger, click "Scrape Again" to refresh Facebook's cache!
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex space-x-3">
                            <button onclick="shareToFacebook('<?php echo htmlspecialchars($post['title']); ?>', '${url}')" 
                                    class="flex-1 bg-seait-orange hover:bg-orange-600 text-white py-2 px-4 rounded transition">
                                📱 Test Facebook Share
                            </button>
                            <button onclick="closeDebugModal()" 
                                    class="flex-1 border border-gray-300 hover:bg-gray-50 py-2 px-4 rounded transition">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', debugModal);
        }

        // Function to close debug modal
        function closeDebugModal() {
            const modal = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-50');
            if (modal) {
                modal.remove();
            }
        }
          
         
         
     </script>
</body>
</html>
