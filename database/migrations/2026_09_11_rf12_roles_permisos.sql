USE citas;

-- 1) Convertir el rol legado ANTES de reducir el ENUM
UPDATE users SET role = 'receptionist' WHERE role = 'employee';

-- 2) Nuevos roles + enlace con médicos
ALTER TABLE users
    MODIFY COLUMN role ENUM('admin','receptionist','doctor') NOT NULL DEFAULT 'receptionist';
ALTER TABLE users ADD COLUMN doctor_id BIGINT UNSIGNED NULL AFTER role;
ALTER TABLE users
    ADD CONSTRAINT fk_users_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id);

-- 3) Estado "no asistida"
ALTER TABLE appointments
    MODIFY COLUMN status ENUM('scheduled','completed','cancelled','no_show')
    NOT NULL DEFAULT 'scheduled';
ALTER TABLE appointments
    ADD COLUMN no_show_by BIGINT UNSIGNED NULL AFTER completed_at,
    ADD COLUMN no_show_at DATETIME NULL AFTER no_show_by;
ALTER TABLE appointments
    ADD CONSTRAINT fk_appointments_no_show_by FOREIGN KEY (no_show_by) REFERENCES users(id);