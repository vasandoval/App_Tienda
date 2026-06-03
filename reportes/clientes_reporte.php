<?php
require_once '../conexion.php';

$todos = $pdo->query("SELECT * FROM v_resumen_clientes ORDER BY num_compras DESC")->fetchAll();

$frecuente   = $pdo->query("SELECT * FROM v_resumen_clientes ORDER BY num_compras DESC LIMIT 1")->fetch();
$una_vez     = $pdo->query("SELECT * FROM v_resumen_clientes WHERE num_compras = 1")->fetchAll();
$mas_compras = array_filter($todos, fn($c) => $c['num_compras'] > 0);
$sin_compras = array_filter($todos, fn($c) => $c['num_compras'] == 0);
$por_valor   = array_filter($todos, fn($c) => $c['num_compras'] > 0);
usort($por_valor, fn($a,$b) => $b['total_comprado'] <=> $a['total_comprado']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Análisis de Clientes — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-interior">

<?php include '../includes/navbar.php'; ?>

<div class="app-layout">
  <main class="content">

    <div class="content-header">
      <div>
        <h1 class="content-title">Análisis de Clientes</h1>
        <p class="content-subtitle">Frecuencia, valor y comportamiento de compra</p>
      </div>
      <a href="reportes.php" class="btn btn-contorno">← Reportes</a>
    </div>

    <?php if ($frecuente && $frecuente['num_compras'] > 0): ?>
    <div class="card card-destacado">
      <div class="card-body destacado-body">
        <div class="destacado-icono">⭐</div>
        <div class="destacado-info">
          <div class="destacado-etiqueta">Cliente más frecuente</div>
          <div class="destacado-nombre"><?= htmlspecialchars($frecuente['nombre'].' '.$frecuente['apellido']) ?></div>
          <div class="destacado-cedula">Cédula: <?= $frecuente['cedula'] ?></div>
        </div>
        <div class="destacado-stats">
          <div>
            <div class="destacado-stat-label">Compras</div>
            <div class="destacado-stat-val"><?= $frecuente['num_compras'] ?></div>
          </div>
          <div>
            <div class="destacado-stat-label">Total gastado</div>
            <div class="destacado-stat-val-verde">$<?= number_format($frecuente['total_comprado'], 0, ',', '.') ?></div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="dos-col">

      <div class="card">
        <div class="card-header"><h3>Mayor cantidad de compras</h3></div>
        <div class="card-body">
          <?php if ($mas_compras): ?>
            <?php foreach (array_values($mas_compras) as $i => $c): ?>
              <div class="rank-fila">
                <div class="rank-num"><?= $i+1 ?></div>
                <div class="rank-info">
                  <div class="rank-nombre"><?= htmlspecialchars($c['nombre'].' '.$c['apellido']) ?></div>
                  <div class="rank-sub">Cédula: <?= $c['cedula'] ?></div>
                </div>
                <div>
                  <div class="rank-val"><?= $c['num_compras'] ?> compras</div>
                  <div class="rank-sub">$<?= number_format($c['total_comprado'], 0, ',', '.') ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="estado-vacio"><p>Sin ventas registradas</p></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>Mayor valor total comprado</h3></div>
        <div class="card-body">
          <?php if ($por_valor): ?>
            <?php foreach (array_values($por_valor) as $i => $c): ?>
              <div class="rank-fila">
                <div class="rank-num"><?= $i+1 ?></div>
                <div class="rank-info">
                  <div class="rank-nombre"><?= htmlspecialchars($c['nombre'].' '.$c['apellido']) ?></div>
                  <div class="rank-sub"><?= $c['num_compras'] ?> compra(s)</div>
                </div>
                <div class="rank-val">$<?= number_format($c['total_comprado'], 0, ',', '.') ?></div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="estado-vacio"><p>Sin ventas</p></div>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <div class="card">
      <div class="card-header">
        <h3>Clientes que solo han comprado una vez (<?= count($una_vez) ?>)</h3>
      </div>
      <div class="card-body">
        <?php if ($una_vez): ?>
          <div class="tabla-wrapper">
            <table class="tabla">
              <thead>
                <tr>
                  <th>Nombre</th><th>Apellido</th>
                  <th>Cédula</th><th>Teléfono</th>
                  <th class="th-right">Total comprado</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($una_vez as $c): ?>
                <tr>
                  <td><?= htmlspecialchars($c['nombre']) ?></td>
                  <td><?= htmlspecialchars($c['apellido']) ?></td>
                  <td><span class="codigo-inline"><?= $c['cedula'] ?></span></td>
                  <td><?= htmlspecialchars($c['telefono']) ?></td>
                  <td class="td-verde">$<?= number_format($c['total_comprado'], 2, ',', '.') ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="alerta-info">Ningún cliente ha comprado solo una vez.</p>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($sin_compras): ?>
    <div class="card">
      <div class="card-header"><h3>Clientes sin compras (<?= count($sin_compras) ?>)</h3></div>
      <div class="card-body sin-compras-wrap">
        <?php foreach ($sin_compras as $c): ?>
          <span class="badge badge-secundario"><?= htmlspecialchars($c['nombre'].' '.$c['apellido']) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <a href="../index.php" class="btn btn-contorno">Volver al menú</a>
  </main>
</div>

<footer class="footer"><p>Tienda App</p></footer>
</body>
</html>
