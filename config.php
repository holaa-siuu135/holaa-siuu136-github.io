<?php
// config.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';
$user = 'root';        // tu usuario de MySQL
$password = '';        // tu contraseña (en XAMPP suele estar vacía)
$database = 'herbario_db';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]));
}

// Establecer charset para evitar problemas con tildes
$conn->set_charset("utf8");
?>