<?php
require_once '../conexion.php';

$errores = [];
$exito   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $impuesto    = floatval($_POST['impuesto'] ?? 0);

    // Validaciones
    if (empty($nombre)) $errores[] = "El nombre de la categoría es obligatorio.";
    if ($impuesto < 0 || $impuesto > 100) $errores[] = "El impuesto debe estar entre 0 y 100.";

    if (empty($errores)) {
        $stmt = $pdo->prepare("INSERT INTO categorias (nombre, descripcion, impuesto) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $impuesto]);
        header("Location: listar_categorias.php?msg=Categoría+registrada+correctamente");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nueva Categoría — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="main-content">

  <div class="page-header">
    <div>
      <h1 class="page-title">
        <span class="title-icon" style="background:#EEF2FF;">➕</span>
        Nueva Categoría
      </h1>
      <p class="page-subtitle">Registrar una nueva categoría de productos</p>
    </div>
    <a href="listar_categorias.php" class="btn btn-outline">← Volver</a>
  </div>

  <?php foreach ($errores as $err): ?>
    <div class="alert alert-danger">❌ <?= htmlspecialchars($err) ?></div>
  <?php endforeach; ?>

  <div class="card">
    <div class="card-header">
      <h3>📂 Datos de la categoría</h3>
    </div>
    <div class="card-body">
      <form method="POST">
        <div class="form-grid">

          <div class="form-group">
            <label for="nombre">Nombre <span class="required">*</span></label>
            <select name="nombre" id="nombre" required>
              <option value="">— Seleccionar —</option>
              <option value="Papelería"    <?= (($_POST['nombre'] ?? '') === 'Papelería')    ? 'selected' : '' ?>>✏️ Papelería</option>
              <option value="Droguería"    <?= (($_POST['nombre'] ?? '') === 'Droguería')    ? 'selected' : '' ?>>💊 Droguería</option>
              <option value="Supermercado" <?= (($_POST['nombre'] ?? '') === 'Supermercado') ? 'selected' : '' ?>>🥫 Supermercado</option>
              <option value="Aseo"         <?= (($_POST['nombre'] ?? '') === 'Aseo')         ? 'selected' : '' ?>>🧴 Aseo</option>
            </select>
          </div>

          <div class="form-group">
            <label for="impuesto">Impuesto (%) <span class="required">*</span></label>
            <input type="number" id="impuesto" name="impuesto"
                   min="0" max="100" step="0.01"
                   value="<?= htmlspecialchars($_POST['impuesto'] ?? '0') ?>"
                   placeholder="Ej: 7">
            <span class="form-hint">Papelería 7% · Droguería 3% · Aseo 5% · Supermercado 0%</span>
          </div>

          <div class="form-group full-width">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion"
                      placeholder="Descripción opcional de la categoría..."><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
          </div>

        </div>

        <div style="margin-top:1.5rem; display:flex; gap:0.8rem;">
          <button type="submit" class="btn btn-primary btn-lg">💾 Guardar Categoría</button>
          <a href="listar_categorias.php" class="btn btn-outline btn-lg">Cancelar</a>
        </div>
      </form>
    </div>
  </div>

</div>

<footer class="footer">Tienda App &copy; <?= date('Y') ?></footer>

<script>
// Autocompletar el impuesto según la categoría seleccionada
document.getElementById('nombre').addEventListener('change', function() {
  const ivas = { 'Papelería': 7, 'Droguería': 3, 'Supermercado': 0, 'Aseo': 5 };
  const val = ivas[this.value];
  if (val !== undefined) {
    document.getElementById('impuesto').value = val;
  }
});
</script>
</body>
</html>
