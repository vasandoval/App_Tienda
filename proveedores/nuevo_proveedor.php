<?php
require_once '../conexion.php';

$errores  = [];
$productos = $pdo->query("SELECT p.id_producto, p.nombre, c.nombre AS categoria
                           FROM productos p
                           JOIN categorias c ON c.id_categoria = p.id_categoria
                           ORDER BY c.nombre, p.nombre")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $ciudad   = trim($_POST['ciudad'] ?? '');
    $prods    = $_POST['productos'] ?? [];

    if (empty($nombre))   $errores[] = "El nombre del proveedor es obligatorio.";
    if (empty($telefono)) $errores[] = "El teléfono es obligatorio.";
    if (empty($ciudad))   $errores[] = "La ciudad es obligatoria.";

    if (empty($errores)) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO proveedores (nombre, telefono, ciudad) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $telefono, $ciudad]);
            $id_prov = $pdo->lastInsertId();

            if (!empty($prods)) {
                $ins = $pdo->prepare("INSERT INTO proveedor_producto (id_proveedor, id_producto) VALUES (?, ?)");
                foreach ($prods as $id_prod) $ins->execute([$id_prov, intval($id_prod)]);
            }

            $pdo->commit();
            header("Location: lista_proveedor.php?msg=Proveedor+registrado+correctamente");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errores[] = "Error al guardar: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nuevo Proveedor — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-interior">

<?php include '../includes/navbar.php'; ?>

<div class="app-layout">
  <main class="content">

    <div class="content-header">
      <div>
        <h1 class="content-title">Nuevo Proveedor</h1>
        <p class="content-subtitle">Registrar un nuevo proveedor de productos</p>
      </div>
      <a href="lista_proveedor.php" class="btn btn-contorno">Volver</a>
    </div>

    <?php foreach ($errores as $err): ?>
      <p class="msg-error"><?= htmlspecialchars($err) ?></p>
    <?php endforeach; ?>

    <div class="card">
      <div class="card-header"><h3>Datos del proveedor</h3></div>
      <div class="card-body">
        <form method="POST">
          <div class="form-grid">

            <div class="form-group">
              <label for="nombre">Nombre del proveedor <span class="requerido">*</span></label>
              <input type="text" id="nombre" name="nombre"
                     value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                     placeholder="Ej: Distribuciones La 14">
            </div>

            <div class="form-group">
              <label for="telefono">Teléfono <span class="requerido">*</span></label>
              <input type="tel" id="telefono" name="telefono"
                     value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
                     placeholder="Ej: 3001234567">
            </div>

            <div class="form-group">
              <label for="ciudad">Ciudad de procedencia <span class="requerido">*</span></label>
              <input type="text" id="ciudad" name="ciudad"
                     value="<?= htmlspecialchars($_POST['ciudad'] ?? '') ?>"
                     placeholder="Ej: Bogotá">
            </div>

          </div>

          <div class="form-group mt-20">
            <label>Productos que suministra</label>
            <span class="form-hint">Selecciona los productos que este proveedor puede abastecer</span>
            <div class="productos-grid">
              <?php
              $selected_prods = $_POST['productos'] ?? [];
              foreach ($productos as $prod):
              ?>
              <label class="producto-check">
                <input type="checkbox" name="productos[]"
                       value="<?= $prod['id_producto'] ?>"
                       <?= in_array($prod['id_producto'], $selected_prods) ? 'checked' : '' ?>>
                <div>
                  <div class="prod-nombre"><?= htmlspecialchars($prod['nombre']) ?></div>
                  <div class="prod-cat"><?= htmlspecialchars($prod['categoria']) ?></div>
                </div>
              </label>
              <?php endforeach; ?>
              <?php if (empty($productos)): ?>
                <p class="form-hint">No hay productos registrados aún.</p>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-acciones">
            <button type="submit" class="btn btn-lg">Guardar Proveedor</button>
            <a href="lista_proveedor.php" class="btn btn-contorno btn-lg">Cancelar</a>
          </div>
        </form>
      </div>
    </div>

  </main>
</div>

<footer class="footer">
  <p>Tienda App </p>
</footer>
</body>
</html>
