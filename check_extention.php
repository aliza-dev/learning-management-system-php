<?php
echo "<h2>Checking PHP Extensions</h2>";

if (extension_loaded('mysqli')) {
    echo "✅ mysqli extension is LOADED<br>";
} else {
    echo "❌ mysqli extension is NOT loaded<br>";
}

if (extension_loaded('pdo_mysql')) {
    echo "✅ pdo_mysql extension is LOADED<br>";
} else {
    echo "❌ pdo_mysql extension is NOT loaded<br>";
}

echo "<br><h3>All loaded extensions:</h3>";
print_r(get_loaded_extensions());

echo "<br><br><h3>PHP Version:</h3>";
echo phpversion();

echo "<br><br><h3>Loaded Configuration File:</h3>";
echo php_ini_loaded_file();
?>