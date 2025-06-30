@@ -1,54 +1,102 @@
<?php

include 'funciones.php';
include 'funciones.php'; // 

csrf();
if (isset($_POST['submit']) && !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
  die();
  die("Error de seguridad CSRF."); // Mensaje de error más informativo
}

if (isset($_POST['submit'])) {
  $resultado = [
    'error' => false,
    'mensaje' => 'El alumno ' . escapar($_POST['nombre']) . ' ha sido agregado con éxito'
  ];
$config = include 'config.php'; 

  $config = include 'config.php';
$resultado = [
  'error' => false,
  'mensaje' => ''
];

  try {
    $dsn = 'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['name'];
    $conexion = new PDO($dsn, $config['db']['user'], $config['db']['pass'], $config['db']['options']);
// Comprobar si se proporcionó un ID vía GET
if (!isset($_GET['id'])) {
  $resultado['error'] = true;
  $resultado['mensaje'] = 'ID de vendedor no especificado.';
}

    $alumno = [
      "nombre"   => $_POST['nombre'],
      "apellido" => $_POST['apellido'],
      "email"    => $_POST['email'],
      "edad"     => $_POST['edad'],
    ];
try {
  $dsn = 'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['name'] . ';charset=utf8mb4'; 
  $conexion = new PDO($dsn, $config['db']['user'], $config['db']['pass'], $config['db']['options']);

    // Insert record
    $consultaSQL = "INSERT INTO alumnos (nombre, apellido, email, edad)";
    $consultaSQL .= "values (:" . implode(", :", array_keys($alumno)) . ")";
  
  if (isset($_POST['submit'])) {
    $id_actualizar = $_GET['id']; 
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');

    //echo $consultaSQL; die();
    // Validaciones básicas antes de actualizar
    if (empty($nombre) || empty($apellido) || empty($email)) {
        $resultado['error'] = true;
        $resultado['mensaje'] = 'Por favor, complete todos los campos requeridos (Nombre, Apellido, Email).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $resultado['error'] = true;
        $resultado['mensaje'] = 'El formato del email es inválido.';
    } else {
        $vendedor_a_actualizar = [
            "id"        => $id_actualizar,
            "nombre"    => $nombre,
            "apellido"  => $apellido,
            "email"     => $email
        ];
        
        // Actualizar registro en la tabla 'vendedores'
        $consultaSQL = "UPDATE vendedores SET
            nombre = :nombre,
            apellido = :apellido,
            email = :email
            WHERE id = :id";
        
        $sentencia = $conexion->prepare($consultaSQL);
        $sentencia->execute($vendedor_a_actualizar);

        // Actualizar el mensaje de éxito
        $resultado['error'] = false;
        $resultado['mensaje'] = 'El vendedor ' . escapar($nombre) . ' ' . escapar($apellido) . ' ha sido actualizado correctamente.';
    }
  }

  // Siempre obtener los datos del vendedor para rellenar el formulario, incluso después de una actualización
  if (!$resultado['error'] && isset($_GET['id'])) {
    $id = $_GET['id'];
    // Usando sentencias preparadas para la obtención para prevenir inyección SQL
    $consultaSQL = "SELECT * FROM vendedores WHERE id = :id";
    $sentencia = $conexion->prepare($consultaSQL);
    $sentencia->execute($alumno);
    $sentencia->bindValue(':id', $id, PDO::PARAM_INT);
    $sentencia->execute();
    
    $vendedor = $sentencia->fetch(PDO::FETCH_ASSOC);

  } catch(PDOException $error) {
    $resultado['error'] = true;
    $resultado['mensaje'] = $error->getMessage();
    if (!$vendedor) {
      $resultado['error'] = true;
      $resultado['mensaje'] = 'No se ha encontrado el vendedor.';
    }
  }

} catch(PDOException $error) {
  $resultado['error'] = true;
  // Manejo específico de errores para email duplicado (si el email es UNIQUE en la BD)
  if ($error->getCode() == '23000') {
      $resultado['mensaje'] = 'Error: El email "' . htmlspecialchars($email ?? '') . '" ya está registrado para otro vendedor.';
  } else {
      $resultado['mensaje'] = 'Error al procesar la solicitud: ' . htmlspecialchars($error->getMessage());
  }
}
?>

<?php include 'templates/header.php'; ?>
<?php require "templates/header.php"; ?>

<?php
if (isset($resultado)) {
// Mostrar mensajes de resultado (error o éxito)
if ($resultado['mensaje'] !== '') { // Mostrar mensaje si no está vacío
  ?>
  <div class="container mt-3">
  <div class="container mt-2">
    <div class="row">
      <div class="col-md-12">
        <div class="alert alert-<?= $resultado['error'] ? 'danger' : 'success' ?>" role="alert">
@@ -61,36 +109,40 @@
}
?>

<div class="container">
  <div class="row">
    <div class="col-md-12">
      <h2 class="mt-4">Agregar nuevo alumno</h2>
      <hr>
      <form method="post">
        <div class="form-group">
          <label for="nombre">Nombre</label>
          <input type="text" name="nombre" id="nombre" class="form-control">
        </div>
        <div class="form-group">
          <label for="apellido">Apellido</label>
          <input type="text" name="apellido" id="apellido" class="form-control">
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" name="email" id="email" class="form-control">
        </div>
        <div class="form-group">
          <label for="edad">Edad</label>
          <input type="text" name="edad" id="edad" class="form-control">
        </div>
        <div class="form-group">
          <input name="csrf" type="hidden" value="<?php echo escapar($_SESSION['csrf']); ?>">
          <input type="submit" name="submit" class="btn btn-primary" value="Guardar">
          <a class="btn btn-secondary" href="index.php">Lista de alumnos</a>
        </div>
      </form>
<?php
// Mostrar el formulario solo si se encontró un vendedor y no hay un error importante
if (isset($vendedor) && $vendedor && !$resultado['error']) {
  ?>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <h2 class="mt-4">Editando el vendedor <?= escapar($vendedor['nombre']) . ' ' . escapar($vendedor['apellido']) ?></h2>
        <hr>
        <form method="post">
          <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" name="nombre" id="nombre" value="<?= escapar($vendedor['nombre']) ?>" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="apellido">Apellido</label>
            <input type="text" name="apellido" id="apellido" value="<?= escapar($vendedor['apellido']) ?>" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?= escapar($vendedor['email']) ?>" class="form-control" required>
          </div>
          <div class="form-group mt-3">
            <input name="csrf" type="hidden" value="<?php echo escapar($_SESSION['csrf']); ?>">
            <input type="hidden" name="id" value="<?= escapar($vendedor['id']) ?>">
            <input type="submit" name="submit" class="btn btn-primary" value="Actualizar Vendedor">
            <a class="btn btn-secondary" href="index.php">Regresar a la lista de vendedores</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
  <?phpAdd commentMore actions
}
?>

<?php include 'templates/footer.php'; ?>
<?php require "templates/footer.php"; ?>
