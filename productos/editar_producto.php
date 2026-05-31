<?php
require_once '../conexion.php';

$id = intval($_GET['id'] ?? 0);
if ($id === 0) { header("Location: lista_producto.php?error=Producto+no+válido"); exit; }

$stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch();
if (!$producto) { header("Location: lista_producto.php?error=Producto+no+encontrado"); exit; }

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
$empaques   = $pdo->query("SELECT * FROM empaques ORDER BY tipo")->fetchAll();
$errores    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo      = trim($_POST['codigo'] ?? '');
    $nombre      = trim($_POST['nombre'] ?? '');
    $peso        = floatval($_POST['peso'] ?? 0);
    $cantidad    = intval($_POST['cantidad_almacenada'] ?? 0);
    $id_categoria= intval($_POST['id_categoria'] ?? 0);
    $id_empaque  = intval($_POST['id_empaque'] ?? 0);
    $precio      = floatval($_POST['precio_unitario'] ?? 0);

    if (empty($codigo))      $errores[] = "El código es obligatorio.";
    if (empty($nombre))      $errores[] = "El nombre es obligatorio.";
    if ($peso <= 0)           $errores[] = "El peso debe ser mayor a 0.";
    if ($id_categoria === 0)  $errores[] = "Selecciona una categoría.";
    if ($id_empaque === 0)    $errores[] = "Selecciona un empaque.";
    if ($precio <= 0)         $errores[] = "El precio debe ser mayor a 0.";

    if (empty($errores)) {
        $check = $pdo->prepare("SELECT id_producto FROM productos WHERE codigo = ? AND id_producto != ?");
        $check->execute([$codigo, $id]);
        if ($check->fetch()) $errores[] = "Ya existe otro producto con ese código.";
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare("UPDATE productos SET codigo=?, nombre=?, peso=?, cantidad_almacenada=?, id_categoria=?, id_empaque=?, precio_unitario=? WHERE id_producto=?");
        $stmt->execute([$codigo, $nombre, $peso, $cantidad, $id_categoria, $id_empaque, $precio, $id]);
        header("Location: lista_producto.php?msg=Producto+actualizado+correctamente");
        exit;
    }
    $producto = array_merge($producto, $_POST);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Producto — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-interior">

<?php include '../includes/navbar.php'; ?>

<div class="app-layout">
  <main class="content">

    <div class="content-header">
      <div>
        <h1 class="content-title">Editar Producto</h1>
        <p class="content-subtitle">Modificar datos de: <strong><?= htmlspecialchars($producto['nombre']) ?></strong></p>
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
                     value="<?= htmlspecialchars($producto['codigo']) ?>">
            </div>

            <div class="form-group">
              <label for="nombre">Nombre <span class="requerido">*</span></label>
              <input type="text" id="nombre" name="nombre"
                     value="<?= htmlspecialchars($producto['nombre']) ?>">
            </div>

            <div class="form-group">
              <label for="id_categoria">Categoría <span class="requerido">*</span></label>
              <select id="id_categoria" name="id_categoria">
                <?php foreach ($categorias as $cat): ?>
                  <option value="<?= $cat['id_categoria'] ?>"
                          data-iva="<?= $cat['impuesto'] ?>"
                          <?= $producto['id_categoria'] == $cat['id_categoria'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nombre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="id_empaque">Empaque <span class="requerido">*</span></label>
              <select id="id_empaque" name="id_empaque">
                <?php foreach ($empaques as $emp): ?>
                  <option value="<?= $emp['id_empaque'] ?>"
                          <?= $producto['id_empaque'] == $emp['id_empaque'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($emp['tipo']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="peso">Peso (gramos) <span class="requerido">*</span></label>
              <input type="number" id="peso" name="peso" min="0.01" step="0.01"
                     value="<?= htmlspecialchars($producto['peso']) ?>">
            </div>

            <div class="form-group">
              <label for="cantidad_almacenada">Cantidad en stock</label>
              <input type="number" id="cantidad_almacenada" name="cantidad_almacenada" min="0"
                     value="<?= htmlspecialchars($producto['cantidad_almacenada']) ?>">
            </div>

            <div class="form-group">
              <label for="precio_unitario">Precio unitario ($) <span class="requerido">*</span></label>
              <input type="number" id="precio_unitario" name="precio_unitario" min="0.01" step="0.01"
                     value="<?= htmlspecialchars($producto['precio_unitario']) ?>">
            </div>

          </div>

          <div class="form-acciones">
            <button type="submit" class="btn btn-advertencia btn-lg">Actualizar Producto</button>
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
</body>
</html>
