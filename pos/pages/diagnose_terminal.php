<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Starting diagnosis...<br>";

try {
    include 'terminal.php';
} catch (Throwable $e) {
    echo "Caught Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
}

echo "<br>Diagnosis complete.";
?>
