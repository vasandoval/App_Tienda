<?php
// ventas/factura.php
require_once '../conexion.php';

$id_venta = (int)($_GET['id'] ?? 0);
if (!$id_venta) { header('Location: historial_ventas.php'); exit; }

// Cabecera de la venta + datos del cliente
$stmt = $pdo->prepare(
    "SELECT v.*, cl.nombre, cl.apellido, cl.cedula, cl.telefono, cl.correo
     FROM ventas v
     JOIN clientes cl ON v.cedula_cliente = cl.cedula
     WHERE v.id_venta = ?"
);
$stmt->execute([$id_venta]);
$venta = $stmt->fetch();
if (!$venta) { header('Location: historial_ventas.php'); exit; }

// Líneas del detalle con nombre del producto y categoría
$stmt2 = $pdo->prepare(
    "SELECT dv.*,
            p.nombre  AS nombre_prod,
            c.nombre  AS categoria,
            c.impuesto
     FROM detalle_ventas dv
     JOIN productos  p ON dv.id_producto  = p.id_producto
     JOIN categorias c ON p.id_categoria  = c.id_categoria
     WHERE dv.id_venta = ?
     ORDER BY c.nombre, p.nombre"
);
$stmt2->execute([$id_venta]);
$lineas = $stmt2->fetchAll();

// Agrupar IVA por categoría para el desglose del pie de factura
$iva_por_cat = [];
foreach ($lineas as $l) {
    if ($l['iva_linea'] > 0) {
        $key = $l['categoria'] . ' (' . $l['impuesto'] . '%)';
        $iva_por_cat[$key] = ($iva_por_cat[$key] ?? 0) + $l['iva_linea'];
    }
}

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
  <title>Factura #<?= str_pad($id_venta, 5, '0', STR_PAD_LEFT) ?> — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-interior">

<?php include '../includes/navbar.php'; ?>

<div class="app-layout">
  <main class="content">

    <div class="content-header no-print">
      <div>
        <h1 class="content-title">Factura generada</h1>
        <p class="content-subtitle">Venta registrada correctamente</p>
      </div>
      <div>
        <button onclick="window.print()" class="btn btn-contorno">Imprimir</button>
        <a href="nueva_venta.php" class="btn">Nueva venta</a>
        <a href="historial_ventas.php" class="btn btn-contorno">Ver historial</a>
      </div>
    </div>

    <div class="card factura-wrap">
      <div class="card-body">

        <!-- ENCABEZADO -->
        <div class="factura-head">
          <div>
            <div class="factura-marca">Tienda App</div>
            <div >Tunja, Boyacá · Colombia</div>
          </div>
          <div class="factura-num">
            <small>FACTURA DE VENTA</small>
            <strong>#<?= str_pad($id_venta, 5, '0', STR_PAD_LEFT) ?></strong>
            <small><?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></small>
          </div>
        </div>

        <!-- DATOS DEL CLIENTE -->
        <div class="factura-cliente">
          <div>
            <p>Cliente</p>
            <strong><?= htmlspecialchars($venta['nombre'].' '.$venta['apellido']) ?></strong>
          </div>
          <div>
            <p>Cédula</p>
            <strong><?= htmlspecialchars($venta['cedula']) ?></strong>
          </div>
          <div>
            <p>Teléfono</p>
            <strong><?= htmlspecialchars($venta['telefono']) ?></strong>
          </div>
          <div>
            <p>Correo</p>
            <strong><?= htmlspecialchars($venta['correo']) ?></strong>
          </div>
        </div>

        <!-- TABLA DE PRODUCTOS -->
        <div class="tabla-wrapper">
          <table class="tabla">
            <thead>
              <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th >Precio unit.</th>
                <th >Cant.</th>
                <th >Subtotal</th>
                <th >IVA</th>
                <th >Total línea</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($lineas as $l): ?>
              <tr>
                <td><?= htmlspecialchars($l['nombre_prod']) ?></td>
                <td>
                  <span class="badge <?= $cat_badge[$l['categoria']] ?? 'badge-secundario' ?>">
                    <?= htmlspecialchars($l['categoria']) ?>
                  </span>
                </td>
                <td >
                  $<?= number_format($l['precio_unitario'], 2, ',', '.') ?>
                </td>
                <td ><?= $l['cantidad'] ?></td>
                <td >
                  $<?= number_format($l['subtotal_linea'], 2, ',', '.') ?>
                </td>
                <td >
                  <?= $l['impuesto_rate'] > 0 ? $l['impuesto_rate'].'%' : '—' ?>
                  <?php if ($l['iva_linea'] > 0): ?>
                    <br><small>$<?= number_format($l['iva_linea'], 2, ',', '.') ?></small>
                  <?php endif; ?>
                </td>
                <td >
                  $<?= number_format($l['total_linea'], 2, ',', '.') ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- TOTALES -->
        <div >
          <div class="tot-fila">
            <span >Subtotal (sin IVA)</span>
            <span>$<?= number_format($venta['subtotal'], 2, ',', '.') ?></span>
          </div>

          <?php foreach ($iva_por_cat as $label => $val): ?>
            <div class="tot-fila">
              <span >IVA <?= htmlspecialchars($label) ?></span>
              <span>$<?= number_format($val, 2, ',', '.') ?></span>
            </div>
          <?php endforeach; ?>

          <?php if (empty($iva_por_cat)): ?>
            <div class="tot-fila">
              <span>IVA</span>
              <span>No aplica</span>
            </div>
          <?php endif; ?>

          <div class="tot-fila tot-grand">
            <span>TOTAL A PAGAR</span>
            <span>$<?= number_format($venta['total'], 2, ',', '.') ?></span>
          </div>
        </div>

        <!-- PIE -->
        <div>
          Gracias por su compra · Tienda App · Tunja, Boyacá
        </div>

      </div>
    </div>

  </main>
</div>

<footer class="footer"><p>Tienda App</p></footer>
</body>
</html>
