<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    $theme = $_POST['theme'];
    $_SESSION['theme'] = ($theme === 'dark') ? 'dark' : 'light';
    echo 'Theme updated';
    exit;
}
?>