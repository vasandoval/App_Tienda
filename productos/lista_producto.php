<?php
require_once '../conexion.php';

$filtro_cat = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$busqueda   = trim($_GET['buscar'] ?? '');

$sql = "SELECT p.*, c.nombre AS categoria, c.impuesto, e.tipo AS empaque
        FROM productos p
        JOIN categorias c ON c.id_categoria = p.id_categoria
        JOIN empaques   e ON e.id_empaque   = p.id_empaque
        WHERE 1=1";
$params = [];

if ($filtro_cat > 0) {
    $sql .= " AND p.id_categoria = ?";
    $params[] = $filtro_cat;
}
if (!empty($busqueda)) {
    $sql .= " AND (p.nombre LIKE ? OR p.codigo LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}
$sql .= " ORDER BY c.nombre, p.nombre";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();

$cat_estilos = [
    'Papelería'    => ['badge' => 'badge-papeleria',    'clase' => 'papeleria'],
    'Droguería'    => ['badge' => 'badge-drogueria',    'clase' => 'drogueria'],
    'Supermercado' => ['badge' => 'badge-supermercado', 'clase' => 'supermercado'],
    'Aseo'         => ['badge' => 'badge-aseo',         'clase' => 'aseo'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Productos — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-interior">

<?php include '../includes/navbar.php'; ?>

<div class="app-layout">
  <main class="content">

    <div class="content-header">
      <div>
        <h1 class="content-title">Productos</h1>
        <p class="content-subtitle"><?= count($productos) ?> producto(s) encontrado(s)</p>
      </div>
      <a href="nuevo_producto.php" class="btn">Nuevo Producto</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <p class="msg-exito"><?= htmlspecialchars($_GET['msg']) ?></p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
      <p class="msg-error"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <form method="GET" class="barra-busqueda">
      <div class="input-busqueda">
        <input type="text" name="buscar" placeholder="Buscar por nombre o código..."
               value="<?= htmlspecialchars($busqueda) ?>">
      </div>
      <select name="categoria" class="filtro-select">
        <option value="0">Todas las categorías</option>
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= $cat['id_categoria'] ?>"
                  <?= $filtro_cat === (int)$cat['id_categoria'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn">Filtrar</button>
      <a href="lista_producto.php" class="btn btn-contorno">Limpiar</a>
    </form>

    <?php if (empty($productos)): ?>
      <div class="card">
        <div class="card-body">
          <div class="estado-vacio">
            <span class="icono-vacio">—</span>
            <p>No se encontraron productos.</p>
            <a href="nuevo_producto.php" class="btn">Agregar producto</a>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="card">
        <div class="tabla-wrapper">
          <table class="tabla">
            <thead>
              <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Peso</th>
                <th>Empaque</th>
                <th>Stock</th>
                <th>Precio</th>
                <th>IVA</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($productos as $p):
                $est   = $cat_estilos[$p['categoria']] ?? ['badge'=>'badge-secundario','clase'=>''];
                $stock = intval($p['cantidad_almacenada']);
                $bajo  = $stock <= 5;
              ?>
              <tr class="cat-<?= $est['clase'] ?>">
                <td><span class="codigo-inline"><?= htmlspecialchars($p['codigo']) ?></span></td>
                <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                <td><span class="badge <?= $est['badge'] ?>"><?= htmlspecialchars($p['categoria']) ?></span></td>
                <td><?= htmlspecialchars($p['peso']) ?> g</td>
                <td><?= htmlspecialchars($p['empaque']) ?></td>
                <td>
                  <span class="badge <?= $bajo ? 'badge-peligro' : 'badge-exito' ?>">
                    <?= $stock ?>
                  </span>
                </td>
                <td>$<?= number_format($p['precio_unitario'], 2, ',', '.') ?></td>
                <td>
                  <?php if ($p['impuesto'] > 0): ?>
                    <span class="badge badge-advertencia"><?= $p['impuesto'] ?>%</span>
                  <?php else: ?>
                    <span class="badge badge-exito">0%</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="td-acciones">
                    <a href="editar_producto.php?id=<?= $p['id_producto'] ?>"
                       class="btn btn-sm btn-advertencia">Editar</a>
                    <a href="eliminar_producto.php?id=<?= $p['id_producto'] ?>"
                       class="btn btn-sm btn-peligro"
                       onclick="return confirm('¿Eliminar este producto?')">Eliminar</a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <?php
      $bajo_stock = array_filter($productos, fn($p) => intval($p['cantidad_almacenada']) <= 5);
      if (!empty($bajo_stock)):
      ?>
      <p class="alerta-advertencia">
        <strong><?= count($bajo_stock) ?> producto(s)</strong> con stock en nivel mínimo (&le; 5 unidades). Considera hacer pedido a proveedores.
      </p>
      <?php endif; ?>

    <?php endif; ?>

    <a href="../index.php" class="btn-volver btn">Volver al menú</a>
  </main>
</div>

<footer class="footer">
  <p>Tienda App</p>
</footer>
</body>
</html>
