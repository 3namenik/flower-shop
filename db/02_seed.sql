SET NAMES utf8mb4;

USE flower_shop;

INSERT INTO categories (name, description) VALUES
('Розы',      'Классические и пионовидные'),
('Тюльпаны',  'Весенние цветы'),
('Букеты',    'Готовые композиции'),
('Комнатные', 'Горшечные растения');

INSERT INTO suppliers (name, phone, email) VALUES
('Цветочная база "Ромашка"', '+7 900 111-22-33', 'sales@romashka.ru'),
('Импорт Флора',             '+7 900 444-55-66', 'info@importflora.ru');

INSERT INTO products (name, category_id, supplier_id, color, price, stock_qty) VALUES
('Роза "Эквадор"',   1, 2, 'красный', 350.00, 120),
('Роза пионовидная', 1, 1, 'розовый', 420.00,  80),
('Тюльпан',          2, 1, 'жёлтый',  120.00, 200),
('Эустома',          2, 1, 'белый',   280.00,  60);

INSERT INTO products (name, category_id, color, description, price, stock_qty) VALUES
('Нежность', 3, 'розовый', 'Пионовидные розы + эустома', 3500.00, 5),
('Весенний', 3, 'микс',    'Тюльпаны микс + зелень',     1800.00, 8),
('Классика', 3, 'красный', '15 красных роз Эквадор',     5200.00, 3);

INSERT INTO customers (full_name, phone, email, birth_date) VALUES
('Анна Иванова', '+7 901 123-45-67', 'anna@mail.ru', '1995-03-14'),
('Пётр Сидоров', '+7 902 765-43-21', 'petr@mail.ru', '1990-11-02');

INSERT INTO orders (customer_id, status, delivery_addr, comment) VALUES
(1, 'доставлен', 'ул. Ленина, 10, кв. 5', 'Позвонить за 30 минут'),
(2, 'в работе',  'ул. Пушкина, 3',        NULL);

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 5, 1,  3500.00),
(2, 7, 1,  5200.00),
(2, 1, 11,  350.00);