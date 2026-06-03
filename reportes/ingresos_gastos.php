<?php
require_once '../conexion.php';

$balance = $pdo->query(
    "SELECT
        (SELECT COALESCE(SUM(total), 0)        FROM ventas)            AS ingresos,
        (SELECT COALESCE(SUM(total_pagado), 0) FROM compras_proveedor) AS gastos"
)->fetch();
$balance['utilidad'] = $balance['ingresos'] - $balance['gastos'];
$balance['margen']   = $balance['ingresos'] > 0
    ? round($balance['utilidad'] / $balance['ingresos'] * 100, 1) : 0;

$ventas_det = $pdo->query(
    "SELECT v.id_venta, v.fecha, cl.nombre, cl.apellido, v.subtotal, v.total_iva, v.total
     FROM ventas v
     JOIN clientes cl ON v.cedula_cliente = cl.cedula
     ORDER BY v.fecha DESC"
)->fetchAll();

$gastos_prov = $pdo->query(
    "SELECT pv.nombre, pv.ciudad,
            COUNT(cp.id_compra)  AS num_compras,
            SUM(cp.total_pagado) AS total
     FROM compras_proveedor cp
     JOIN proveedores pv ON cp.id_proveedor = pv.id_proveedor
     GROUP BY cp.id_proveedor
     ORDER BY total DESC"
)->fetchAll();

$compras_det = $pdo->query(
    "SELECT cp.id_compra, cp.fecha,
            pv.nombre AS proveedor, pv.ciudad,
            p.nombre  AS producto,  c.nombre AS categoria,
            cp.cantidad, cp.precio_unitario, cp.total_pagado
     FROM compras_proveedor cp
     JOIN proveedores pv ON cp.id_proveedor = pv.id_proveedor
     JOIN productos   p  ON cp.id_producto  = p.id_producto
     JOIN categorias  c  ON p.id_categoria  = c.id_categoria
     ORDER BY cp.fecha DESC"
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
  <title>Ingresos vs Gastos — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-interior">

<?php include '../includes/navbar.php'; ?>

<div class="app-layout">
  <main class="content">

    <div class="content-header">
      <div>
        <h1 class="content-title">Ingresos vs Gastos</h1>
        <p class="content-subtitle">Balance financiero de la tienda</p>
      </div>
      <a href="reportes.php" class="btn btn-contorno">← Reportes</a>
    </div>

    <div class="stat-cards">
      <div class="stat-card stat-card-verde">
        <div class="stat-icono stat-icono-drogueria">↑</div>
        <div>
          <div class="stat-valor-verde">$<?= number_format($balance['ingresos'], 0, ',', '.') ?></div>
          <div class="stat-etiqueta">Ingresos por ventas</div>
        </div>
      </div>
      <div class="stat-card stat-card-peligro">
        <div class="stat-icono stat-icono-aseo">↓</div>
        <div>
          <div class="stat-valor-peligro">$<?= number_format($balance['gastos'], 0, ',', '.') ?></div>
          <div class="stat-etiqueta">Gastos a proveedores</div>
        </div>
      </div>
      <div class="stat-card <?= $balance['utilidad'] >= 0 ? 'stat-card-verde' : 'stat-card-peligro' ?>">
        <div class="stat-icono">=</div>
        <div>
          <div class="<?= $balance['utilidad'] >= 0 ? 'stat-valor-verde' : 'stat-valor-peligro' ?>">
            <?= $balance['utilidad'] >= 0 ? '+' : '' ?>$<?= number_format($balance['utilidad'], 0, ',', '.') ?>
          </div>
          <div class="stat-etiqueta"><?= $balance['utilidad'] >= 0 ? 'Ganancia' : 'Pérdida' ?></div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icono">%</div>
        <div>
          <div class="stat-valor"><?= $balance['margen'] ?>%</div>
          <div class="stat-etiqueta">Margen sobre ventas</div>
        </div>
      </div>
    </div>

    <div class="dos-col">

      <div class="card">
        <div class="card-header card-header-verde">
          <h3 class="verde">Ingresos — detalle de ventas</h3>
        </div>
        <?php if ($ventas_det): ?>
        <div class="tabla-wrapper">
          <table class="tabla">
            <thead>
              <tr>
                <th>Factura</th><th>Cliente</th>
                <th class="th-right">IVA</th>
                <th class="th-right">Total</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($ventas_det as $v): ?>
              <tr>
                <td>
                  <a href="../ventas/factura.php?id=<?= $v['id_venta'] ?>" class="codigo-inline">
                    #<?= str_pad($v['id_venta'], 5, '0', STR_PAD_LEFT) ?>
                  </a>
                </td>
                <td><?= htmlspecialchars($v['nombre'].' '.$v['apellido']) ?></td>
                <td class="td-muted">$<?= number_format($v['total_iva'], 0, ',', '.') ?></td>
                <td class="td-verde">$<?= number_format($v['total'], 0, ',', '.') ?></td>
              </tr>
            <?php endforeach; ?>
            <tr class="tr-total">
              <td colspan="3"><strong>TOTAL INGRESOS</strong></td>
              <td class="td-verde">$<?= number_format($balance['ingresos'], 0, ',', '.') ?></td>
            </tr>
            </tbody>
          </table>
        </div>
        <?php else: ?>
          <div class="card-body"><div class="estado-vacio"><p>Sin ventas registradas</p></div></div>
        <?php endif; ?>
      </div>

      <div class="card">
        <div class="card-header card-header-peligro">
          <h3 class="peligro">Gastos — por proveedor</h3>
        </div>
        <div class="card-body">
          <?php if ($gastos_prov): ?>
            <?php foreach ($gastos_prov as $i => $pv): ?>
              <div class="rank-fila">
                <div class="rank-num"><?= $i+1 ?></div>
                <div class="rank-info">
                  <div class="rank-nombre"><?= htmlspecialchars($pv['nombre']) ?></div>
                  <div class="rank-sub"><?= htmlspecialchars($pv['ciudad']) ?> · <?= $pv['num_compras'] ?> compra(s)</div>
                </div>
                <div class="rank-val-peligro">$<?= number_format($pv['total'], 0, ',', '.') ?></div>
              </div>
            <?php endforeach; ?>
            <div class="total-gastos">
              <span>Total gastos</span>
              <span class="total-gastos-val">$<?= number_format($balance['gastos'], 0, ',', '.') ?></span>
            </div>
          <?php else: ?>
            <div class="estado-vacio"><p>Sin compras registradas</p></div>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <?php if ($compras_det): ?>
    <div class="card">
      <div class="card-header"><h3>Detalle completo de compras a proveedores</h3></div>
      <div class="tabla-wrapper">
        <table class="tabla">
          <thead>
            <tr>
              <th>#</th><th>Fecha</th><th>Proveedor</th><th>Ciudad</th>
              <th>Producto</th><th>Categoría</th>
              <th class="th-center">Cant.</th>
              <th class="th-right">Precio unit.</th>
              <th class="th-right">Total pagado</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($compras_det as $cp): ?>
            <tr>
              <td><span class="codigo-inline">#<?= $cp['id_compra'] ?></span></td>
              <td><?= date('d/m/Y', strtotime($cp['fecha'])) ?></td>
              <td><strong><?= htmlspecialchars($cp['proveedor']) ?></strong></td>
              <td><?= htmlspecialchars($cp['ciudad']) ?></td>
              <td><?= htmlspecialchars($cp['producto']) ?></td>
              <td><span class="badge <?= $cat_badge[$cp['categoria']] ?? 'badge-secundario' ?>"><?= $cp['categoria'] ?></span></td>
              <td class="td-center"><?= $cp['cantidad'] ?></td>
              <td class="td-right">$<?= number_format($cp['precio_unitario'], 0, ',', '.') ?></td>
              <td class="td-peligro">$<?= number_format($cp['total_pagado'], 0, ',', '.') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <a href="../index.php" class="btn btn-contorno">Volver al menú</a>
  </main>
</div>

<footer class="footer"><p>Tienda App</p></footer>
</body>
</html>