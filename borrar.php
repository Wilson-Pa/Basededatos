
<?php
include 'funciones.php';

$config = include 'config.php'; 

$resultado = [
  'error' => false,
  'mensaje' => ''
];

try {
  $dsn = 'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['name'] . ';charset=utf8mb4'; 
  $conexion = new PDO($dsn, $config['db']['user'], $config['db']['pass'], $config['db']['options']);

 
  if (isset($_GET['id'])) {
    $id_vendedor = $_GET['id'];

   
    $consultaSQL = "DELETE FROM vendedores WHERE id = :id";
    $sentencia = $conexion->prepare($consultaSQL);
    $sentencia->bindParam(':id', $id_vendedor, PDO::PARAM_INT); 
    $sentencia->execute();

    // Redirigir al usuario a la página principal después de la eliminación exitosa
    header('Location: index.php?mensaje=Vendedor eliminado exitosamente.'); 
    exit;
  } else {
    // Si no se proporciona un ID, se marca un error
    $resultado['error'] = true;
    $resultado['mensaje'] = 'ID de vendedor no proporcionado para la eliminación.';
  }
} catch (PDOException $error) {
  // Capturar y mostrar cualquier error de la base de datos
  $resultado['error'] = true;
  $resultado['mensaje'] = 'Error al eliminar el vendedor: ' . htmlspecialchars($error->getMessage());
}
?>

<?php require "templates/header.php"; ?>

<?php if ($resultado['error']): ?>
  <div class="container mt-2">
    <div class="row">
      <div class="col-md-12">
        <div class="alert alert-danger" role="alert">
          <?= $resultado['mensaje'] ?>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php require "templates/footer.php"; ?>
