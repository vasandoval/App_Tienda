<?php
require_once '../conexion.php';

$cedula = $_GET['cedula'] ?? '';
if (empty($cedula)) {
    header("Location: lista_cliente.php?error=Cliente+no+válido");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM clientes WHERE cedula = ?");
$stmt->execute([$cedula]);
$cliente = $stmt->fetch();
if (!$cliente) {
    header("Location: lista_cliente.php?error=Cliente+no+encontrado");
    exit;
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');

    if (empty($nombre))   $errores[] = "El nombre es obligatorio.";
    if (empty($apellido)) $errores[] = "El apellido es obligatorio.";
    if (empty($telefono)) $errores[] = "El teléfono es obligatorio.";
    if (empty($correo))   $errores[] = "El correo es obligatorio.";
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores[] = "El correo no es válido.";

    if (empty($errores)) {
        $stmt = $pdo->prepare("UPDATE clientes SET nombre=?, apellido=?, telefono=?, correo=? WHERE cedula=?");
        $stmt->execute([$nombre, $apellido, $telefono, $correo, $cedula]);
        header("Location: lista_cliente.php?msg=Cliente+actualizado+correctamente");
        exit;
    }
    $cliente = array_merge($cliente, $_POST);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Cliente — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-interior">

<?php include '../includes/navbar.php'; ?>

<div class="app-layout">
  <main class="content">

    <div class="content-header">
      <div>
        <h1 class="content-title">Editar Cliente</h1>
        <p class="content-subtitle">Modificando: <strong><?= htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']) ?></strong></p>
      </div>
      <a href="lista_cliente.php" class="btn btn-contorno">Volver</a>
    </div>

    <?php foreach ($errores as $err): ?>
      <p class="msg-error"><?= htmlspecialchars($err) ?></p>
    <?php endforeach; ?>

    <div class="card">
      <div class="card-header"><h3>Datos del cliente</h3></div>
      <div class="card-body">
        <form method="POST">
          <div class="form-grid">

            <div class="form-group">
              <label>Cédula</label>
              <input type="text" value="<?= htmlspecialchars($cliente['cedula']) ?>" disabled>
              <span class="form-hint">La cédula no se puede modificar</span>
            </div>

            <div class="form-group">
              <label for="nombre">Nombre <span class="requerido">*</span></label>
              <input type="text" id="nombre" name="nombre"
                     value="<?= htmlspecialchars($cliente['nombre']) ?>">
            </div>

            <div class="form-group">
              <label for="apellido">Apellido <span class="requerido">*</span></label>
              <input type="text" id="apellido" name="apellido"
                     value="<?= htmlspecialchars($cliente['apellido']) ?>">
            </div>

            <div class="form-group">
              <label for="telefono">Teléfono <span class="requerido">*</span></label>
              <input type="tel" id="telefono" name="telefono"
                     value="<?= htmlspecialchars($cliente['telefono']) ?>">
            </div>

            <div class="form-group ancho-completo">
              <label for="correo">Correo electrónico <span class="requerido">*</span></label>
              <input type="email" id="correo" name="correo"
                     value="<?= htmlspecialchars($cliente['correo']) ?>">
            </div>

          </div>

          <div class="form-acciones">
            <button type="submit" class="btn btn-advertencia btn-lg">Actualizar Cliente</button>
            <a href="lista_cliente.php" class="btn btn-contorno btn-lg">Cancelar</a>
          </div>
        </form>
      </div>
    </div>

  </main>
</div>

<footer class="footer">
  <p>Tienda App</p>
</footer>
</body>
</html>
