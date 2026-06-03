DROP DATABASE IF EXISTS app_tienda;
CREATE DATABASE app_tienda CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
USE app_tienda;


-- TABLAS ------------------------

-- Categorías 
CREATE TABLE categorias (
    id_categoria  INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(50)   NOT NULL UNIQUE,
    descripcion   VARCHAR(150),
    impuesto      DECIMAL(5,2)  NOT NULL DEFAULT 0.00
    -- impuesto guarda el porcentaje: 7, 3, 5 ó 0
);

-- Tipos de empaque 
CREATE TABLE empaques (
    id_empaque  INT AUTO_INCREMENT PRIMARY KEY,
    tipo        VARCHAR(50) NOT NULL UNIQUE
);

-- Productos 
CREATE TABLE productos (
    id_producto         INT AUTO_INCREMENT PRIMARY KEY,
    codigo              VARCHAR(10)   NOT NULL UNIQUE,
    nombre              VARCHAR(100)  NOT NULL,
    peso                DECIMAL(8,2)  NOT NULL,          -- en gramos
    cantidad_almacenada INT           NOT NULL DEFAULT 0,
    id_categoria        INT           NOT NULL,
    id_empaque          INT           NOT NULL,
    precio_unitario     DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_prod_cat FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria),
    CONSTRAINT fk_prod_emp FOREIGN KEY (id_empaque)   REFERENCES empaques(id_empaque)
);

-- Clientes 
CREATE TABLE clientes (
    cedula    VARCHAR(15)  PRIMARY KEY,
    nombre    VARCHAR(60)  NOT NULL,
    apellido  VARCHAR(60)  NOT NULL,
    telefono  VARCHAR(15)  NOT NULL,
    correo    VARCHAR(100) NOT NULL
);

-- Proveedores
CREATE TABLE proveedores (
    id_proveedor  INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(100) NOT NULL,
    telefono      VARCHAR(15)  NOT NULL,
    ciudad        VARCHAR(80)  NOT NULL
);

-- Relación N:M proveedor <-> producto
CREATE TABLE proveedor_producto (
    id_proveedor  INT NOT NULL,
    id_producto   INT NOT NULL,
    PRIMARY KEY (id_proveedor, id_producto),
    CONSTRAINT fk_pp_prov FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    CONSTRAINT fk_pp_prod FOREIGN KEY (id_producto)  REFERENCES productos(id_producto)
);

-- Ventas — cabecera de cada factura 
CREATE TABLE ventas (
    id_venta        INT AUTO_INCREMENT PRIMARY KEY,
    cedula_cliente  VARCHAR(15)   NOT NULL,
    fecha           DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    subtotal        DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total_iva       DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total           DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT fk_venta_cli FOREIGN KEY (cedula_cliente) REFERENCES clientes(cedula)
);

-- Detalle de cada venta (líneas de la factura)
CREATE TABLE detalle_ventas (
    id_detalle      INT AUTO_INCREMENT PRIMARY KEY,
    id_venta        INT           NOT NULL,
    id_producto     INT           NOT NULL,
    cantidad        INT           NOT NULL,
    precio_unitario DECIMAL(12,2) NOT NULL,   -- precio al momento de vender
    impuesto_rate   DECIMAL(5,2)  NOT NULL,   -- % de IVA en ese momento (7, 3, 5 ó 0)
    subtotal_linea  DECIMAL(14,2) NOT NULL,   -- precio_unitario * cantidad
    iva_linea       DECIMAL(14,2) NOT NULL,   -- subtotal_linea * (impuesto_rate/100)
    total_linea     DECIMAL(14,2) NOT NULL,   -- subtotal_linea + iva_linea
    CONSTRAINT fk_det_venta FOREIGN KEY (id_venta)    REFERENCES ventas(id_venta),
    CONSTRAINT fk_det_prod  FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

-- Compras a proveedores (para calcular gastos)
CREATE TABLE compras_proveedor (
    id_compra       INT AUTO_INCREMENT PRIMARY KEY,
    id_proveedor    INT           NOT NULL,
    id_producto     INT           NOT NULL,
    cantidad        INT           NOT NULL,
    precio_unitario DECIMAL(12,2) NOT NULL,
    total_pagado    DECIMAL(14,2) NOT NULL,
    fecha           DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cp_prov FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    CONSTRAINT fk_cp_prod FOREIGN KEY (id_producto)  REFERENCES productos(id_producto)
);


-- DATOS INICIALES-------------------------------------

-- Categorías con IVA
INSERT INTO categorias (nombre, descripcion, impuesto) VALUES
('Papelería',    'Útiles escolares y de oficina',       7),
('Droguería',    'Medicamentos y productos de salud',   3),
('Supermercado', 'Alimentos y bebidas',                 0),
('Aseo',         'Productos de limpieza e higiene',     5);

-- Empaques
INSERT INTO empaques (tipo) VALUES
('Cartón'), ('Plástico'), ('Vidrio'), ('Metal'), ('Bolsa');

-- Productos: 5 por categoría (20 en total)
INSERT INTO productos (codigo, nombre, peso, cantidad_almacenada, id_categoria, id_empaque, precio_unitario) VALUES
-- Papelería (id_categoria=1)
('PAP001', 'Cuaderno universitario 100 hojas', 300,  25, 1, 1, 4500.00),
('PAP002', 'Resma papel carta 500 hojas',      2500, 15, 1, 2, 12000.00),
('PAP003', 'Bolígrafos negros x12',            100,  40, 1, 1, 8500.00),
('PAP004', 'Carpeta argollada carta',           400,  20, 1, 1, 6500.00),
('PAP005', 'Corrector líquido 20ml',            50,  30, 1, 2, 3200.00),
-- Droguería (id_categoria=2)
('DRO001', 'Acetaminofén 500mg x10',            50,  50, 2, 1, 2800.00),
('DRO002', 'Vendas elásticas 10cm',            100,  20, 2, 2, 5400.00),
('DRO003', 'Alcohol antiséptico 250ml',        250,  18, 2, 2, 4200.00),
('DRO004', 'Vitamina C 1000mg x20',             80,  35, 2, 1, 9800.00),
('DRO005', 'Ibuprofeno 400mg x8',               40,   3, 2, 1, 3500.00),
-- Supermercado (id_categoria=3)
('SUP001', 'Arroz 1kg Diana',                 1000,  60, 3, 5, 3800.00),
('SUP002', 'Aceite vegetal 1L',               1000,  24, 3, 2, 8900.00),
('SUP003', 'Leche entera 900ml',               900,  30, 3, 1, 4200.00),
('SUP004', 'Café molido 250g',                 250,  20, 3, 5, 7500.00),
('SUP005', 'Pasta alimenticia 500g',           500,   4, 3, 1, 3200.00),
-- Aseo (id_categoria=4)
('ASE001', 'Jabón de baño x3',                450,  22, 4, 1, 6000.00),
('ASE002', 'Detergente en polvo 1kg',        1000,  18, 4, 5, 8500.00),
('ASE003', 'Desengrasante multiusos 500ml',   500,   4, 4, 2, 5200.00),
('ASE004', 'Papel higiénico x12 rollos',      900,  35, 4, 2, 14000.00),
('ASE005', 'Limpiapisos lavanda 1L',         1000,  16, 4, 2, 7800.00);

-- Clientes (mínimo 10)
INSERT INTO clientes VALUES
('1098765001', 'Valentina', 'García',    '3101234501', 'valentina.garcia@email.com'),
('1098765002', 'Andrés',    'Martínez',  '3112345602', 'andres.martinez@email.com'),
('1098765003', 'Camila',    'Rodríguez', '3123456703', 'camila.rodriguez@email.com'),
('1098765004', 'Felipe',    'López',     '3134567804', 'felipe.lopez@email.com'),
('1098765005', 'Sofía',     'Hernández', '3145678905', 'sofia.hernandez@email.com'),
('1098765006', 'Daniel',    'Torres',    '3156789006', 'daniel.torres@email.com'),
('1098765007', 'Isabella',  'Vargas',    '3167890107', 'isabella.vargas@email.com'),
('1098765008', 'Sebastián', 'Morales',   '3178901208', 'sebastian.morales@email.com'),
('1098765009', 'Juliana',   'Díaz',      '3189012309', 'juliana.diaz@email.com'),
('1098765010', 'Mateo',     'Sánchez',   '3190123410', 'mateo.sanchez@email.com'),
('1098765011', 'Luciana',   'Pérez',     '3101234511', 'luciana.perez@email.com'),
('1098765012', 'Tomás',     'Gómez',     '3112345612', 'tomas.gomez@email.com');

-- Proveedores
INSERT INTO proveedores (nombre, telefono, ciudad) VALUES
('Distribuidora Palomino', '6074321001', 'Bogotá'),
('Suministros Andinos',    '6074321002', 'Medellín'),
('Proveexpress Ltda',      '6074321003', 'Cali'),
('Inversiones Boyacá',     '6074321004', 'Tunja'),
('Comercial Norte',        '6074321005', 'Bucaramanga');

-- Proveedor-Producto
INSERT INTO proveedor_producto VALUES
(1,1),(1,2),(1,3),
(2,6),(2,7),(2,9),
(3,11),(3,12),(3,13),
(4,16),(4,17),(4,19),
(5,4),(5,5),(5,8);


-- VENTAS DE EJEMPLO --------------------------------------------

-- Valentina García — compra 1
INSERT INTO ventas (cedula_cliente, subtotal, total_iva, total)
VALUES ('1098765001', 21500.00, 1935.00, 23435.00);
INSERT INTO detalle_ventas (id_venta,id_producto,cantidad,precio_unitario,impuesto_rate,subtotal_linea,iva_linea,total_linea) VALUES
(1, 1, 2, 4500.00, 7, 9000.00,  630.00,  9630.00),
(1,11, 3, 3800.00, 0,11400.00,    0.00, 11400.00),
(1,16, 1, 6000.00, 5, 6000.00,  300.00,  6300.00);
UPDATE productos SET cantidad_almacenada = cantidad_almacenada - 2 WHERE id_producto = 1;
UPDATE productos SET cantidad_almacenada = cantidad_almacenada - 3 WHERE id_producto = 11;
UPDATE productos SET cantidad_almacenada = cantidad_almacenada - 1 WHERE id_producto = 16;

-- Andrés Martínez — compra 1
INSERT INTO ventas (cedula_cliente, subtotal, total_iva, total)
VALUES ('1098765002', 12000.00, 840.00, 12840.00);
INSERT INTO detalle_ventas (id_venta,id_producto,cantidad,precio_unitario,impuesto_rate,subtotal_linea,iva_linea,total_linea) VALUES
(2, 2, 1, 12000.00, 7, 12000.00, 840.00, 12840.00);
UPDATE productos SET cantidad_almacenada = cantidad_almacenada - 1 WHERE id_producto = 2;

-- Camila Rodríguez — compra 1
INSERT INTO ventas (cedula_cliente, subtotal, total_iva, total)
VALUES ('1098765003', 19800.00, 1274.00, 21074.00);
INSERT INTO detalle_ventas (id_venta,id_producto,cantidad,precio_unitario,impuesto_rate,subtotal_linea,iva_linea,total_linea) VALUES
(3, 3, 2, 8500.00, 7, 17000.00, 1190.00, 18190.00),
(3, 6, 1, 2800.00, 3,  2800.00,   84.00,  2884.00);
UPDATE productos SET cantidad_almacenada = cantidad_almacenada - 2 WHERE id_producto = 3;
UPDATE productos SET cantidad_almacenada = cantidad_almacenada - 1 WHERE id_producto = 6;

-- Valentina García — compra 2 (para que sea la más frecuente)
INSERT INTO ventas (cedula_cliente, subtotal, total_iva, total)
VALUES ('1098765001', 14000.00, 700.00, 14700.00);
INSERT INTO detalle_ventas (id_venta,id_producto,cantidad,precio_unitario,impuesto_rate,subtotal_linea,iva_linea,total_linea) VALUES
(4, 19, 1, 14000.00, 5, 14000.00, 700.00, 14700.00);
UPDATE productos SET cantidad_almacenada = cantidad_almacenada - 1 WHERE id_producto = 19;

-- Valentina García — compra 3
INSERT INTO ventas (cedula_cliente, subtotal, total_iva, total)
VALUES ('1098765001', 7500.00, 0.00, 7500.00);
INSERT INTO detalle_ventas (id_venta,id_producto,cantidad,precio_unitario,impuesto_rate,subtotal_linea,iva_linea,total_linea) VALUES
(5, 14, 1, 7500.00, 0, 7500.00, 0.00, 7500.00);
UPDATE productos SET cantidad_almacenada = cantidad_almacenada - 1 WHERE id_producto = 14;

-- Felipe López — compra 1
INSERT INTO ventas (cedula_cliente, subtotal, total_iva, total)
VALUES ('1098765004', 16700.00, 835.00, 17535.00);
INSERT INTO detalle_ventas (id_venta,id_producto,cantidad,precio_unitario,impuesto_rate,subtotal_linea,iva_linea,total_linea) VALUES
(6, 17, 2, 8500.00, 5, 17000.00, 850.00, 17850.00);
UPDATE productos SET cantidad_almacenada = cantidad_almacenada - 2 WHERE id_producto = 17;

-- Compras a proveedores (gastos)
INSERT INTO compras_proveedor (id_proveedor, id_producto, cantidad, precio_unitario, total_pagado) VALUES
(1,  1, 50, 3500.00, 175000.00),
(2,  6, 30, 2200.00,  66000.00),
(3, 11, 40, 3000.00, 120000.00),
(4, 17, 20, 6500.00, 130000.00),
(5,  4, 25, 4800.00, 120000.00);


-- VISTAS ----------------------------------------------------

-- Vista: productos con stock bajo el mínimo (5)
CREATE VIEW v_stock_bajo AS
SELECT p.codigo, p.nombre, c.nombre AS categoria,
       p.cantidad_almacenada AS stock,
       5 AS stock_minimo,
       (5 - p.cantidad_almacenada) AS unidades_faltantes
FROM productos p
JOIN categorias c ON p.id_categoria = c.id_categoria
WHERE p.cantidad_almacenada < 5;

-- Vista: resumen de compras por cliente 
CREATE VIEW v_resumen_clientes AS
SELECT cl.cedula, cl.nombre, cl.apellido, cl.telefono, cl.correo,
       COUNT(v.id_venta)            AS num_compras,
       COALESCE(SUM(v.total), 0)    AS total_comprado
FROM clientes cl
LEFT JOIN ventas v ON cl.cedula = v.cedula_cliente
GROUP BY cl.cedula, cl.nombre, cl.apellido, cl.telefono, cl.correo;
