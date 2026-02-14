<?php
session_start();
require_once __DIR__ . '/../../config/connectOperatore.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: frontend_login_operatori.php');
    exit();
}

if ($_SESSION['ruolo'] !== 'operator' && $_SESSION['ruolo'] !== 'admin') {
    header('Location: frontend_login_operatori.php');
    exit();
}

$user_role = $_SESSION['ruolo'];
$page_title = $user_role === 'admin' ? 'Pannello Amministratore' : 'Pannello Operatore';
