<?php

require 'config.php'; 
require 'funciones.php'; // Incluimos funciones como escapar()

$mensaje = '';

if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
}

try {
    $dsn = 'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['name'] . ';charset=utf8mb4';
    $conexion = new PDO($dsn, $config['db']['user'], $config['db']['pass'], $config['db']['options']);

    
    $consultaSQL = "SELECT id, nombre, apellido, email, fecha_registro FROM vendedores ORDER BY id DESC";
    $sentencia = $conexion->prepare($consultaSQL);
    $sentencia->execute();

    $vendedores = $sentencia->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $error) {
    $mensaje = "Error al listar los vendedores: " . htmlspecialchars($error->getMessage());
}
?>

<?php require "templates/header.php"; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">Gestión de Vendedores</h2>
            <hr>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($mensaje) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <p>
                <a href="create.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Agregar Nuevo Vendedor
                </a>
            </p>

            <?php if (isset($vendedores) && count($vendedores) > 0): ?>
                <table class="table table-striped table-hover mt-3">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Email</th>
                            <th>Fecha de Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendedores as $vendedor): ?>
                            <tr>
                                <td><?= escapar($vendedor['id']) ?></td>
                                <td><?= escapar($vendedor['nombre']) ?></td>
                                <td><?= escapar($vendedor['apellido']) ?></td>
                                <td><?= escapar($vendedor['email']) ?></td>
                                <td><?= escapar($vendedor['fecha_registro']) ?></td>
                                <td>
                                    <a href="edit.php?id=<?= escapar($vendedor['id']) ?>" class="btn btn-warning btn-sm me-2">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="delete.php?id=<?= escapar($vendedor['id']) ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('¿Estás seguro de que quieres eliminar a este vendedor? Esta acción no se puede deshacer.');">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-warning mt-3" role="alert">
                    No se encontraron vendedores.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require "templates/footer.php"; ?>
