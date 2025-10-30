-- FARMASIS-RDS: Consultas SQL útiles
-- Ajusta placeholders (?) o reemplázalos por valores concretos según necesidad.

-- ==========================
-- Listados básicos
-- ==========================
SELECT * FROM categories ORDER BY name;
SELECT * FROM products ORDER BY name;
SELECT * FROM suppliers ORDER BY fullname;
SELECT * FROM clients ORDER BY fullname;
SELECT * FROM sales ORDER BY created_at DESC;
SELECT * FROM details ORDER BY created_at DESC;
SELECT * FROM purchases ORDER BY date DESC;

-- ==========================
-- Búsquedas y filtros
-- ==========================
-- Productos por categoría
SELECT p.* FROM products p WHERE p.category_id = ?;

-- Productos con poco stock (umbral)
SELECT p.* FROM products p WHERE p.quantity <= ? ORDER BY p.quantity ASC;

-- Productos por nombre o código (búsqueda)
SELECT p.* FROM products p WHERE p.name LIKE CONCAT('%', ?, '%') OR p.code LIKE CONCAT('%', ?, '%');

-- Ventas por tipo de comprobante
SELECT * FROM sales WHERE type = 'FACTURA';

-- Ventas por rango de fechas (created_at)
SELECT * FROM sales WHERE created_at BETWEEN ? AND ? ORDER BY created_at;

-- Compras por proveedor
SELECT pu.* FROM purchases pu WHERE pu.supplier_id = ? ORDER BY pu.date DESC;

-- Clientes por tipo
SELECT * FROM clients WHERE type = 'NATURAL';

-- ==========================
-- Joins comunes
-- ==========================
-- Productos con su categoría
SELECT p.id, p.code, p.name, p.quantity, p.price, c.name AS category
FROM products p
JOIN categories c ON c.id = p.category_id;

-- Detalles de venta con producto y cabecera
SELECT d.id, s.number AS sale_number, s.type, d.product_id, p.name AS product_name,
       d.quantity, d.price, d.amount, s.created_at
FROM details d
JOIN sales s ON s.id = d.sale_id
JOIN products p ON p.id = d.product_id
ORDER BY s.created_at DESC;

-- Compras con proveedor
SELECT pu.id, pu.tcomporbante, pu.numberc, pu.date, s.fullname AS supplier
FROM purchases pu
JOIN suppliers s ON s.id = pu.supplier_id
ORDER BY pu.date DESC;

-- ==========================
-- Agregados y reportes
-- ==========================
-- Stock total y valorizado
SELECT SUM(quantity) AS total_items, SUM(quantity * price) AS inventory_value FROM products;

-- Top productos más vendidos (por cantidad)
SELECT p.id, p.name, SUM(d.quantity) AS qty_sold
FROM details d
JOIN products p ON p.id = d.product_id
GROUP BY p.id, p.name
ORDER BY qty_sold DESC
LIMIT 10;

-- Ingresos por día
SELECT DATE(s.created_at) AS day, SUM(s.total) AS revenue
FROM sales s
GROUP BY DATE(s.created_at)
ORDER BY day DESC;

-- Ingresos por mes
SELECT DATE_FORMAT(s.created_at, '%Y-%m') AS month, SUM(s.total) AS revenue
FROM sales s
GROUP BY DATE_FORMAT(s.created_at, '%Y-%m')
ORDER BY month DESC;

-- Ticket promedio
SELECT AVG(s.total) AS avg_ticket FROM sales s;

-- Ventas por tipo de comprobante
SELECT s.type, COUNT(*) AS cnt, SUM(s.total) AS total_amount
FROM sales s
GROUP BY s.type;

-- Margen/IGV agregado en rango
SELECT SUM(s.subtotal) AS subtotal, SUM(s.igv) AS igv, SUM(s.total) AS total
FROM sales s
WHERE s.created_at BETWEEN ? AND ?;

-- ==========================
-- Ventas y detalles
-- ==========================
-- Total de una venta a partir de detalles
SELECT d.sale_id, SUM(d.amount) AS total_details
FROM details d
WHERE d.sale_id = ?
GROUP BY d.sale_id;

-- Valorización por producto
SELECT p.id, p.name, SUM(d.amount) AS total_amount
FROM details d
JOIN products p ON p.id = d.product_id
GROUP BY p.id, p.name
ORDER BY total_amount DESC;

-- ==========================
-- Inventario y alertas
-- ==========================
-- Productos sin stock
SELECT * FROM products WHERE quantity = 0;

-- Reposición sugerida (debajo del umbral)
SELECT id, code, name, quantity FROM products WHERE quantity < ? ORDER BY quantity ASC;

-- ==========================
-- Proveedores y compras
-- ==========================
-- Compras por proveedor con conteo
SELECT s.id AS supplier_id, s.fullname, COUNT(pu.id) AS purchases_count
FROM suppliers s
LEFT JOIN purchases pu ON pu.supplier_id = s.id
GROUP BY s.id, s.fullname
ORDER BY purchases_count DESC;

-- Última compra por proveedor
SELECT pu.*
FROM purchases pu
WHERE pu.supplier_id = ?
ORDER BY pu.date DESC
LIMIT 1;

-- ==========================
-- Clientes y ventas
-- ==========================
-- Ventas por cliente (texto exacto en sales.cliente)
SELECT s.cliente, COUNT(*) AS cnt, SUM(s.total) AS total
FROM sales s
WHERE s.cliente = ?
GROUP BY s.cliente;

-- Top clientes por monto (sales.cliente como texto)
SELECT s.cliente, SUM(s.total) AS total
FROM sales s
GROUP BY s.cliente
ORDER BY total DESC
LIMIT 10;

-- ==========================
-- Mantenimiento/ayudas
-- ==========================
-- Productos por código exacto
SELECT * FROM products WHERE code = ?;

-- Productos ordenados por rotación (más vendidos primero)
SELECT p.id, p.name, COALESCE(SUM(d.quantity), 0) AS qty_sold
FROM products p
LEFT JOIN details d ON d.product_id = p.id
GROUP BY p.id, p.name
ORDER BY qty_sold DESC;
