<?php
$filePath = dirname(__FILE__) . '/login.html';

if (file_exists($filePath)) {
    readfile($filePath);
} else {
    echo "The file 'login.html' could not be found.";
}
?>
