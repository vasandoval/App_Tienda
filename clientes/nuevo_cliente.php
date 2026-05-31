<?php
require_once '../conexion.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula   = trim($_POST['cedula'] ?? '');
    $nombre   = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');

    if (empty($cedula))               $errores[] = "La cédula es obligatoria.";
    if (!ctype_digit($cedula))        $errores[] = "La cédula debe contener solo números.";
    if (empty($nombre))               $errores[] = "El nombre es obligatorio.";
    if (empty($apellido))             $errores[] = "El apellido es obligatorio.";
    if (empty($telefono))             $errores[] = "El teléfono es obligatorio.";
    if (empty($correo))               $errores[] = "El correo es obligatorio.";
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores[] = "El correo no es válido.";

    if (empty($errores)) {
        $check = $pdo->prepare("SELECT cedula FROM clientes WHERE cedula = ?");
        $check->execute([$cedula]);
        if ($check->fetch()) $errores[] = "Ya existe un cliente con esa cédula.";
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare("INSERT INTO clientes (cedula, nombre, apellido, telefono, correo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$cedula, $nombre, $apellido, $telefono, $correo]);
        header("Location: lista_cliente.php?msg=Cliente+registrado+correctamente");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nuevo Cliente — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-interior">

<?php include '../includes/navbar.php'; ?>

<div class="app-layout">
  <main class="content">

    <div class="content-header">
      <div>
        <h1 class="content-title">Nuevo Cliente</h1>
        <p class="content-subtitle">Registrar un nuevo cliente en el sistema</p>
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
              <label for="cedula">Cédula <span class="requerido">*</span></label>
              <input type="text" id="cedula" name="cedula"
                     value="<?= htmlspecialchars($_POST['cedula'] ?? '') ?>"
                     placeholder="Ej: 1098765432">
            </div>

            <div class="form-group">
              <label for="nombre">Nombre <span class="requerido">*</span></label>
              <input type="text" id="nombre" name="nombre"
                     value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                     placeholder="Ej: María">
            </div>

            <div class="form-group">
              <label for="apellido">Apellido <span class="requerido">*</span></label>
              <input type="text" id="apellido" name="apellido"
                     value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>"
                     placeholder="Ej: García">
            </div>

            <div class="form-group">
              <label for="telefono">Teléfono <span class="requerido">*</span></label>
              <input type="tel" id="telefono" name="telefono"
                     value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
                     placeholder="Ej: 3109876543">
            </div>

            <div class="form-group ancho-completo">
              <label for="correo">Correo electrónico <span class="requerido">*</span></label>
              <input type="email" id="correo" name="correo"
                     value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                     placeholder="Ej: maria.garcia@gmail.com">
            </div>

          </div>

          <div class="form-acciones">
            <button type="submit" class="btn btn-lg">Guardar Cliente</button>
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
