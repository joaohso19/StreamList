<?php
require_once __DIR__ . '/../includes/functions.php';
$_SESSION = array();
session_destroy();
header("Location: " . BASE . "/login/login.php");
exit();
