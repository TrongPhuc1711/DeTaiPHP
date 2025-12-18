<?php
require_once 'config.php';

// --- Recursive File Scanning Function ---
function scanDirRecursive($dir, $basePath = '') {
    $results = [];
    if (!is_dir($dir)) return $results;

    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;

        $fullPath = $dir . '/' . $item;
        // Relative path for the link
        $relativePath = $basePath ? $basePath . '/' . $item : $item;

        if (is_dir($fullPath)) {
            // Recursive call for subdirectories
            $results = array_merge($results, scanDirRecursive($fullPath, $relativePath));
        } else {
            // Check for PHP or TXT files
            $ext = pathinfo($item, PATHINFO_EXTENSION);
            if ($ext == 'php' || $ext == 'txt') {
                $fileName = pathinfo($item, PATHINFO_FILENAME);
                
                // Beautify the name
                $displayName = ucfirst(str_replace('_', ' ', $fileName));
                
                // Add parent folder name if in subdirectory
                if (strpos($basePath, '/') !== false) {
                     $parentFolder = basename(dirname($fullPath));
                     if (!preg_match('/tuan\d+/i', $parentFolder)) {
                         $displayName = ucfirst($parentFolder) . " - " . $displayName;
                     }
                }

                $results[] = [
                    'file' => $item,
                    'name' => $displayName,
                    'path' => "labthuchanh/" . $relativePath
                ];
            }
        }
    }
    return $results;
}

// --- Function to get Labs organized by week ---
function getLabFiles() {
    $labPath = __DIR__ . '/labthuchanh/';
    $labs = [];
    
    if (!is_dir($labPath)) return $labs;
    
    $weeks = scandir($labPath);
    foreach ($weeks as $week) {
        if ($week == '.' || $week == '..') continue;
        
        $weekPath = $labPath . $week;
        if (is_dir($weekPath)) {
            // Extract week number
            preg_match('/\d+/', $week, $matches);
            $weekNumber = isset($matches[0]) ? (int)$matches[0] : 999;
            
            // Get files recursively
            $files = scanDirRecursive($weekPath, $week);
            if (!empty($files)) {
                $labs[$weekNumber] = $files;
            }
        }
    }
    
    ksort($labs);
    return $labs;
}

$labFiles = getLabFiles();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách bài Lab thực hành</title>
    <link rel="stylesheet" href="./css/index.css">
    <style>
        
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="top-bar-content">
            <div><?php echo isLoggedIn() ? 'Xin chào, <b>'.$_SESSION['ho_ten'].'</b>' : ''; ?></div>
        </div>
    </div>

    <div class="header">
        <div class="header-content">
            <a href="index.php" class="logo"><h1>🥤 DrinkShop</h1></a>
            <div class="header-actions">
                <a href="index.php" class="btn-outline">🏠 Trang chủ</a>
            </div>
        </div>
    </div>

    <div class="lab-container">
        <div class="lab-header">
            <h1>📚 Kho Bài Tập Thực Hành</h1>
            <p>Tổng hợp các bài lab theo tuần</p>
        </div>

        <?php if (!empty($labFiles)): ?>
            <?php foreach ($labFiles as $weekNumber => $files): ?>
                <div class="lab-week-section">
                    <div class="lab-week-title">📅 Tuần <?php echo $weekNumber; ?></div>
                    <ul class="lab-list">
                        <?php foreach ($files as $file): ?>
                            <li>
                                <a href="<?php echo $file['path']; ?>" class="lab-link" target="_blank">
                                    <span class="lab-icon">📝</span>
                                    <?php echo $file['name']; ?>
                                    <span style="margin-left: auto; font-size: 0.8em; color: #999;">➜</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 50px; color: #999;">
                <p>Chưa có bài Lab nào.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>&copy; 2025 DrinkShop Lab Repository</p>
    </div>
</body>
</html>