<?php
// ventas/historial_ventas.php
require_once '../conexion.php';

$ventas = $pdo->query(
    "SELECT v.id_venta, v.fecha,
            cl.nombre, cl.apellido, cl.cedula,
            v.subtotal, v.total_iva, v.total,
            COUNT(dv.id_detalle) AS num_productos
     FROM ventas v
     JOIN clientes cl       ON v.cedula_cliente = cl.cedula
     JOIN detalle_ventas dv ON dv.id_venta      = v.id_venta
     GROUP BY v.id_venta
     ORDER BY v.fecha DESC"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial de Ventas — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-interior">

<?php include '../includes/navbar.php'; ?>

<div class="app-layout">
  <main class="content">

    <div class="content-header">
      <div>
        <h1 class="content-title">Historial de Ventas</h1>
        <p class="content-subtitle"><?= count($ventas) ?> venta(s) registrada(s)</p>
      </div>
      <a href="nueva_venta.php" class="btn">+ Nueva Venta</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <p class="msg-exito"><?= htmlspecialchars($_GET['msg']) ?></p>
    <?php endif; ?>

    <?php if (empty($ventas)): ?>
      <div class="card">
        <div class="card-body">
          <div class="estado-vacio">
            <span class="icono-vacio">—</span>
            <p>No hay ventas registradas aún.</p>
            <a href="nueva_venta.php" class="btn">Registrar primera venta</a>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="card">
        <div class="tabla-wrapper">
          <table class="tabla">
            <thead>
              <tr>
                <th>Factura</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Cédula</th>
                <th>Productos</th>
                <th>Subtotal</th>
                <th class="th-right">IVA</th>
                <th>Total</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($ventas as $v): ?>
              <tr>
                <td>
                  <span class="codigo-inline">
                    #<?= str_pad($v['id_venta'], 5, '0', STR_PAD_LEFT) ?>
                  </span>
                </td>
                <td><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
                <td><strong><?= htmlspecialchars($v['nombre'].' '.$v['apellido']) ?></strong></td>
                <td><?= htmlspecialchars($v['cedula']) ?></td>
                <td>
                  <span class="badge badge-secundario"><?= $v['num_productos'] ?> ítem(s)</span>
                </td>
                <td>
                  $<?= number_format($v['subtotal'], 2, ',', '.') ?>
                </td>
                <td>
                  $<?= number_format($v['total_iva'], 2, ',', '.') ?>
                </td>
                <td>
                  $<?= number_format($v['total'], 2, ',', '.') ?>
                </td>
                <td>
                  <a href="factura.php?id=<?= $v['id_venta'] ?>"
                     class="btn btn-sm btn-contorno">
                    Ver factura
                  </a>
                </td>
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
