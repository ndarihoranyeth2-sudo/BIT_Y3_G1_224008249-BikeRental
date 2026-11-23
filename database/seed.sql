-- ============================================================
-- seed.sql -- sample data (run AFTER schema.sql)
-- Demo logins:  admin / Admin@123   and   staff1 / Staff@123
-- ============================================================
USE bike_rental;

INSERT INTO users (username, email, password_hash, role) VALUES
('administration',  'adminbikerental@gmail.com',  'Thierry123@!', 'admin'),
('staff1', 'staffbikerental@gmail.com', 'Thierry@224008249', 'staff');

INSERT INTO bikes (code, model, hourly_rate, status) VALUES
('BK-001', 'Trek Marlin 5 Mountain',  1500.00, 'available'),
('BK-002', 'Giant Escape 3 City',     1200.00, 'rented'),
('BK-003', 'Specialized Rockhopper',  1800.00, 'available'),
('BK-004', 'Cannondale Quick 4',      1300.00, 'maintenance'),
('BK-005', 'Scott Aspect 950',        1600.00, 'rented'),
('BK-006', 'Raleigh Tamland Hybrid',  1100.00, 'available'),
('BK-007', 'Merida Big Nine 20',      1700.00, 'available'),
('BK-008', 'Kids Cruiser 16',          800.00, 'available');

INSERT INTO customers (full_name, phone, email) VALUES
('Uwase Aline',        '+250788111001', 'alineuwase@example.com'),
('Mugisha Eric',       '+250788111002', 'ericmugisha@.com'),
('Ingabire Chantal',   '+250788111003', 'chantalingabire@.com'),
('Niyonzima Patrick',  '+250788111004', 'patrickniyonzi.com'),
('Mukamana Diane',     '+250788111005', 'dianemukamana@.com');

-- Active rentals match bikes BK-002 (id 2) and BK-005 (id 5) being 'rented'.
INSERT INTO rentals (bike_id, customer_id, start_time, end_time, total_cost, status) VALUES
(1, 1, '2026-11-10 09:00:00', '2026-11-10 11:30:00', 4500.00, 'completed'),
(3, 2, '2026-11-11 14:00:00', '2026-11-11 15:00:00', 1800.00, 'completed'),
(6, 3, '2026-11-12 08:15:00', '2026-11-12 12:00:00', 4400.00, 'completed'),
(7, 4, '2026-11-13 10:00:00', NULL,                  0.00,    'cancelled'),
(2, 5, '2026-11-21 08:30:00', NULL,                  NULL,    'active'),
(5, 1, '2026-11-21 09:00:00', NULL,                  NULL,    'active');
