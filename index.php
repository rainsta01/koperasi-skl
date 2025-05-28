<?php
session_start();
$timeout = 1800;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: login");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
} else {
    $redirect_url = ($_SESSION['role'] == 'admin') ? 'admin' : 'user';
    header("Location: $redirect_url");
}
exit();
?>
