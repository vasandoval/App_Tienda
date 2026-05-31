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
<body>

<?php include '../includes/navbar.php'; ?>

<div class="main-content">

  <div class="page-header">
    <div>
      <h1 class="page-title">
        <span class="title-icon" style="background:var(--aseo-light);">👤</span>
        Clientes
      </h1>
      <p class="page-subtitle"><?= $total_clientes ?> cliente(s) registrado(s)</p>
    </div>
    <a href="nuevo_cliente.php" class="btn btn-primary" style="background:var(--aseo);">
      ➕ Nuevo Cliente
    </a>
  </div>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">❌ <?= htmlspecialchars($_GET['error']) ?></div>
  <?php endif; ?>

  <!-- Buscador -->
  <form method="GET" class="search-bar">
    <div class="search-input">
      <span class="search-icon">🔍</span>
      <input type="text" name="buscar" placeholder="Buscar por nombre, apellido o cédula..."
             value="<?= htmlspecialchars($busqueda) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Buscar</button>
    <a href="listar_clientes.php" class="btn btn-outline">Limpiar</a>
  </form>

  <?php if (empty($clientes)): ?>
    <div class="card">
      <div class="card-body">
        <div class="empty-state">
          <div class="empty-icon">👤</div>
          <p>No hay clientes registrados.</p>
          <a href="nuevo_cliente.php" class="btn btn-primary">Agregar cliente</a>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="card">
      <div class="table-wrapper">
        <table>
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
              <td>
                <code style="background:var(--aseo-light);color:var(--aseo-dark);padding:0.2rem 0.5rem;border-radius:4px;font-size:0.82rem;">
                  <?= htmlspecialchars($cl['cedula']) ?>
                </code>
              </td>
              <td>
                <strong><?= htmlspecialchars($cl['nombre'] . ' ' . $cl['apellido']) ?></strong>
              </td>
              <td>📞 <?= htmlspecialchars($cl['telefono']) ?></td>
              <td>✉️ <?= htmlspecialchars($cl['correo']) ?></td>
              <td>
                <?php if ($cl['total_compras'] == 1): ?>
                  <span class="badge badge-secondary">1 compra</span>
                <?php elseif ($cl['total_compras'] > 1): ?>
                  <span class="badge badge-success">⭐ <?= $cl['total_compras'] ?> compras</span>
                <?php else: ?>
                  <span class="badge badge-secondary">Sin compras</span>
                <?php endif; ?>
              </td>
              <td>
                <strong style="color:var(--drogueria-dark);">
                  $<?= number_format($cl['total_gastado'], 2, ',', '.') ?>
                </strong>
              </td>
              <td>
                <div class="actions">
                  <a href="editar_cliente.php?cedula=<?= urlencode($cl['cedula']) ?>"
                     class="btn btn-sm btn-warning">✏️ Editar</a>
                  <a href="eliminar_cliente.php?cedula=<?= urlencode($cl['cedula']) ?>"
                     class="btn btn-sm btn-danger"
                     onclick="return confirm('¿Eliminar este cliente?')">🗑️</a>
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
