<?php
require_once '../conexion.php';

$busqueda = trim($_GET['buscar'] ?? '');

$sql = "SELECT cl.*,
               COUNT(DISTINCT v.id_venta) AS total_compras,
               COALESCE(SUM(v.total), 0) AS total_gastado
        FROM clientes cl
        LEFT JOIN ventas v ON v.cedula_cliente = cl.cedula
        WHERE 1=1";
$params = [];

if (!empty($busqueda)) {
    $sql .= " AND (cl.nombre LIKE ? OR cl.apellido LIKE ? OR cl.cedula LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}
$sql .= " GROUP BY cl.cedula ORDER BY cl.apellido, cl.nombre";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();
$total_clientes = count($clientes);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Clientes — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-interior">

<?php include '../includes/navbar.php'; ?>

<div class="app-layout">
  <main class="content">

    <div class="content-header">
      <div>
        <h1 class="content-title">Clientes</h1>
        <p class="content-subtitle"><?= $total_clientes ?> cliente(s) registrado(s)</p>
      </div>
      <a href="nuevo_cliente.php" class="btn">Nuevo Cliente</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <p class="msg-exito"><?= htmlspecialchars($_GET['msg']) ?></p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
      <p class="msg-error"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <form method="GET" class="barra-busqueda">
      <div class="input-busqueda">
        <input type="text" name="buscar" placeholder="Buscar por nombre, apellido o cédula..."
               value="<?= htmlspecialchars($busqueda) ?>">
      </div>
      <button type="submit" class="btn">Buscar</button>
      <a href="lista_cliente.php" class="btn btn-contorno">Limpiar</a>
    </form>

    <?php if (empty($clientes)): ?>
      <div class="card">
        <div class="card-body">
          <div class="estado-vacio">
            <span class="icono-vacio">—</span>
            <p>No hay clientes registrados.</p>
            <a href="nuevo_cliente.php" class="btn">Agregar cliente</a>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="card">
        <div class="tabla-wrapper">
          <table class="tabla">
            <thead>
              <tr>
                <th>Cédula</th>
                <th>Nombre completo</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Compras</th>
                <th>Total gastado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($clientes as $cl): ?>
              <tr>
                <td><span class="codigo-inline"><?= htmlspecialchars($cl['cedula']) ?></span></td>
                <td><strong><?= htmlspecialchars($cl['nombre'] . ' ' . $cl['apellido']) ?></strong></td>
                <td><?= htmlspecialchars($cl['telefono']) ?></td>
                <td><?= htmlspecialchars($cl['correo']) ?></td>
                <td>
                  <?php if ($cl['total_compras'] == 1): ?>
                    <span class="badge badge-secundario">1 compra</span>
                  <?php elseif ($cl['total_compras'] > 1): ?>
                    <span class="badge badge-exito"><?= $cl['total_compras'] ?> compras</span>
                  <?php else: ?>
                    <span class="badge badge-secundario">Sin compras</span>
                  <?php endif; ?>
                </td>
                <td><strong>$<?= number_format($cl['total_gastado'], 2, ',', '.') ?></strong></td>
                <td>
                  <div class="td-acciones">
                    <a href="editar_cliente.php?cedula=<?= urlencode($cl['cedula']) ?>"
                       class="btn btn-sm btn-advertencia">Editar</a>
                    <a href="eliminar_cliente.php?cedula=<?= urlencode($cl['cedula']) ?>"
                       class="btn btn-sm btn-peligro"
                       onclick="return confirm('¿Eliminar este cliente?')">Eliminar</a>
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
