<?php
require_once '../conexion.php';

$clientes = $pdo->query(
    "SELECT cedula, nombre, apellido FROM clientes ORDER BY apellido, nombre"
)->fetchAll();

$productos = $pdo->query(
    "SELECT p.id_producto, p.nombre, p.precio_unitario,
            p.cantidad_almacenada AS stock,
            c.nombre AS categoria, c.impuesto
     FROM productos p
     JOIN categorias c ON p.id_categoria = c.id_categoria
     WHERE p.cantidad_almacenada > 0
     ORDER BY c.nombre, p.nombre"
)->fetchAll();

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nueva Venta — Tienda App</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body class="pagina-interior">

<?php include '../includes/navbar.php'; ?>

<div class="app-layout">
  <main class="content">

    <div class="content-header">
      <div>
        <h1 class="content-title">Nueva Venta</h1>
        <p class="content-subtitle">Registrar una venta y generar factura</p>
      </div>
      <a href="historial_ventas.php" class="btn btn-contorno">Ver historial</a>
    </div>

    <?php if ($error === 'sin_stock'): ?>
      <p class="msg-error">Stock insuficiente para uno o más productos. Revisa las cantidades.</p>
    <?php elseif ($error === 'datos'): ?>
      <p class="msg-error">Completa todos los campos antes de procesar la venta.</p>
    <?php endif; ?>

    <div class="venta-layout">

      <div class="card">
        <div class="card-header"><h3>Datos de la venta</h3></div>
        <div class="card-body">
          <form method="POST" action="procesar_venta.php" id="formVenta">

            <div class="form-group mt-20">
              <label for="cedula_cliente">Cliente <span class="requerido">*</span></label>
              <select name="cedula_cliente" id="cedula_cliente" required>
                <option value="">— Seleccionar cliente —</option>
                <?php foreach ($clientes as $c): ?>
                  <option value="<?= $c['cedula'] ?>">
                    <?= htmlspecialchars($c['nombre'].' '.$c['apellido']) ?> (<?= $c['cedula'] ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div id="items-container" class="mt-20"></div>

            <button type="button" class="btn btn-contorno btn-sm mt-20" onclick="agregarFila()">
              + Agregar producto
            </button>

            <div class="form-acciones">
              <button type="submit" class="btn btn-lg">
                Procesar venta y generar factura
              </button>
            </div>

          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>Resumen de la venta</h3></div>
        <div class="card-body" id="resumen-body">
          <div class="estado-vacio">
            <p>Agrega productos para ver el resumen</p>
          </div>
        </div>
      </div>

    </div>

  </main>
</div>

<footer class="footer"><p>Tienda App</p></footer>

<script>
const PRODS = <?= json_encode(array_values($productos)) ?>;
const fmt = n => '$' + Number(n).toLocaleString('es-CO', {minimumFractionDigits:0, maximumFractionDigits:0});

function agregarFila() {
    const container = document.getElementById('items-container');
    const idx = container.children.length;
    const div = document.createElement('div');
    div.className = 'item-fila';

    const opciones = PRODS.map(p =>
        `<option value="${p.id_producto}"
                 data-precio="${p.precio_unitario}"
                 data-stock="${p.stock}"
                 data-imp="${p.impuesto}">
            ${p.nombre} (${p.categoria}) — ${fmt(p.precio_unitario)}
         </option>`
    ).join('');

    div.innerHTML = `
        <select name="items[${idx}][id_producto]" onchange="calcularResumen()">
            <option value="">— Producto —</option>
            ${opciones}
        </select>
        <input type="number" name="items[${idx}][cantidad]"
               min="1" value="1" placeholder="Cant."
               oninput="calcularResumen()">
        <button type="button" class="btn btn-peligro btn-sm"
                onclick="this.closest('.item-fila').remove(); calcularResumen();">✕</button>`;

    container.appendChild(div);
    calcularResumen();
}

function calcularResumen() {
    const filas = document.querySelectorAll('.item-fila');
    let html = '', subtotal = 0, totalIva = 0, hayItems = false;

    filas.forEach(fila => {
        const sel  = fila.querySelector('select');
        const inp  = fila.querySelector('input[type=number]');
        if (!sel.value || !inp.value) return;
        const opt    = sel.options[sel.selectedIndex];
        const precio = parseFloat(opt.dataset.precio);
        const imp    = parseFloat(opt.dataset.imp);
        const stock  = parseInt(opt.dataset.stock);
        const cant   = parseInt(inp.value);
        if (isNaN(cant) || cant <= 0) return;
        if (cant > stock) { inp.classList.add('input-error'); return; }
        inp.classList.remove('input-error');
        const sub = precio * cant;
        const iva = sub * (imp / 100);
        subtotal += sub; totalIva += iva; hayItems = true;
        html += `<div class="resumen-linea"><span>${opt.text.split('—')[0].trim()} ×${cant}</span><span>${fmt(sub+iva)}</span></div>`;
    });

    const body = document.getElementById('resumen-body');
    if (!hayItems) {
        body.innerHTML = '<div class="estado-vacio"><p>Agrega productos para ver el resumen</p></div>';
        return;
    }
    body.innerHTML = html
        + '<hr class="separador">'
        + `<div class="resumen-linea"><span>Subtotal</span><span>${fmt(subtotal)}</span></div>`
        + `<div class="resumen-linea"><span>IVA</span><span>${fmt(totalIva)}</span></div>`
        + `<div class="resumen-total"><span>Total</span><span>${fmt(subtotal+totalIva)}</span></div>`;
}

agregarFila();
</script>

</body>
</html>
