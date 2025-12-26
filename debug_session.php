<?php
session_start();

echo "<h1>Debug de Sesión</h1>";

echo "<h2>Estado de Sesión:</h2>";
echo "Session Status: " . session_status() . "<br>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Name: " . session_name() . "<br>";

echo "<h2>Variables de Sesión:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Cookies:</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

require_once __DIR__ . '/includes/functions.php';

echo "<h2>Is Logged In:</h2>";
echo isLoggedIn() ? "✅ SÍ - Usuario logueado" : "❌ NO - Usuario NO logueado";

echo "<h2>Probar Controlador:</h2>";
echo "<a href='controllers/reportes.php?action=equiposPorEstado' target='_blank'>Test: equiposPorEstado</a><br>";
?>
