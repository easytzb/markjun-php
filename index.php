<?php
$d = dirname(__FILE__);
include $d . '/conf/conf.php';
include $d . '/class/autoloader.php';
new Autoloader();

if (isset($_GET['url'])) {
    $price = new Price();
    $price->response();
}

if (isset($_GET['t'])) {
    $stat = new Stat();
    $stat->response();
}

$isAdmin = Common::isAdmin();

if (isset($_GET['a'])) {
    if (!$isAdmin) die;
    $stat = new Admin();
    $stat->response();
}

if ($isAdmin) {
    define('isAdmin', true);
    include $d . '/admin.php';
}
