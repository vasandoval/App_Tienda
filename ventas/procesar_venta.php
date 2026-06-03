<?php
// ventas/procesar_venta.php
// Este archivo recibe el POST de nueva_venta.php,
// guarda la venta en la BD y redirige a la factura.
require_once '../conexion.php';

// Solo acepta POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: nueva_venta.php');
    exit;
}

$cedula = trim($_POST['cedula_cliente'] ?? '');
$items  = $_POST['items'] ?? [];

// Filtrar filas vacías o con cantidad 0
$items = array_filter($items, fn($i) =>
    !empty($i['id_producto']) && (int)($i['cantidad'] ?? 0) > 0
);

// Validaciones básicas
if (empty($cedula) || empty($items)) {
    header('Location: nueva_venta.php?error=datos');
    exit;
}

try {
    // Iniciamos transacción: si algo falla, no queda nada a medias
    $pdo->beginTransaction();

    $subtotal_total = 0;
    $iva_total      = 0;
    $detalles       = [];   // aquí guardamos las líneas calculadas

    foreach ($items as $item) {
        $id_prod = (int)$item['id_producto'];
        $cant    = (int)$item['cantidad'];

        // Obtenemos el producto con su impuesto (bloqueamos la fila para evitar
        // problemas si dos personas compran al mismo tiempo)
        $stmt = $pdo->prepare(
            "SELECT p.id_producto, p.nombre, p.precio_unitario,
                    p.cantidad_almacenada AS stock, c.impuesto
             FROM productos p
             JOIN categorias c ON p.id_categoria = c.id_categoria
             WHERE p.id_producto = ?
             FOR UPDATE"
        );
        $stmt->execute([$id_prod]);
        $prod = $stmt->fetch();

        // Verificar que existe y tiene suficiente stock
        if (!$prod || $prod['stock'] < $cant) {
            $pdo->rollBack();
            header('Location: nueva_venta.php?error=sin_stock');
            exit;
        }

        // Cálculo de la línea
        // impuesto en BD es porcentaje (7, 3, 5, 0)
        $sub_linea = round($prod['precio_unitario'] * $cant, 2);
        $iva_linea = round($sub_linea * ($prod['impuesto'] / 100), 2);
        $tot_linea = $sub_linea + $iva_linea;

        $subtotal_total += $sub_linea;
        $iva_total      += $iva_linea;

        $detalles[] = [
            'id_producto'    => $id_prod,
            'cantidad'       => $cant,
            'precio_unitario'=> $prod['precio_unitario'],
            'impuesto_rate'  => $prod['impuesto'],
            'subtotal_linea' => $sub_linea,
            'iva_linea'      => $iva_linea,
            'total_linea'    => $tot_linea,
        ];

        // Descontamos el stock del producto
        $pdo->prepare(
            "UPDATE productos SET cantidad_almacenada = cantidad_almacenada - ?
             WHERE id_producto = ?"
        )->execute([$cant, $id_prod]);
    }

    $total_venta = $subtotal_total + $iva_total;

    // Insertamos la cabecera de la venta
    $pdo->prepare(
        "INSERT INTO ventas (cedula_cliente, subtotal, total_iva, total)
         VALUES (?, ?, ?, ?)"
    )->execute([$cedula, $subtotal_total, $iva_total, $total_venta]);

    $id_venta = $pdo->lastInsertId();  // ID de la venta recién creada

    // Insertamos cada línea de detalle
    $stmtDet = $pdo->prepare(
        "INSERT INTO detalle_ventas
             (id_venta, id_producto, cantidad, precio_unitario,
              impuesto_rate, subtotal_linea, iva_linea, total_linea)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    foreach ($detalles as $d) {
        $stmtDet->execute([
            $id_venta,
            $d['id_producto'],
            $d['cantidad'],
            $d['precio_unitario'],
            $d['impuesto_rate'],
            $d['subtotal_linea'],
            $d['iva_linea'],
            $d['total_linea'],
        ]);
    }

    // Todo salió bien: confirmamos la transacción
    $pdo->commit();

    // Redirigimos a la factura
    header("Location: factura.php?id=$id_venta");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    // En producción loguearías el error; aquí lo mostramos para depurar
    die('Error al procesar la venta: ' . htmlspecialchars($e->getMessage()) . '</p>');
}
