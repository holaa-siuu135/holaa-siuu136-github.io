<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Validar campos obligatorios
if (empty($_POST['nombre_comun']) || empty($_POST['nombre_cientifico'])) {
    echo json_encode(['error' => 'Nombre común y científico son obligatorios']);
    exit;
}

// Procesar la imagen subida
$imagen_path = '';
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $nombre_archivo = time() . '_' . uniqid() . '.' . $extension;
    $ruta_destino = $upload_dir . $nombre_archivo;
    
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
        $imagen_path = 'uploads/' . $nombre_archivo;
    } else {
        echo json_encode(['error' => 'Error al guardar la imagen']);
        exit;
    }
} else {
    // Si no se sube imagen, usar una por defecto
    $imagen_path = 'uploads/default.jpg';
}

// Insertar en la base de datos
$stmt = $conn->prepare("INSERT INTO plantas (nombre_comun, nombre_cientifico, familia, descripcion, usos, imagen_path) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", 
    $_POST['nombre_comun'],
    $_POST['nombre_cientifico'],
    $_POST['familia'],
    $_POST['descripcion'],
    $_POST['usos'],
    $imagen_path
);

if ($stmt->execute()) {
    $id_nuevo = $stmt->insert_id;
    
    // Si se enviaron categorías (IDs)
    if (!empty($_POST['categorias'])) {
        $categorias = explode(',', $_POST['categorias']);
        $stmt_rel = $conn->prepare("INSERT INTO planta_categoria (planta_id, categoria_id) VALUES (?, ?)");
        foreach ($categorias as $cat_id) {
            $stmt_rel->bind_param("ii", $id_nuevo, $cat_id);
            $stmt_rel->execute();
        }
        $stmt_rel->close();
    }
    
    echo json_encode(['success' => true, 'id' => $id_nuevo, 'imagen' => $imagen_path]);
} else {
    echo json_encode(['error' => 'Error al guardar: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>