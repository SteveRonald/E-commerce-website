<?php
session_start();
session_destroy();
header("Location: shop_main.php?msg=You have been logged out successfully.");
exit();
?>