<?php
require_once '../conexion.php';

$balance = $pdo->query(
    "SELECT
        (SELECT COALESCE(SUM(total), 0)        FROM ventas)            AS ingresos,
        (SELECT COALESCE(SUM(total_pagado), 0) FROM compras_proveedor) AS gastos,
        (SELECT COUNT(*)                        FROM ventas)            AS num_ventas,
        (SELECT COUNT(DISTINCT cedula_cliente)  FROM ventas)            AS clientes_activos"
)->fetch();
$balance['utilidad'] = $balance['ingresos'] - $balance['gastos'];

$por_cat = $pdo->query(
    "SELECT c.nombre AS categoria, c.impuesto,
            COALESCE(SUM(dv.subtotal_linea), 0) AS subtotal,
            COALESCE(SUM(dv.iva_linea), 0)      AS iva,
            COALESCE(SUM(dv.total_linea), 0)    AS total
     FROM categorias c
     LEFT JOIN productos p       ON p.id_categoria  = c.id_categoria
     LEFT JOIN detalle_ventas dv ON dv.id_producto  = p.id_producto
     GROUP BY c.id_categoria, c.nombre, c.impuesto
     ORDER BY total DESC"
)->fetchAll();

$top_prods = $pdo->query(
    "SELECT p.nombre, c.nombre AS categoria,
            SUM(dv.cantidad)    AS unidades,
            SUM(dv.total_linea) AS total
     FROM detalle_ventas dv
     JOIN productos  p ON dv.id_producto = p.id_producto
     JOIN categorias c ON p.id_categoria = c.id_categoria
     GROUP BY p.id_producto
     ORDER BY unidades DESC
     LIMIT 5"
)->fetchAll();

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
  <title>Reportes — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-interior">

<?php include '../includes/navbar.php'; ?>

<div class="app-layout">
  <main class="content">

    <div class="content-header">
      <div>
        <h1 class="content-title">Reportes</h1>
        <p class="content-subtitle">Resumen general de la tienda</p>
      </div>
      <div class="header-acciones">
        <a href="clientes_reporte.php" class="btn btn-contorno">Clientes</a>
        <a href="ingresos_gastos.php"  class="btn btn-contorno">Ingresos/Gastos</a>
      </div>
    </div>

    <div class="stat-cards">
      <div class="stat-card">
        <div class="stat-icono stat-icono-drogueria">$</div>
        <div>
          <div class="stat-valor-verde">$<?= number_format($balance['ingresos'], 0, ',', '.') ?></div>
          <div class="stat-etiqueta">Ingresos totales · <?= $balance['num_ventas'] ?> ventas</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icono stat-icono-aseo">↓</div>
        <div>
          <div class="stat-valor-peligro">$<?= number_format($balance['gastos'], 0, ',', '.') ?></div>
          <div class="stat-etiqueta">Gastos a proveedores</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icono">↑</div>
        <div>
          <div class="<?= $balance['utilidad'] >= 0 ? 'stat-valor-verde' : 'stat-valor-peligro' ?>">
            $<?= number_format($balance['utilidad'], 0, ',', '.') ?>
          </div>
          <div class="stat-etiqueta">Utilidad neta</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icono stat-icono-papeleria">👤</div>
        <div>
          <div class="stat-valor"><?= $balance['clientes_activos'] ?></div>
          <div class="stat-etiqueta">Clientes que han comprado</div>
        </div>
      </div>
    </div>

    <div class="dos-col">

      <div class="card">
        <div class="card-header"><h3>Ventas por categoría</h3></div>
        <div class="tabla-wrapper">
          <table class="tabla">
            <thead>
              <tr>
                <th>Categoría</th>
                <th>IVA</th>
                <th class="th-right">Subtotal</th>
                <th class="th-right">IVA cobrado</th>
                <th class="th-right">Total</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($por_cat as $cat): ?>
              <tr>
                <td><span class="badge <?= $cat_badge[$cat['categoria']] ?? 'badge-secundario' ?>"><?= htmlspecialchars($cat['categoria']) ?></span></td>
                <td><?= $cat['impuesto'] > 0 ? $cat['impuesto'].'%' : '—' ?></td>
                <td class="td-right">$<?= number_format($cat['subtotal'], 0, ',', '.') ?></td>
                <td class="td-muted">$<?= number_format($cat['iva'], 0, ',', '.') ?></td>
                <td class="td-verde">$<?= number_format($cat['total'], 0, ',', '.') ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>Top 5 productos más vendidos</h3></div>
        <div class="card-body">
          <?php if ($top_prods): ?>
            <?php foreach ($top_prods as $i => $p): ?>
              <div class="rank-fila">
                <div class="rank-num"><?= $i+1 ?></div>
                <div class="rank-info">
                  <div class="rank-nombre"><?= htmlspecialchars($p['nombre']) ?></div>
                  <div class="rank-sub">
                    <span class="badge <?= $cat_badge[$p['categoria']] ?? 'badge-secundario' ?>"><?= $p['categoria'] ?></span>
                  </div>
                </div>
                <div class="rank-info">
                  <div class="rank-val">$<?= number_format($p['total'], 0, ',', '.') ?></div>
                  <div class="rank-sub"><?= $p['unidades'] ?> uds.</div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="estado-vacio"><p>Sin ventas registradas</p></div>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <div class="dos-col">
      <a href="clientes_reporte.php" class="card tarjeta-reporte">
        <div class="tarjeta-reporte-body">
          <div class="tarjeta-reporte-icono">⭐</div>
          <div class="tarjeta-reporte-titulo">Análisis de clientes</div>
          <div class="tarjeta-reporte-desc">Cliente frecuente · mayor comprador · compraron solo una vez</div>
        </div>
      </a>
      <a href="ingresos_gastos.php" class="card tarjeta-reporte">
        <div class="tarjeta-reporte-body">
          <div class="tarjeta-reporte-icono">💰</div>
          <div class="tarjeta-reporte-titulo">Ingresos vs Gastos</div>
          <div class="tarjeta-reporte-desc">Detalle de pagos a proveedores · balance financiero</div>
        </div>
      </a>
    </div>

    <a href="../index.php" class="btn btn-contorno">Volver al menú</a>
  </main>
</div>

<footer class="footer"><p>Tienda App</p></footer>
</body>
</html>