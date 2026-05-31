<?php
require_once '../conexion.php';

$sql = "SELECT c.id_categoria, c.nombre, c.descripcion, c.impuesto,
               COUNT(p.id_producto) AS total_productos
        FROM categorias c
        LEFT JOIN productos p ON p.id_categoria = c.id_categoria
        GROUP BY c.id_categoria
        ORDER BY c.nombre";
$categorias = $pdo->query($sql)->fetchAll();

$estilos = [
    'Papelería'    => ['badge' => 'badge-papeleria',    'clase' => 'papeleria'],
    'Droguería'    => ['badge' => 'badge-drogueria',    'clase' => 'drogueria'],
    'Supermercado' => ['badge' => 'badge-supermercado', 'clase' => 'supermercado'],
    'Aseo'         => ['badge' => 'badge-aseo',         'clase' => 'aseo'],
];

function getEstilo($nombre, $campo) {
    global $estilos;
    return $estilos[$nombre][$campo] ?? 'badge-secundario';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Categorías — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-interior">

<?php include '../includes/navbar.php'; ?>

<div class="app-layout">
  <main class="content">

    <div class="content-header">
      <div>
        <h1 class="content-title">Categorías</h1>
        <p class="content-subtitle">Gestión de categorías de productos</p>
      </div>
      <a href="nueva_categoria.php" class="btn">Nueva categoría</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <p class="msg-exito"><?= htmlspecialchars($_GET['msg']) ?></p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
      <p class="msg-error"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <?php if (!empty($categorias)): ?>
      <div class="stat-cards">
        <?php foreach ($categorias as $cat):
          $clase = getEstilo($cat['nombre'], 'clase');
          $iva   = $cat['impuesto'] > 0 ? "IVA {$cat['impuesto']}%" : "Sin IVA";
        ?>
        <div class="stat-card stat-<?= $clase ?>">
          <div class="stat-icono"></div>
          <div>
            <div class="stat-valor"><?= $cat['total_productos'] ?></div>
            <div class="stat-etiqueta"><?= htmlspecialchars($cat['nombre']) ?> &middot; <?= $iva ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (empty($categorias)): ?>
      <div class="card">
        <div class="card-body">
          <div class="estado-vacio">
            <span class="icono-vacio">—</span>
            <p>No hay categorías registradas aún.</p>
            <a href="nueva_categoria.php" class="btn">Agregar primera categoría</a>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="card">
        <div class="card-header">
          <h3>Lista de categorías</h3>
        </div>
        <div class="tabla-wrapper">
          <table class="tabla">
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
                $iva   = $cat['impuesto'] > 0 ? "IVA {$cat['impuesto']}%" : "Sin IVA";
              ?>
              <tr>
                <td><?= $cat['id_categoria'] ?></td>
                <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($cat['nombre']) ?></span></td>
                <td><?= htmlspecialchars($cat['descripcion'] ?? '—') ?></td>
                <td>
                  <?php if ($cat['impuesto'] > 0): ?>
                    <span class="badge badge-advertencia"><?= $cat['impuesto'] ?>%</span>
                  <?php else: ?>
                    <span class="badge badge-exito">Sin IVA</span>
                  <?php endif; ?>
                </td>
                <td><span class="badge badge-secundario"><?= $cat['total_productos'] ?> productos</span></td>
                <td>
                  <div class="td-acciones">
                    <a href="../productos/lista_producto.php?categoria=<?= $cat['id_categoria'] ?>"
                       class="btn btn-sm btn-contorno">Ver productos</a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <a href="../index.php" class="btn-volver btn">Volver al menú</a>
  </main>
</div>

<footer class="footer">
  <p>Tienda App</p>
</footer>
</body>
</html>
