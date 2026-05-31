<?php
// Detectar página activa
$pagina_actual = basename($_SERVER['PHP_SELF']);
$carpeta_actual = basename(dirname($_SERVER['PHP_SELF']));
?>
<nav class="navbar">
  <a href="../index.php" class="navbar-brand">
    <span class="brand-icon">🛒</span>
    <span>Tienda App</span>
  </a>

  <ul class="navbar-menu">

    <!-- Productos & Categorías -->
    <li class="nav-item-productos">
      <span class="nav-link">
        📦 Productos ▾
      </span>
      <div class="dropdown-menu">
        <a href="../categorias/listar_categorias.php">📂 Ver Categorías</a>
        <a href="../categorias/nueva_categoria.php">➕ Nueva Categoría</a>
        <a href="../productos/listar_productos.php">📋 Ver Productos</a>
        <a href="../productos/nuevo_producto.php">➕ Nuevo Producto</a>
      </div>
    </li>

    <!-- Proveedores -->
    <li class="nav-item-proveedores">
      <span class="nav-link">
        🚚 Proveedores ▾
      </span>
      <div class="dropdown-menu">
        <a href="../proveedores/listar_proveedores.php">📋 Ver Proveedores</a>
        <a href="../proveedores/nuevo_proveedor.php">➕ Nuevo Proveedor</a>
      </div>
    </li>

    <!-- Clientes -->
    <li class="nav-item-clientes">
      <span class="nav-link">
        👤 Clientes ▾
      </span>
      <div class="dropdown-menu">
        <a href="../clientes/listar_clientes.php">📋 Ver Clientes</a>
        <a href="../clientes/nuevo_cliente.php">➕ Nuevo Cliente</a>
      </div>
    </li>

    <!-- Ventas (compañera) -->
    <li class="nav-item-ventas">
      <span class="nav-link">
        💰 Ventas ▾
      </span>
      <div class="dropdown-menu">
        <a href="../ventas/nueva_venta.php">🛍️ Nueva Venta</a>
        <a href="../inventario/inventario.php">📊 Inventario</a>
      </div>
    </li>

    <!-- Reportes (compañera) -->
    <li class="nav-item-reportes">
      <span class="nav-link">
        📈 Reportes ▾
      </span>
      <div class="dropdown-menu">
        <a href="../reportes/reportes.php">📊 Ver Reportes</a>
        <a href="../reportes/cliente_frecuente.php">⭐ Cliente Frecuente</a>
        <a href="../reportes/ingresos_gastos.php">💵 Ingresos & Gastos</a>
      </div>
    </li>

  </ul>
</nav>
