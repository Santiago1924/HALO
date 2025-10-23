<?php
require_once("../../database/conexion.php");

$db = new Database();
$con = $db->conectar();

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $game_mode = $_POST['game_mode'];
    $release_date = $_POST['release_date'];

    // Subida de imagen
    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $nombreImagen = time() . "_" . basename($_FILES["image"]["name"]);
        $rutaDestino = "../../uploads/" . $nombreImagen;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $rutaDestino)) {
            $image = $nombreImagen;
        }
    }

    // Insertar en BD
    $sql = "INSERT INTO worlds (name, description, image, game_mode, release_date)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $resultado = $stmt->execute([$name, $description, $image, $game_mode, $release_date]);

    $mensaje = $resultado ? "✅ Mapa agregado correctamente" : "❌ Error al agregar el mapa";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Mapa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">
    <div class="container py-5">

        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="bi bi-map"></i> Agregar Nuevo Mapa</h3>
            <a href="dashboard.php" class="btn btn-outline-light">
                <i class="bi bi-arrow-left"></i> Volver al Panel
            </a>
        </div>

        <!-- Tarjeta principal -->
        <div class="card bg-secondary text-white border-0 shadow">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Nombre del Mapa</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Modo de Juego</label>
                            <select name="game_mode" class="form-select" required>
                                <option value="">-- Selecciona --</option>
                                <option value="Arena">Arena</option>
                                <option value="Big Team Battle">Big Team Battle</option>
                                <option value="Campaña">Campaña</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fecha de Lanzamiento</label>
                            <input type="date" name="release_date" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Imagen del Mapa</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>

                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-warning fw-bold">
                                <i class="bi bi-save"></i> Guardar Mapa
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Mensaje -->
        <?php if ($mensaje): ?>
            <div class="alert <?= str_contains($mensaje, '✅') ? 'alert-success' : 'alert-danger' ?> text-center mt-4">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
