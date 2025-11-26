<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: index.php');
    exit;
}

function isAdmin() {
    return isset($_SESSION['rol']) && ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'operador');
}

function isInvitado() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'invitado';
}

function isEmpleado() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'empleado';
}

function isOperador() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'operador';
}
?>