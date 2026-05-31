<?php
require_once '../conexion.php';

// Consultar categorías con conteo de productos
$sql = "SELECT c.id_categoria, c.nombre, c.descripcion, c.impuesto,
               COUNT(p.id_producto) AS total_productos
        FROM categorias c
        LEFT JOIN productos p ON p.id_categoria = c.id_categoria
        GROUP BY c.id_categoria
        ORDER BY c.nombre";
$categorias = $pdo->query($sql)->fetchAll();

// Mapa de estilo por categoría
$estilos = [
    'Papelería'    => ['badge' => 'badge-papeleria',    'emoji' => '✏️', 'clase' => 'papeleria'],
    'Droguería'    => ['badge' => 'badge-drogueria',    'emoji' => '💊', 'clase' => 'drogueria'],
    'Supermercado' => ['badge' => 'badge-supermercado', 'emoji' => '🥫', 'clase' => 'supermercado'],
    'Aseo'         => ['badge' => 'badge-aseo',         'emoji' => '🧴', 'clase' => 'aseo'],
];

function getEstilo($nombre, $campo) {
    global $estilos;
    return $estilos[$nombre][$campo] ?? 'badge-secondary';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Categorías — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="main-content">

  <div class="page-header">
    <div>
      <h1 class="page-title">
        <span class="title-icon" style="background:#EEF2FF;">📂</span>
        Categorías
      </h1>
      <p class="page-subtitle">Gestión de categorías de productos</p>
    </div>
    <a href="nueva_categoria.php" class="btn btn-primary">
      ➕ Nueva Categoría
    </a>
  </div>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">❌ <?= htmlspecialchars($_GET['error']) ?></div>
  <?php endif; ?>

  <?php if (empty($categorias)): ?>
    <div class="card">
      <div class="card-body">
        <div class="empty-state">
          <div class="empty-icon">📂</div>
          <p>No hay categorías registradas aún.</p>
          <a href="nueva_categoria.php" class="btn btn-primary">Agregar primera categoría</a>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="stat-cards">
      <?php foreach ($categorias as $cat):
        $clase = getEstilo($cat['nombre'], 'clase');
        $emoji = getEstilo($cat['nombre'], 'emoji');
        $iva   = $cat['impuesto'] > 0 ? "IVA {$cat['impuesto']}%" : "Sin IVA";
      ?>
      <div class="stat-card stat-<?= $clase ?>">
        <div class="stat-icon"><?= $emoji ?></div>
        <div>
          <div class="stat-value"><?= $cat['total_productos'] ?></div>
          <div class="stat-label"><?= htmlspecialchars($cat['nombre']) ?> · <?= $iva ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="card">
      <div class="card-header">
        <h3>📋 Lista de categorías</h3>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Categoría</th>
              <th>Descripción</th>
              <th>Impuesto</th>
              <th>Productos</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categorias as $cat):
              $badge = getEstilo($cat['nombre'], 'badge');
              $emoji = getEstilo($cat['nombre'], 'emoji');
              $iva   = $cat['impuesto'] > 0 ? "IVA {$cat['impuesto']}%" : "Sin IVA";
            ?>
            <tr>
              <td><?= $cat['id_categoria'] ?></td>
              <td>
                <span class="badge <?= $badge ?>">
                  <?= $emoji ?> <?= htmlspecialchars($cat['nombre']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($cat['descripcion'] ?? '—') ?></td>
              <td>
                <?php if ($cat['impuesto'] > 0): ?>
                  <span class="badge badge-warning"><?= $cat['impuesto'] ?>%</span>
                <?php else: ?>
                  <span class="badge badge-success">Sin IVA</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge badge-secondary"><?= $cat['total_productos'] ?> productos</span>
              </td>
              <td>
                <div class="actions">
                  <a href="../productos/listar_productos.php?categoria=<?= $cat['id_categoria'] ?>"
                     class="btn btn-sm btn-outline">👁️ Ver productos</a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

</div>

<footer class="footer">Tienda App &copy; <?= date('Y') ?></footer>
</body>
</html>
