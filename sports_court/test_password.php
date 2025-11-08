<?php
$hash = '$2y$10$Uu.bT/6hRTyQ/aiFZScA9uKZ3HbdF.5b3dL5W9zYpuG1aCzk7vLEK';
$password = 'admin123';

if (password_verify($password, $hash)) {
    echo "<h2 style='color:green;'>✅ Password is valid!</h2>";
} else {
    echo "<h2 style='color:red;'>❌ Password test failed!</h2>";
}
?>
