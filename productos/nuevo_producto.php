<?php
require_once '../conexion.php';

$errores    = [];
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
$empaques   = $pdo->query("SELECT * FROM empaques ORDER BY tipo")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo      = trim($_POST['codigo'] ?? '');
    $nombre      = trim($_POST['nombre'] ?? '');
    $peso        = floatval($_POST['peso'] ?? 0);
    $cantidad    = intval($_POST['cantidad_almacenada'] ?? 0);
    $id_categoria= intval($_POST['id_categoria'] ?? 0);
    $id_empaque  = intval($_POST['id_empaque'] ?? 0);
    $precio      = floatval($_POST['precio_unitario'] ?? 0);

    if (empty($codigo))       $errores[] = "El código del producto es obligatorio.";
    if (empty($nombre))       $errores[] = "El nombre del producto es obligatorio.";
    if ($peso <= 0)            $errores[] = "El peso debe ser mayor a 0.";
    if ($cantidad < 0)         $errores[] = "La cantidad no puede ser negativa.";
    if ($id_categoria === 0)   $errores[] = "Selecciona una categoría.";
    if ($id_empaque === 0)     $errores[] = "Selecciona un tipo de empaque.";
    if ($precio <= 0)          $errores[] = "El precio unitario debe ser mayor a 0.";

    if (empty($errores)) {
        $check = $pdo->prepare("SELECT id_producto FROM productos WHERE codigo = ?");
        $check->execute([$codigo]);
        if ($check->fetch()) $errores[] = "Ya existe un producto con ese código.";
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare("INSERT INTO productos (codigo, nombre, peso, cantidad_almacenada, id_categoria, id_empaque, precio_unitario) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$codigo, $nombre, $peso, $cantidad, $id_categoria, $id_empaque, $precio]);
        header("Location: lista_producto.php?msg=Producto+registrado+correctamente");
        exit;
    }
}

$iva_map = [];
foreach ($categorias as $c) $iva_map[$c['id_categoria']] = $c['impuesto'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nuevo Producto — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-interior">

<?php include '../includes/navbar.php'; ?>

<div class="app-layout">
  <main class="content">

    <div class="content-header">
      <div>
        <h1 class="content-title">Nuevo Producto</h1>
        <p class="content-subtitle">Registrar un nuevo producto en el inventario</p>
      </div>
      <a href="lista_producto.php" class="btn btn-contorno">Volver</a>
    </div>

    <?php foreach ($errores as $err): ?>
      <p class="msg-error"><?= htmlspecialchars($err) ?></p>
    <?php endforeach; ?>

    <div class="card">
      <div class="card-header"><h3>Datos del producto</h3></div>
      <div class="card-body">
        <form method="POST">
          <div class="form-grid">

            <div class="form-group">
              <label for="codigo">Código <span class="requerido">*</span></label>
              <input type="text" id="codigo" name="codigo"
                     value="<?= htmlspecialchars($_POST['codigo'] ?? '') ?>"
                     placeholder="Ej: PAP-001">
            </div>

            <div class="form-group">
              <label for="nombre">Nombre del producto <span class="requerido">*</span></label>
              <input type="text" id="nombre" name="nombre"
                     value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                     placeholder="Ej: Cuaderno universitario">
            </div>

            <div class="form-group">
              <label for="id_categoria">Categoría <span class="requerido">*</span></label>
              <select id="id_categoria" name="id_categoria" required>
                <option value="">— Seleccionar —</option>
                <?php foreach ($categorias as $cat): ?>
                  <option value="<?= $cat['id_categoria'] ?>"
                          data-iva="<?= $cat['impuesto'] ?>"
                          <?= (($_POST['id_categoria'] ?? '') == $cat['id_categoria']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nombre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="id_empaque">Tipo de empaque <span class="requerido">*</span></label>
              <select id="id_empaque" name="id_empaque" required>
                <option value="">— Seleccionar —</option>
                <?php foreach ($empaques as $emp): ?>
                  <option value="<?= $emp['id_empaque'] ?>"
                          <?= (($_POST['id_empaque'] ?? '') == $emp['id_empaque']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($emp['tipo']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="peso">Peso (gramos) <span class="requerido">*</span></label>
              <input type="number" id="peso" name="peso" min="0.01" step="0.01"
                     value="<?= htmlspecialchars($_POST['peso'] ?? '') ?>"
                     placeholder="Ej: 250">
            </div>

            <div class="form-group">
              <label for="cantidad_almacenada">Cantidad en stock <span class="requerido">*</span></label>
              <input type="number" id="cantidad_almacenada" name="cantidad_almacenada" min="0"
                     value="<?= htmlspecialchars($_POST['cantidad_almacenada'] ?? '') ?>"
                     placeholder="Ej: 50">
              <span class="form-hint">Stock mínimo recomendado: 5 unidades</span>
            </div>

            <div class="form-group">
              <label for="precio_unitario">Precio unitario ($) <span class="requerido">*</span></label>
              <input type="number" id="precio_unitario" name="precio_unitario" min="0.01" step="0.01"
                     value="<?= htmlspecialchars($_POST['precio_unitario'] ?? '') ?>"
                     placeholder="Ej: 3500">
            </div>

            <div class="form-group">
              <label>IVA aplicable</label>
              <div id="iva-info" class="alerta-info">Selecciona una categoría para ver el IVA</div>
            </div>

          </div>

          <div class="form-acciones">
            <button type="submit" class="btn btn-lg">Guardar Producto</button>
            <a href="lista_producto.php" class="btn btn-contorno btn-lg">Cancelar</a>
          </div>
        </form>
      </div>
    </div>

  </main>
</div>

<footer class="footer">
  <p>Tienda App</p>
</footer>

<script>
document.getElementById('id_categoria').addEventListener('change', function() {
  const opt = this.options[this.selectedIndex];
  const iva = opt.dataset.iva;
  const box = document.getElementById('iva-info');
  if (iva !== undefined) {
    box.textContent = iva > 0
      ? 'Esta categoría aplica IVA del ' + iva + '%'
      : 'Supermercado: no aplica impuesto';
    box.className = iva > 0 ? 'alerta-advertencia' : 'alerta-exito';
  }
});
</script>
</body>
</html>
