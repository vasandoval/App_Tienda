
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tienda App</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="pagina-inicio">

<header class="header-inicio">
  <h1>Tienda App</h1>
  <p class="subtitulo">Sistema de gestión de productos, clientes, proveedores y ventas</p>
</header>

<main>
  <div class="bienvenida">
    <h2>Bienvenido al panel de gestión</h2>
    <h4>¿Qué deseas administrar hoy?</h4>
  </div>

  <div class="grid-acciones">

    <a href="productos/lista_producto.php" class="tarjeta">
      <div class="icono">
      <img src="recursos\prod.png " >
      </div>
      <h3>Productos</h3>
      <p>Gestiona el inventario y catálogo de productos</p>
    </a>

    <a href="categorias/lista_categoria.php" class="tarjeta">
      <div class="icono">
        <img src="recursos\categor.png" >
      </div>
      <h3>Categorías</h3>
      <p>Administra las categorías y sus impuestos</p>
    </a>

    <a href="proveedores/lista_proveedor.php" class="tarjeta">
      <div class="icono">
        <img src="recursos\prov.png" >
      </div>
      <h3>Proveedores</h3>
      <p>Gestiona proveedores y sus productos asignados</p>
    </a>

    <a href="clientes/lista_cliente.php" class="tarjeta">
      <div class="icono">
        <img src="recursos\clien.png">
      </div>
      <h3>Clientes</h3>
      <p>Registra y consulta la base de clientes</p>
    </a>

    <a href="ventas/nueva_venta.php" class="tarjeta">
      <div class="icono">
        <img src="recursos/car.png">
      </div>
      <h3>Ventas</h3>
      <p>Registra ventas y genera facturas</p>
    </a>

    <a href="inventario/inventario.php" class="tarjeta">
      <div class="icono">
        <img src="recursos/inven.png">
      </div>
      <h3>Inventario</h3>
      <p>Control de stock y alertas de mínimos</p>
    </a>

    <a href="reportes/reportes.php" class="tarjeta">
      <div class="icono">
        <img src="recursos/repor.png">
      </div>
      <h3>Reportes</h3>
      <p>Ingresos, gastos y análisis de clientes</p>
    </a>

  </div>
</main>

<footer class="footer">
  <p>Tienda App </p>
</footer>

</body>
</html>

