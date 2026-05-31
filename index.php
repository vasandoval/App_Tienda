<?php
// Incluir conexión cuando esté lista
// require_once 'conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tienda App</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* Estilos extra solo para el index */
    .hero {
      background: linear-gradient(135deg, #6366F1 0%, #EC4899 100%);
      border-radius: var(--radius);
      padding: 2.5rem 2rem;
      color: white;
      margin-bottom: 2rem;
      position: relative;
      overflow: hidden;
    }
    .hero::before {
      content: '';
      position: absolute;
      top: -40px; right: -40px;
      width: 200px; height: 200px;
      border-radius: 50%;
      background: rgba(255,255,255,0.08);
    }
    .hero::after {
      content: '';
      position: absolute;
      bottom: -60px; left: 30%;
      width: 280px; height: 280px;
      border-radius: 50%;
      background: rgba(255,255,255,0.05);
    }
    .hero h1 { font-family: 'Nunito', sans-serif; font-size: 2.2rem; font-weight: 900; margin-bottom: 0.5rem; }
    .hero p  { opacity: 0.85; font-size: 1rem; }

    .category-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1.2rem;
      margin-bottom: 2rem;
    }
    .category-card {
      background: var(--white);
      border-radius: var(--radius);
      padding: 1.5rem;
      border: 1px solid var(--border);
      box-shadow: var(--shadow);
      transition: transform 0.2s, box-shadow 0.2s;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      gap: 0.8rem;
    }
    .category-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
    .category-card .cat-emoji { font-size: 2.5rem; }
    .category-card h3 { font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 1.15rem; }
    .category-card p  { font-size: 0.85rem; color: var(--text-muted); }
    .category-card.papeleria    { border-top: 4px solid var(--papeleria); }
    .category-card.drogueria    { border-top: 4px solid var(--drogueria); }
    .category-card.supermercado { border-top: 4px solid var(--supermercado); }
    .category-card.aseo         { border-top: 4px solid var(--aseo); }

    .quick-access {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 0.8rem;
    }
    .quick-btn {
      background: var(--white);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-sm);
      padding: 1rem;
      text-align: center;
      font-weight: 600;
      font-size: 0.88rem;
      color: var(--text);
      transition: all 0.2s;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.4rem;
    }
    .quick-btn:hover { border-color: var(--primary); color: var(--primary); background: #EEF2FF; transform: translateY(-2px); }
    .quick-btn .qb-icon { font-size: 1.5rem; }
  </style>
</head>
<body>

<?php
// Incluir navbar (ajustar ruta si se llama desde subcarpeta)
$base = '';
?>
<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <span class="brand-icon">🛒</span>
    <span>Tienda App</span>
  </a>
  <ul class="navbar-menu">
    <li class="nav-item-productos">
      <span class="nav-link">📦 Productos ▾</span>
      <div class="dropdown-menu">
        <a href="categorias/listar_categorias.php">📂 Ver Categorías</a>
        <a href="categorias/nueva_categoria.php">➕ Nueva Categoría</a>
        <a href="productos/listar_productos.php">📋 Ver Productos</a>
        <a href="productos/nuevo_producto.php">➕ Nuevo Producto</a>
      </div>
    </li>
    <li class="nav-item-proveedores">
      <span class="nav-link">🚚 Proveedores ▾</span>
      <div class="dropdown-menu">
        <a href="proveedores/listar_proveedores.php">📋 Ver Proveedores</a>
        <a href="proveedores/nuevo_proveedor.php">➕ Nuevo Proveedor</a>
      </div>
    </li>
    <li class="nav-item-clientes">
      <span class="nav-link">👤 Clientes ▾</span>
      <div class="dropdown-menu">
        <a href="clientes/listar_clientes.php">📋 Ver Clientes</a>
        <a href="clientes/nuevo_cliente.php">➕ Nuevo Cliente</a>
      </div>
    </li>
    <li class="nav-item-ventas">
      <span class="nav-link">💰 Ventas ▾</span>
      <div class="dropdown-menu">
        <a href="ventas/nueva_venta.php">🛍️ Nueva Venta</a>
        <a href="inventario/inventario.php">📊 Inventario</a>
      </div>
    </li>
    <li class="nav-item-reportes">
      <span class="nav-link">📈 Reportes ▾</span>
      <div class="dropdown-menu">
        <a href="reportes/reportes.php">📊 Ver Reportes</a>
        <a href="reportes/cliente_frecuente.php">⭐ Cliente Frecuente</a>
        <a href="reportes/ingresos_gastos.php">💵 Ingresos & Gastos</a>
      </div>
    </li>
  </ul>
</nav>

<div class="main-content">

  <!-- Hero -->
  <div class="hero">
    <h1>🛒 Bienvenido a Tienda App</h1>
    <p>Sistema de gestión de productos, clientes, proveedores y ventas</p>
  </div>

  <!-- Stats rápidos -->
  <div class="stat-cards">
    <div class="stat-card stat-supermercado">
      <div class="stat-icon">📦</div>
      <div>
        <div class="stat-value">—</div>
        <div class="stat-label">Productos registrados</div>
      </div>
    </div>
    <div class="stat-card stat-aseo">
      <div class="stat-icon">👤</div>
      <div>
        <div class="stat-value">—</div>
        <div class="stat-label">Clientes registrados</div>
      </div>
    </div>
    <div class="stat-card stat-drogueria">
      <div class="stat-icon">🚚</div>
      <div>
        <div class="stat-value">—</div>
        <div class="stat-label">Proveedores</div>
      </div>
    </div>
    <div class="stat-card stat-papeleria">
      <div class="stat-icon">💰</div>
      <div>
        <div class="stat-value">—</div>
        <div class="stat-label">Ventas hoy</div>
      </div>
    </div>
  </div>

  <!-- Categorías -->
  <h2 style="font-family:'Nunito',sans-serif; font-weight:800; margin-bottom:1rem;">
    Categorías de productos
  </h2>
  <div class="category-cards">
    <a href="productos/listar_productos.php?categoria=1" class="category-card papeleria">
      <div class="cat-emoji">✏️</div>
      <h3>Papelería</h3>
      <p>Cuadernos, bolígrafos, carpetas y más</p>
      <span class="badge badge-papeleria">IVA 7%</span>
    </a>
    <a href="productos/listar_productos.php?categoria=2" class="category-card drogueria">
      <div class="cat-emoji">💊</div>
      <h3>Droguería</h3>
      <p>Medicamentos, vitaminas y cuidado personal</p>
      <span class="badge badge-drogueria">IVA 3%</span>
    </a>
    <a href="productos/listar_productos.php?categoria=3" class="category-card supermercado">
      <div class="cat-emoji">🥫</div>
      <h3>Supermercado</h3>
      <p>Alimentos, bebidas y abarrotes</p>
      <span class="badge badge-supermercado">Sin IVA</span>
    </a>
    <a href="productos/listar_productos.php?categoria=4" class="category-card aseo">
      <div class="cat-emoji">🧴</div>
      <h3>Aseo</h3>
      <p>Productos de limpieza del hogar</p>
      <span class="badge badge-aseo">IVA 5%</span>
    </a>
  </div>

  <!-- Acceso rápido -->
  <h2 style="font-family:'Nunito',sans-serif; font-weight:800; margin-bottom:1rem;">
    Acceso rápido
  </h2>
  <div class="quick-access">
    <a href="productos/nuevo_producto.php" class="quick-btn">
      <span class="qb-icon">➕</span> Nuevo Producto
    </a>
    <a href="clientes/nuevo_cliente.php" class="quick-btn">
      <span class="qb-icon">👤</span> Nuevo Cliente
    </a>
    <a href="proveedores/nuevo_proveedor.php" class="quick-btn">
      <span class="qb-icon">🚚</span> Nuevo Proveedor
    </a>
    <a href="ventas/nueva_venta.php" class="quick-btn">
      <span class="qb-icon">🛍️</span> Nueva Venta
    </a>
    <a href="inventario/inventario.php" class="quick-btn">
      <span class="qb-icon">📊</span> Ver Inventario
    </a>
    <a href="reportes/reportes.php" class="quick-btn">
      <span class="qb-icon">📈</span> Reportes
    </a>
  </div>

</div>

<footer class="footer">
  Tienda App &copy; <?= date('Y') ?> — Sistema de Gestión
</footer>

</body>
</html>
