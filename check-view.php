<?php
// Check what's being served - correct path
$file = __DIR__ . '/../relearn/resources/views/products/index.blade.php';
$file2 = '/domains/relearn.co.ke/relearn/resources/views/products/index.blade.php';

echo "<h1>Products View File Diagnostic</h1>";
echo "<p><strong>Checking paths:</strong></p>";

foreach ([$file, $file2] as $testPath) {
    echo "<p>Path: $testPath";
    if (file_exists($testPath)) {
        $size = filesize($testPath);
        $content = file_get_contents($testPath);
        
        echo " - <strong style='color: green;'>FOUND</strong> ($size bytes)</p>";
        echo "<hr>";
        
        if (strpos($content, 'grid-template-columns: repeat(4') !== false) {
            echo "<p style='color: green;'><strong>✓ 4-column grid CSS found in file</strong></p>";
        } else {
            echo "<p style='color: red;'><strong>✗ 4-column grid CSS NOT found in file</strong></p>";
        }
        
        if (strpos($content, 'products-grid') !== false) {
            echo "<p style='color: green;'><strong>✓ products-grid class found</strong></p>";
        } else {
            echo "<p style='color: red;'><strong>✗ products-grid class NOT found</strong></p>";
        }
        
        echo "<hr>";
        echo "<h3>First 1500 characters of file:</h3>";
        echo "<pre>" . htmlspecialchars(substr($content, 0, 1500)) . "</pre>";
        break;
    } else {
        echo " - NOT found</p>";
    }
}
?>

