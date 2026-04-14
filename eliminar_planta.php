<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener ID desde la URL (ej: eliminar_planta.php?id=5)
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

// Obtener la ruta de la imagen para borrar el archivo
$stmt_img = $conn->prepare("SELECT imagen_path FROM plantas WHERE id = ?");
$stmt_img->bind_param("i", $id);
$stmt_img->execute();
$result = $stmt_img->get_result();
if ($row = $result->fetch_assoc()) {
    $imagen_path = '../' . $row['imagen_path'];
    if (file_exists($imagen_path) && is_file($imagen_path)) {
        unlink($imagen_path); // eliminar archivo físico
    }
}
$stmt_img->close();

// Eliminar registros relacionados (categorías) y la planta
$conn->begin_transaction();
try {
    $conn->query("DELETE FROM planta_categoria WHERE planta_id = $id");
    $stmt_del = $conn->prepare("DELETE FROM plantas WHERE id = ?");
    $stmt_del->bind_param("i", $id);
    $stmt_del->execute();
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => 'Error al eliminar']);
}
$conn->close();
?>