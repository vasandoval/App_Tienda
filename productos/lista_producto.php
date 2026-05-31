<?php
require_once '../conexion.php';

// Filtro por categoría
$filtro_cat = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$busqueda   = trim($_GET['buscar'] ?? '');

// Construir consulta
$sql = "SELECT p.*, c.nombre AS categoria, c.impuesto,
               e.tipo AS empaque
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

// Categorías para filtro
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();

// Mapa de estilos
$cat_estilos = [
    'Papelería'    => ['badge' => 'badge-papeleria',    'clase' => 'papeleria',    'emoji' => '✏️'],
    'Droguería'    => ['badge' => 'badge-drogueria',    'clase' => 'drogueria',    'emoji' => '💊'],
    'Supermercado' => ['badge' => 'badge-supermercado', 'clase' => 'supermercado', 'emoji' => '🥫'],
    'Aseo'         => ['badge' => 'badge-aseo',         'clase' => 'aseo',         'emoji' => '🧴'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Productos — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="main-content">

  <div class="page-header">
    <div>
      <h1 class="page-title">
        <span class="title-icon" style="background:#DBEAFE;">📦</span>
        Productos
      </h1>
      <p class="page-subtitle">
        <?= count($productos) ?> producto(s) encontrado(s)
      </p>
    </div>
    <a href="nuevo_producto.php" class="btn btn-primary">➕ Nuevo Producto</a>
  </div>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">❌ <?= htmlspecialchars($_GET['error']) ?></div>
  <?php endif; ?>

  <!-- Filtros -->
  <form method="GET" class="search-bar">
    <div class="search-input">
      <span class="search-icon">🔍</span>
      <input type="text" name="buscar" placeholder="Buscar por nombre o código..."
             value="<?= htmlspecialchars($busqueda) ?>">
    </div>
    <select name="categoria" style="padding:0.65rem 0.9rem; border:1.5px solid var(--border); border-radius:var(--radius-sm); font-family:inherit; font-size:0.9rem;">
      <option value="0">Todas las categorías</option>
      <?php foreach ($categorias as $cat): ?>
        <option value="<?= $cat['id_categoria'] ?>"
                <?= $filtro_cat === (int)$cat['id_categoria'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($cat['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">Filtrar</button>
    <a href="listar_productos.php" class="btn btn-outline">Limpiar</a>
  </form>

  <?php if (empty($productos)): ?>
    <div class="card">
      <div class="card-body">
        <div class="empty-state">
          <div class="empty-icon">📦</div>
          <p>No se encontraron productos.</p>
          <a href="nuevo_producto.php" class="btn btn-primary">Agregar producto</a>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="card">
      <div class="table-wrapper">
        <table>
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
              $est   = $cat_estilos[$p['categoria']] ?? ['badge'=>'badge-secondary','clase'=>'','emoji'=>'📦'];
              $stock = intval($p['cantidad_almacenada']);
              $bajo  = $stock <= 5;
              $precio_con_iva = $p['precio_unitario'] * (1 + $p['impuesto'] / 100);
            ?>
            <tr class="cat-<?= $est['clase'] ?>">
              <td><code style="background:var(--bg);padding:0.2rem 0.5rem;border-radius:4px;font-size:0.82rem;"><?= htmlspecialchars($p['codigo']) ?></code></td>
              <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
              <td><span class="badge <?= $est['badge'] ?>"><?= $est['emoji'] ?> <?= htmlspecialchars($p['categoria']) ?></span></td>
              <td><?= htmlspecialchars($p['peso']) ?> g</td>
              <td><?= htmlspecialchars($p['empaque']) ?></td>
              <td>
                <span class="badge <?= $bajo ? 'badge-danger' : 'badge-success' ?>">
                  <?= $bajo ? '⚠️' : '' ?> <?= $stock ?>
                </span>
              </td>
              <td>$<?= number_format($p['precio_unitario'], 2, ',', '.') ?></td>
              <td>
                <?php if ($p['impuesto'] > 0): ?>
                  <span class="badge badge-warning"><?= $p['impuesto'] ?>%</span>
                <?php else: ?>
                  <span class="badge badge-success">0%</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="actions">
                  <a href="editar_producto.php?id=<?= $p['id_producto'] ?>"
                     class="btn btn-sm btn-warning">✏️</a>
                  <a href="eliminar_producto.php?id=<?= $p['id_producto'] ?>"
                     class="btn btn-sm btn-danger"
                     onclick="return confirm('¿Eliminar este producto?')">🗑️</a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Aviso stock mínimo -->
    <?php
    $bajo_stock = array_filter($productos, fn($p) => intval($p['cantidad_almacenada']) <= 5);
    if (!empty($bajo_stock)):
    ?>
    <div class="alert alert-warning" style="margin-top:1rem;">
      ⚠️ <strong><?= count($bajo_stock) ?> producto(s)</strong> con stock en nivel mínimo (≤ 5 unidades). Considera hacer pedido a proveedores.
    </div>
    <?php endif; ?>

  <?php endif; ?>

</div>

<footer class="footer">Tienda App &copy; <?= date('Y') ?></footer>
</body>
</html>
