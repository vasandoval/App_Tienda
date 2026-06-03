<?php
require_once '../conexion.php';

$msg_stock = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_stock'])) {
    $id_prod   = (int)$_POST['id_producto'];
    $nuevo_stk = (int)$_POST['nuevo_stock'];
    if ($nuevo_stk >= 0) {
        $pdo->prepare("UPDATE productos SET cantidad_almacenada = ? WHERE id_producto = ?")
            ->execute([$nuevo_stk, $id_prod]);
        $msg_stock = 'Stock actualizado correctamente.';
    }
}

$filtro = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

$sql = "SELECT p.id_producto, p.codigo, p.nombre, p.peso,
               p.cantidad_almacenada, p.precio_unitario,
               c.nombre AS categoria, c.impuesto, c.id_categoria,
               e.tipo   AS empaque
        FROM productos p
        JOIN categorias c ON p.id_categoria = c.id_categoria
        JOIN empaques   e ON p.id_empaque   = e.id_empaque
        WHERE 1=1";
$params = [];
if ($filtro > 0) { $sql .= " AND p.id_categoria = ?"; $params[] = $filtro; }
$sql .= " ORDER BY c.nombre, p.nombre";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
$total_bajo = $pdo->query("SELECT COUNT(*) FROM v_stock_bajo")->fetchColumn();

$cat_badge = [
    'Papelería'    => 'badge-papeleria',
    'Droguería'    => 'badge-drogueria',
    'Supermercado' => 'badge-supermercado',
    'Aseo'         => 'badge-aseo',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Inventario — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-interior">

<?php include '../includes/navbar.php'; ?>

<div class="app-layout">
  <main class="content">

    <div class="content-header">
      <div>
        <h1 class="content-title">Inventario</h1>
        <p class="content-subtitle">Control de stock por producto</p>
      </div>
    </div>

    <?php if ($msg_stock): ?>
      <p class="msg-exito"><?= htmlspecialchars($msg_stock) ?></p>
    <?php endif; ?>

    <?php if ($total_bajo > 0): ?>
      <p class="alerta-advertencia">
        <strong><?= $total_bajo ?> producto(s)</strong> con stock menor a 5 unidades. Revisa la tabla y contacta a los proveedores.
      </p>
    <?php else: ?>
      <p class="alerta-exito">Todo el inventario está sobre el stock mínimo (5 unidades).</p>
    <?php endif; ?>

    <div class="filtros-cat">
      <a href="inventario.php" class="btn btn-sm <?= $filtro === 0 ? '' : 'btn-contorno' ?>">Todos</a>
      <?php foreach ($categorias as $cat): ?>
        <a href="inventario.php?cat=<?= $cat['id_categoria'] ?>"
           class="btn btn-sm <?= $filtro === (int)$cat['id_categoria'] ? '' : 'btn-contorno' ?>">
          <?= htmlspecialchars($cat['nombre']) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="card">
      <div class="card-header">
        <h3>Productos — <?= count($productos) ?> registros</h3>
      </div>
      <div class="tabla-wrapper">
        <table class="tabla">
          <thead>
            <tr>
              <th>Código</th><th>Nombre</th><th>Categoría</th><th>Empaque</th>
              <th>Peso (g)</th><th>Precio unit.</th><th>IVA</th>
              <th>Stock</th><th>Estado</th><th>Actualizar stock</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($productos as $p):
            $bajo  = (int)$p['cantidad_almacenada'] < 5;
            $badge = $cat_badge[$p['categoria']] ?? 'badge-secundario';
          ?>
            <tr>
              <td><span class="codigo-inline"><?= htmlspecialchars($p['codigo']) ?></span></td>
              <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
              <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($p['categoria']) ?></span></td>
              <td><?= htmlspecialchars($p['empaque']) ?></td>
              <td><?= number_format($p['peso'], 0) ?></td>
              <td>$<?= number_format($p['precio_unitario'], 2, ',', '.') ?></td>
              <td>
                <?php if ($p['impuesto'] > 0): ?>
                  <span class="badge badge-advertencia"><?= $p['impuesto'] ?>%</span>
                <?php else: ?>
                  <span class="badge badge-exito">0%</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="<?= $bajo ? 'stock-bajo' : 'stock-ok' ?>"><?= $p['cantidad_almacenada'] ?></span>
              </td>
              <td>
                <span class="badge <?= $bajo ? 'badge-peligro' : 'badge-exito' ?>">
                  <?= $bajo ? '⚠ Bajo' : '✓ OK' ?>
                </span>
              </td>
              <td>
                <form method="POST" class="td-acciones">
                  <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                  <input type="number" name="nuevo_stock"
                         value="<?= $p['cantidad_almacenada'] ?>" min="0">
                  <button type="submit" name="actualizar_stock" class="btn btn-sm btn-contorno">Guardar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <a href="../index.php" class="btn btn-contorno">Volver al menú</a>
  </main>
</div>

<footer class="footer"><p>Tienda App</p></footer>
</body>
</html>
