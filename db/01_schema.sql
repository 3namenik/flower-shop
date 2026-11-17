-- ============================================
-- 🌷 СХЕМА БАЗЫ ДАННЫХ
-- ============================================
USE flower_shop;

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET CHARACTER SET utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items, orders, products, customers, suppliers, categories;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(60) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB;

CREATE TABLE suppliers (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(100) NOT NULL,
    phone   VARCHAR(20),
    email   VARCHAR(100),
    address VARCHAR(200)
) ENGINE=InnoDB;

CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    category_id INT,
    supplier_id INT,
    color       VARCHAR(40),
    description TEXT,
    price       DECIMAL(10,2) NOT NULL,
    stock_qty   INT NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_products_supplier
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    CONSTRAINT chk_products_price CHECK (price >= 0),
    CONSTRAINT chk_products_stock CHECK (stock_qty >= 0)
) ENGINE=InnoDB;

CREATE TABLE customers (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(100) NOT NULL,
    phone      VARCHAR(20) UNIQUE,
    email      VARCHAR(100) UNIQUE,
    birth_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE orders (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    customer_id   INT NOT NULL,
    order_date    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status        ENUM('новый','в работе','доставлен','отменён')
                  NOT NULL DEFAULT 'новый',
    total_price   DECIMAL(10,2) NOT NULL DEFAULT 0,
    delivery_addr VARCHAR(200),
    comment       TEXT,

    CONSTRAINT fk_orders_customer
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT NOT NULL,
    product_id INT NOT NULL,
    quantity   INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,

    CONSTRAINT fk_items_order
        FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    CONSTRAINT fk_items_product
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    CONSTRAINT chk_items_qty   CHECK (quantity > 0),
    CONSTRAINT chk_items_price CHECK (unit_price >= 0)
) ENGINE=InnoDB;

CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_orders_customer   ON orders(customer_id);
CREATE INDEX idx_orders_date       ON orders(order_date);
CREATE INDEX idx_items_order       ON order_items(order_id);
CREATE INDEX idx_items_product     ON order_items(product_id);

-- ---------- триггеры пересчёта суммы заказа ----------
DELIMITER $$

CREATE TRIGGER trg_recalc_total_ins
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE orders SET total_price = (
        SELECT COALESCE(SUM(quantity * unit_price), 0)
        FROM order_items WHERE order_id = NEW.order_id
    ) WHERE id = NEW.order_id;
END$$

CREATE TRIGGER trg_recalc_total_upd
AFTER UPDATE ON order_items
FOR EACH ROW
BEGIN
    UPDATE orders SET total_price = (
        SELECT COALESCE(SUM(quantity * unit_price), 0)
        FROM order_items WHERE order_id = NEW.order_id
    ) WHERE id = NEW.order_id;
END$$

CREATE TRIGGER trg_recalc_total_del
AFTER DELETE ON order_items
FOR EACH ROW
BEGIN
    UPDATE orders SET total_price = (
        SELECT COALESCE(SUM(quantity * unit_price), 0)
        FROM order_items WHERE order_id = OLD.order_id
    ) WHERE id = OLD.order_id;
END$$

DELIMITER ;