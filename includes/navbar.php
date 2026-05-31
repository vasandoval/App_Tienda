<?php
$carpeta = basename(dirname($_SERVER['PHP_SELF']));
$base = ($carpeta !== 'App_Tienda-main' && $carpeta !== '.') ? '../' : '';
?>
<div class="menu">
  <header>
    <a href="<?= $base ?>index.php" class="marca">Tienda App</a>
    <nav>

      <div class="nav-item">
        <span class="nav-link">Productos</span>
        <div class="dropdown">
          <a href="<?= $base ?>categorias/lista_categoria.php">Ver categorías</a>
          <a href="<?= $base ?>categorias/nueva_categoria.php">Nueva categoría</a>
          <a href="<?= $base ?>productos/lista_producto.php">Ver productos</a>
          <a href="<?= $base ?>productos/nuevo_producto.php">Nuevo producto</a>
        </div>
      </div>

      <div class="nav-item">
        <span class="nav-link">Proveedores</span>
        <div class="dropdown">
          <a href="<?= $base ?>proveedores/lista_proveedor.php">Ver proveedores</a>
          <a href="<?= $base ?>proveedores/nuevo_proveedor.php">Nuevo proveedor</a>
        </div>
      </div>

      <div class="nav-item">
        <span class="nav-link">Clientes</span>
        <div class="dropdown">
          <a href="<?= $base ?>clientes/lista_cliente.php">Ver clientes</a>
          <a href="<?= $base ?>clientes/nuevo_cliente.php">Nuevo cliente</a>
        </div>
      </div>

    </nav>
  </header>
</div>
