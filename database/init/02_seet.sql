USE citas;

INSERT INTO users (name, email, password_hash, role)
VALUES ('Administrador', 'admin@citas.local', '$2y$12$zklGWiffVJu/h7QZTe5uFuAblqsjGFby4UA/81QwADQacbtgupAsi', 'admin');

INSERT INTO doctors (license_number, first_name, last_name, specialty) VALUES
('RM-1001', 'Camilo', 'Robledo', 'Odontología general'),
('RM-1002', 'Laura', 'Mendoza', 'Ortodoncia'),
('RM-1003', 'Santiago', 'Ríos', 'Endodoncia');

INSERT INTO rooms (code, name) VALUES
('C-01', 'Consultorio 1'),
('C-02', 'Consultorio 2'),
('C-03', 'Consultorio 3');

INSERT INTO patients
(document_type, document_number, first_name, last_name, birth_date, sex, phone, email)
VALUES
('CC', '91222333', 'Carlos Jesús', 'Rodríguez Cala', '1990-03-15', 'M', '3001234567', 'carlos@example.com'),
('CC', '52222111', 'María Fernanda', 'Gómez Ruiz', '1988-07-21', 'F', '3019876543', 'maria@example.com');