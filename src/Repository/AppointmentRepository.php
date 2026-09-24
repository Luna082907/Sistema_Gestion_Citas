<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class AppointmentRepository{
    public function __construct(private PDO $pdo){
    }

    public function list(string $document = ''): array{
        $sql = 'SELECT a.id, a.appointment_date, a.appointment_time, a.status,

        p.document_number, p.first_name AS patient_first_name,
        p.last_name AS patient_last_name,
        d.first_name AS doctor_first_name, d.last_name AS doctor_last_name,
        r.code AS room_code
        FROM appointments a
        JOIN patients p ON p.id = a.patient_id
        JOIN doctors d ON d.id = a.doctor_id
        JOIN rooms r ON r.id = a.room_id';

        $params = [];

        if ($document !== '') {
            $sql .= ' WHERE p.document_number = :document';
            $params['document'] = $document;
        }

        $sql .= ' ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT 150';
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public function find(int $id): ?array{
        $statement = $this->pdo->prepare(
        'SELECT a.*, p.document_type, p.document_number,
        p.first_name AS patient_first_name, p.last_name AS patient_last_name,
        d.license_number, d.first_name AS doctor_first_name,
        d.last_name AS doctor_last_name, d.specialty,
        r.code AS room_code, r.name AS room_name,
        creator.name AS creator_name,
        canceller.name AS canceller_name,
        completer.name AS completer_name
        FROM appointments a
        JOIN patients p ON p.id = a.patient_id
        JOIN doctors d ON d.id = a.doctor_id
        JOIN rooms r ON r.id = a.room_id
        JOIN users creator ON creator.id = a.created_by
        LEFT JOIN users canceller ON canceller.id = a.cancelled_by
        LEFT JOIN users completer ON completer.id = a.completed_by
        WHERE a.id = :id LIMIT 1'
        );

        $statement->execute(['id' => $id]);
        $appointment = $statement->fetch();
        return $appointment === false ? null : $appointment;
    }

    /** @return list<string> */
    public function occupiedTimes(string $date, int $doctorId, int $roomId): array{
        $statement = $this->pdo->prepare(
        "SELECT appointment_time
        FROM appointments
        WHERE appointment_date = :date
        AND status = 'scheduled'
        AND (doctor_id = :doctor_id OR room_id = :room_id)"
        );

        $statement->execute([
        'date' => $date,
        'doctor_id' => $doctorId,
        'room_id' => $roomId,
        ]);

        return array_column($statement->fetchAll(), 'appointment_time');
    }

    public function hasConflict(string $date, string $time, int $patientId, int $doctorId, int $roomId): bool{
        $statement = $this->pdo->prepare(
        "SELECT COUNT(*)
        FROM appointments
        WHERE appointment_date = :date
        AND appointment_time = :time
        AND status = 'scheduled'
        AND (patient_id = :patient_id OR doctor_id = :doctor_id OR room_id = :room_id)"
        );

        $statement->execute([
        'date' => $date,
        'time' => $time,
        'patient_id' => $patientId,
        'doctor_id' => $doctorId,
        'room_id' => $roomId,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function create(array $data): int{
        $statement = $this->pdo->prepare(
        'INSERT INTO appointments
        (patient_id, doctor_id, room_id, appointment_date, appointment_time, notes, created_by)
        VALUES
        (:patient_id, :doctor_id, :room_id, :appointment_date, :appointment_time, :notes, :created_by)'
        );

        $statement->execute($data);
        return (int) $this->pdo->lastInsertId();
    }
    
    public function cancel(int $id, int $userId): bool{
        $statement = $this->pdo->prepare(
        "UPDATE appointments
        SET status = 'cancelled', cancelled_by = :user_id, cancelled_at = NOW()
        WHERE id = :id AND status = 'scheduled'"
        );

        $statement->execute(['id' => $id, 'user_id' => $userId]);
        return $statement->rowCount() === 1;
    }

    public function complete(int $id, int $userId): bool{
        $statement = $this->pdo->prepare(
        "UPDATE appointments
        SET status = 'completed', completed_by = :user_id, completed_at = NOW()
        WHERE id = :id
        AND status = 'scheduled'
        AND TIMESTAMP(appointment_date, appointment_time) <= NOW()"
        );

        $statement->execute(['id' => $id, 'user_id' => $userId]);
        return $statement->rowCount() === 1;
    }

    public function countToday(): int{
        return (int) $this->pdo->query(
        "SELECT COUNT(*) FROM appointments
        WHERE appointment_date = CURRENT_DATE AND status = 'scheduled'"
        )->fetchColumn();
    }

    public function countScheduled(): int{
        return (int) $this->pdo->query(
        "SELECT COUNT(*) FROM appointments WHERE status = 'scheduled'"
        )->fetchColumn();
    }

    public function countAttended(): int{
        return (int) $this->pdo->query(
        "SELECT COUNT(*) FROM appointments WHERE status = 'completed'"
        )->fetchColumn();
    }

    public function countCancelled(): int{
        return (int) $this->pdo->query(
        "SELECT COUNT(*) FROM appointments WHERE status = 'cancelled'"
        )->fetchColumn();
    }

    public function listByDate(string $date): array{ /**Creación de una nueva función que recibe el dato string $date (fecha)*/
    $statement = $this->pdo->prepare( /**Prepara los datos*/
            'SELECT /**Quiero informacion de... */
                a.id,
                a.appointment_date,
                a.appointment_time,
                a.status,
                p.document_number,
                p.first_name AS patient_first_name,
                p.last_name AS patient_last_name,
                d.first_name AS doctor_first_name,
                d.last_name AS doctor_last_name,
                r.code AS room_code,
                r.name AS room_name
            FROM appointments a
            JOIN patients p ON p.id = a.patient_id /**Pero que este relacionado con... */
            JOIN doctors d ON d.id = a.doctor_id
            JOIN rooms r ON r.id = a.room_id
            WHERE a.appointment_date = :date
            ORDER BY a.appointment_time ASC'
        );

        $statement->execute([ /**Pone a ejecutar $statement*/
            'date' => $date /**date recibe el valor de $date */
        ]);

        return $statement->fetchAll();
    }

    public function listByAgenda(array $filters): array{
            $sql = 'SELECT /**Quiero informacion de... */
                a.id,
                a.appointment_date,
                a.appointment_time,
                a.status,
                p.document_number,
                p.first_name AS patient_first_name,
                p.last_name AS patient_last_name,
                d.first_name AS doctor_first_name,
                d.last_name AS doctor_last_name,
                r.code AS room_code,
                r.name AS room_name
            FROM appointments a
            JOIN patients p ON p.id = a.patient_id /**Pero que este relacionado con... */
            JOIN doctors d ON d.id = a.doctor_id
            JOIN rooms r ON r.id = a.room_id
            WHERE 1=1';

            $params = []; /**Guardamos los valores que vamos a enviarle a la consulta SQL */

            if ($filters['date'] !== ''){ /**El usuario puso una fecha? */
                $sql .= ' AND a.appointment_date = :date';/**Si si entonces busca (creo) */
                $params['date'] = $filters['date'];
            };

            if ($filters['doctor_id'] !== ''){
                $sql .= ' AND a.doctor_id = :doctor_id';
                $params['doctor_id'] = $filters['doctor_id'];
            };

            if ($filters['status'] !== ''){
                $sql .= ' AND a.status = :status';
                $params['status'] = $filters['status'];
            };

            if ($filters['room_id'] !== ''){
                $sql .= ' AND a.room_id = :room_id';
                $params['room_id'] = $filters['room_id'];
            };

            $sql .= ' ORDER BY a.appointment_time ASC'; /**Ordena de modo ascendente */

            $statement = $this->pdo->prepare($sql); /**Prepara para ejecutar */
            $statement -> execute($params); /**Ejecuta segun los parametros */
            return $statement->fetchAll(); /**Recoge y devuelve */

    }

    public function listByDoctor(int $doctorId, string $document = ''): array
    {
        // SELECT ... FROM appointments ... WHERE a.doctor_id = :doctor_id ...
    }

    public function markNoShow(int $id, int $userId): bool { /* status = 'no_show' */ }
    public function updateNotes(int $id, ?string $notes): bool { /* UPDATE notes */ }


}

?>