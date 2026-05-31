<?php
require_once '../conexion.php';

$busqueda = trim($_GET['buscar'] ?? '');

$sql = "SELECT p.*, GROUP_CONCAT(DISTINCT pr.nombre ORDER BY pr.nombre SEPARATOR ', ') AS productos_suministra,
               COUNT(DISTINCT pp.id_producto) AS total_productos
        FROM proveedores p
        LEFT JOIN proveedor_producto pp ON pp.id_proveedor = p.id_proveedor
        LEFT JOIN productos pr ON pr.id_producto = pp.id_producto
        WHERE 1=1";
$params = [];

if (!empty($busqueda)) {
    $sql .= " AND (p.nombre LIKE ? OR p.ciudad LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}
$sql .= " GROUP BY p.id_proveedor ORDER BY p.nombre";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$proveedores = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Proveedores — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-interior">

<?php include '../includes/navbar.php'; ?>

<div class="app-layout">
  <main class="content">

    <div class="content-header">
      <div>
        <h1 class="content-title">Proveedores</h1>
        <p class="content-subtitle"><?= count($proveedores) ?> proveedor(es) registrado(s)</p>
      </div>
      <a href="nuevo_proveedor.php" class="btn">Nuevo Proveedor</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <p class="msg-exito"><?= htmlspecialchars($_GET['msg']) ?></p>
    <?php endif; ?>

    <form method="GET" class="barra-busqueda">
      <div class="input-busqueda">
        <input type="text" name="buscar" placeholder="Buscar por nombre o ciudad..."
               value="<?= htmlspecialchars($busqueda) ?>">
      </div>
      <button type="submit" class="btn">Buscar</button>
      <a href="lista_proveedor.php" class="btn btn-contorno">Limpiar</a>
    </form>

    <?php if (empty($proveedores)): ?>
      <div class="card">
        <div class="card-body">
          <div class="estado-vacio">
            <span class="icono-vacio">—</span>
            <p>No hay proveedores registrados.</p>
            <a href="nuevo_proveedor.php" class="btn">Agregar proveedor</a>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="card">
        <div class="tabla-wrapper">
          <table class="tabla">
            <thead>
              <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Ciudad</th>
                <th>Productos que suministra</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($proveedores as $p): ?>
              <tr>
                <td><?= $p['id_proveedor'] ?></td>
                <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                <td><?= htmlspecialchars($p['telefono']) ?></td>
                <td><?= htmlspecialchars($p['ciudad']) ?></td>
                <td>
                  <?php if ($p['total_productos'] > 0): ?>
                    <span class="badge badge-drogueria"><?= $p['total_productos'] ?> producto(s)</span><br>
                    <small><?= htmlspecialchars($p['productos_suministra'] ?? '') ?></small>
                  <?php else: ?>
                    <span class="badge badge-secundario">Sin asignar</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="td-acciones">
                    <a href="editar_proveedor.php?id=<?= $p['id_proveedor'] ?>"
                       class="btn btn-sm btn-advertencia">Editar</a>
                    <a href="eliminar_proveedor.php?id=<?= $p['id_proveedor'] ?>"
                       class="btn btn-sm btn-peligro"
                       onclick="return confirm('¿Eliminar este proveedor?')">Eliminar</a>
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
  <p>Tienda App </p>
</footer>
</body>
</html>
