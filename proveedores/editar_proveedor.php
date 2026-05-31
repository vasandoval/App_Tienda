<?php
require_once '../conexion.php';

$id = intval($_GET['id'] ?? 0);
if ($id === 0) { header("Location: lista_proveedor.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM proveedores WHERE id_proveedor = ?");
$stmt->execute([$id]);
$proveedor = $stmt->fetch();
if (!$proveedor) { header("Location: lista_proveedor.php?error=Proveedor+no+encontrado"); exit; }

$asignados_stmt = $pdo->prepare("SELECT id_producto FROM proveedor_producto WHERE id_proveedor = ?");
$asignados_stmt->execute([$id]);
$asignados = array_column($asignados_stmt->fetchAll(), 'id_producto');

$productos = $pdo->query("SELECT p.id_producto, p.nombre, c.nombre AS categoria
                           FROM productos p JOIN categorias c ON c.id_categoria = p.id_categoria
                           ORDER BY c.nombre, p.nombre")->fetchAll();
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $ciudad   = trim($_POST['ciudad'] ?? '');
    $prods    = $_POST['productos'] ?? [];

    if (empty($nombre))   $errores[] = "El nombre es obligatorio.";
    if (empty($telefono)) $errores[] = "El teléfono es obligatorio.";
    if (empty($ciudad))   $errores[] = "La ciudad es obligatoria.";

    if (empty($errores)) {
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE proveedores SET nombre=?, telefono=?, ciudad=? WHERE id_proveedor=?")
                ->execute([$nombre, $telefono, $ciudad, $id]);
            $pdo->prepare("DELETE FROM proveedor_producto WHERE id_proveedor = ?")->execute([$id]);
            if (!empty($prods)) {
                $ins = $pdo->prepare("INSERT INTO proveedor_producto (id_proveedor, id_producto) VALUES (?, ?)");
                foreach ($prods as $id_prod) $ins->execute([$id, intval($id_prod)]);
            }
            $pdo->commit();
            header("Location: lista_proveedor.php?msg=Proveedor+actualizado+correctamente");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errores[] = "Error: " . $e->getMessage();
        }
    }
    $proveedor = array_merge($proveedor, $_POST);
    $asignados = $prods;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Proveedor — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-interior">

<?php include '../includes/navbar.php'; ?>

<div class="app-layout">
  <main class="content">

    <div class="content-header">
      <div>
        <h1 class="content-title">Editar Proveedor</h1>
        <p class="content-subtitle">Modificando: <strong><?= htmlspecialchars($proveedor['nombre']) ?></strong></p>
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
              <label for="nombre">Nombre <span class="requerido">*</span></label>
              <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($proveedor['nombre']) ?>">
            </div>

            <div class="form-group">
              <label for="telefono">Teléfono <span class="requerido">*</span></label>
              <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($proveedor['telefono']) ?>">
            </div>

            <div class="form-group">
              <label for="ciudad">Ciudad <span class="requerido">*</span></label>
              <input type="text" id="ciudad" name="ciudad" value="<?= htmlspecialchars($proveedor['ciudad']) ?>">
            </div>

          </div>

          <div class="form-group mt-20">
            <label>Productos que suministra</label>
            <div class="productos-grid">
              <?php foreach ($productos as $prod): ?>
              <label class="producto-check">
                <input type="checkbox" name="productos[]" value="<?= $prod['id_producto'] ?>"
                       <?= in_array($prod['id_producto'], $asignados) ? 'checked' : '' ?>>
                <div>
                  <div class="prod-nombre"><?= htmlspecialchars($prod['nombre']) ?></div>
                  <div class="prod-cat"><?= htmlspecialchars($prod['categoria']) ?></div>
                </div>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="form-acciones">
            <button type="submit" class="btn btn-advertencia btn-lg">Actualizar Proveedor</button>
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
