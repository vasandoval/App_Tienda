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

            // Asignar productos al proveedor
            if (!empty($prods)) {
                $ins = $pdo->prepare("INSERT INTO proveedor_producto (id_proveedor, id_producto) VALUES (?, ?)");
                foreach ($prods as $id_prod) {
                    $ins->execute([$id_prov, intval($id_prod)]);
                }
            }

            $pdo->commit();
            header("Location: listar_proveedores.php?msg=Proveedor+registrado+correctamente");
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
  <style>
    .productos-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 0.6rem;
      max-height: 300px;
      overflow-y: auto;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-sm);
      padding: 0.8rem;
      background: var(--bg);
    }
    .producto-check {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.4rem 0.6rem;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.15s;
    }
    .producto-check:hover { background: var(--white); }
    .producto-check input[type="checkbox"] { cursor: pointer; width: 16px; height: 16px; }
    .producto-check .prod-nombre { font-size: 0.85rem; font-weight: 600; }
    .producto-check .prod-cat    { font-size: 0.75rem; color: var(--text-muted); }
  </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="main-content">

  <div class="page-header">
    <div>
      <h1 class="page-title">
        <span class="title-icon" style="background:#D1FAE5;">➕</span>
        Nuevo Proveedor
      </h1>
      <p class="page-subtitle">Registrar un nuevo proveedor de productos</p>
    </div>
    <a href="listar_proveedores.php" class="btn btn-outline">← Volver</a>
  </div>

  <?php foreach ($errores as $err): ?>
    <div class="alert alert-danger">❌ <?= htmlspecialchars($err) ?></div>
  <?php endforeach; ?>

  <div class="card">
    <div class="card-header"><h3>🚚 Datos del proveedor</h3></div>
    <div class="card-body">
      <form method="POST">
        <div class="form-grid">

          <div class="form-group">
            <label for="nombre">Nombre del proveedor <span class="required">*</span></label>
            <input type="text" id="nombre" name="nombre"
                   value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                   placeholder="Ej: Distribuciones La 14">
          </div>

          <div class="form-group">
            <label for="telefono">Teléfono <span class="required">*</span></label>
            <input type="tel" id="telefono" name="telefono"
                   value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
                   placeholder="Ej: 3001234567">
          </div>

          <div class="form-group">
            <label for="ciudad">Ciudad de procedencia <span class="required">*</span></label>
            <input type="text" id="ciudad" name="ciudad"
                   value="<?= htmlspecialchars($_POST['ciudad'] ?? '') ?>"
                   placeholder="Ej: Bogotá">
          </div>

        </div>

        <!-- Productos que suministra -->
        <div class="form-group" style="margin-top:1.2rem;">
          <label>Productos que suministra</label>
          <span class="form-hint" style="margin-bottom:0.5rem;display:block;">
            Selecciona los productos que este proveedor puede abastecer
          </span>
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
              <p style="color:var(--text-muted);font-size:0.85rem;">
                No hay productos registrados aún.
              </p>
            <?php endif; ?>
          </div>
        </div>

        <div style="margin-top:1.5rem; display:flex; gap:0.8rem;">
          <button type="submit" class="btn btn-success btn-lg">💾 Guardar Proveedor</button>
          <a href="listar_proveedores.php" class="btn btn-outline btn-lg">Cancelar</a>
        </div>
      </form>
    </div>
  </div>

</div>

<footer class="footer">Tienda App &copy; <?= date('Y') ?></footer>
</body>
</html>
