<?php

include 'funciones.php'; 

csrf();

$config = include 'config.php'; 
$resultado = [
  'error' => false,
  'mensaje' => ''
];
$vendedor = null; 

try {
  $dsn = 'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['name'] . ';charset=utf8mb4';
  $conexion = new PDO($dsn, $config['db']['user'], $config['db']['pass'], $config['db']['options']);

  /
  if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $consultaSQL = "SELECT * FROM vendedores WHERE id = :id";
    $sentencia = $conexion->prepare($consultaSQL);
    $sentencia->bindValue(':id', $id);
    $sentencia->execute();
    $vendedor = $sentencia->fetch(PDO::FETCH_ASSOC);

    if (!$vendedor) {
      $resultado['error'] = true;
      $resultado['mensaje'] = 'Vendedor no encontrado.';
    }
  } else {
    $resultado['error'] = true;
    $resultado['mensaje'] = 'ID de vendedor no especificado.';
  }

  
  if (isset($_POST['submit']) && !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
    die("Error de seguridad CSRF.");
  }

  if (isset($_POST['submit'])) {
    $id_actualizar = $_POST['id']; 
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Validaciones básicas antes de actualizar
    if (empty($nombre) || empty($apellido) || empty($email)) {
        $resultado['error'] = true;
        $resultado['mensaje'] = 'Por favor, complete todos los campos requeridos (Nombre, Apellido, Email).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $resultado['error'] = true;
        $resultado['mensaje'] = 'El formato del email es inválido.';
    } else {
        $vendedor_actualizado = [
            "id"        => $id_actualizar,
            "nombre"    => $nombre,
            "apellido"  => $apellido,
            "email"     => $email
        ];

        $consultaSQL = "UPDATE vendedores SET
            nombre = :nombre,
            apellido = :apellido,
            email = :email
            WHERE id = :id";

        $sentencia = $conexion->prepare($consultaSQL);
        $sentencia->execute($vendedor_actualizado);

        // Actualizar la variable $vendedor para reflejar los cambios en el formulario
        $vendedor = $vendedor_actualizado; 
        
        $consultaSQL_recargar = "SELECT * FROM vendedores WHERE id = :id";
        $sentencia_recargar = $conexion->prepare($consultaSQL_recargar);
        $sentencia_recargar->bindValue(':id', $id_actualizar);
        $sentencia_recargar->execute();
        $vendedor = $sentencia_recargar->fetch(PDO::FETCH_ASSOC);


        $resultado['error'] = false;
        $resultado['mensaje'] = 'El vendedor ' . escapar($nombre) . ' ' . escapar($apellido) . ' ha sido actualizado con éxito.';
    }
  }

} catch(PDOException $error) {
  $resultado['error'] = true;
  // Manejo específico para el error de email duplicado (si el email es UNIQUE en la BD)
  if ($error->getCode() == '23000') {
    $resultado['mensaje'] = 'Error: El email "' . htmlspecialchars($email) . '" ya está registrado para otro vendedor.';
  } else {
    $resultado['mensaje'] = 'Error al actualizar el vendedor: ' . htmlspecialchars($error->getMessage());
  }
}
?>

<?php include 'templates/header.php'; ?>

<?php
if (isset($resultado) && ($resultado['mensaje'] !== '' || $resultado['error'])) {
  ?>
  <div class="container mt-3">
    <div class="row">
      <div class="col-md-12">
        <div class="alert alert-<?= $resultado['error'] ? 'danger' : 'success' ?>" role="alert">
          <?= $resultado['mensaje'] ?>
        </div>
      </div>
    </div>
  </div>
  <?php
}
?>

<div class="container">
  <div class="row">
    <div class="col-md-12">
      <h2 class="mt-4">Editar Vendedor</h2>
      <hr>
      <?php if ($vendedor): ?>
      <form method="post">
        <div class="form-group">
          <label for="nombre">Nombre</label>
          <input type="text" name="nombre" id="nombre" value="<?php echo escapar($vendedor['nombre']); ?>" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="apellido">Apellido</label>
          <input type="text" name="apellido" id="apellido" value="<?php echo escapar($vendedor['apellido']); ?>" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" name="email" id="email" value="<?php echo escapar($vendedor['email']); ?>" class="form-control" required>
        </div>
        <div class="form-group mt-3">
          <input name="csrf" type="hidden" value="<?php echo escapar($_SESSION['csrf']); ?>">
          <input type="hidden" name="id" value="<?php echo escapar($vendedor['id']); ?>">
          <input type="submit" name="submit" class="btn btn-primary" value="Actualizar Vendedor">
          <a class="btn btn-secondary" href="index.php">Volver a la lista de vendedores</a>
        </div>
      </form>
      <?php else: ?>
        <p>No se pudo cargar la información del vendedor para editar.</p>
        <a class="btn btn-secondary" href="index.php">Volver a la lista de vendedores</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'templates/footer.php'; ?>
