<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\Database;
use App\Domain\SlotGenerator;
use App\Repository\AppointmentRepository;
use App\Repository\DoctorRepository;
use App\Repository\RoomRepository;
use DateTimeImmutable;
use DomainException;
use PDOException;

final class AppointmentService{

    /** @param array{day_start:string,day_end:string,slot_minutes:int,max_days_ahead:int,allow_weekends:bool} $schedule */
    public function __construct(
    private Database $database,
    private AppointmentRepository $appointments,
    private DoctorRepository $doctors,
    private RoomRepository $rooms,
    private SlotGenerator $slots,
    private array $schedule
    ) {
    }

    /** @return list<string> */
    public function availableSlots(string $date, int $doctorId, int $roomId): array{
        if (
            !$this->isValidDate($date)
            || $this->doctors->findActive($doctorId) === null
            || $this->rooms->findActive($roomId) === null
        ) {
            return [];
        }

        $occupied = $this->appointments->occupiedTimes($date, $doctorId, $roomId);
        $available = array_values(array_diff($this->allowedSlots(), $occupied));
        if ($date === (new DateTimeImmutable('today'))->format('Y-m-d')) {
           $now = new DateTimeImmutable('now');
            $available = array_values(array_filter(
            $available,                static fn (string $slot): bool => new DateTimeImmutable($date . ' ' . $slot) > $now
            ));
        }
        
        return $available;
    }

    public function create(array $data): int{

        if (!in_array($data['appointment_time'], $this->allowedSlots(), true)) {
            throw new DomainException('La hora seleccionada no pertenece al horario permitido.');
        }

        if (!$this->isValidDate($data['appointment_date'])) {
            throw new DomainException('La fecha de la cita no es válida o no está habilitada.');
        }

        $scheduledAt = new DateTimeImmutable($data['appointment_date'] . ' ' . $data['appointment_time']);
        
        if ($scheduledAt <= new DateTimeImmutable('now')) {
            throw new DomainException('La cita debe programarse para una fecha y hora futuras.');
        }

        if ($this->doctors->findActive((int) $data['doctor_id']) === null) {
            throw new DomainException('El médico seleccionado no está disponible.');
        }

        if ($this->rooms->findActive((int) $data['room_id']) === null) {
            throw new DomainException('El consultorio seleccionado no está disponible.');
        }

        try {
            return $this->database->transaction(function () use ($data): int {
            
            if ($this->appointments->hasConflict(
                $data['appointment_date'],
                $data['appointment_time'],
                (int) $data['patient_id'],
                (int) $data['doctor_id'],
                (int) $data['room_id']
            )) {
                throw new DomainException('El paciente, el médico o el consultorio ya tiene una cita en ese horario.');
            }
                return $this->appointments->create($data);
            });
        } catch (PDOException $exception) {

        if ($exception->getCode() === '23000') {
            throw new DomainException('El horario acaba de ser reservado por otro usuario. Seleccione otro.');
        }
            throw $exception;
        }
    }

    /** @return list<string> */
    private function allowedSlots(): array{
        return $this->slots->generate(
        $this->schedule['day_start'],
        $this->schedule['day_end'],
        $this->schedule['slot_minutes']
        );
    }

    private function isValidDate(string $date): bool{
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        
        if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
            return false;
        }

        $today = new DateTimeImmutable('today');
        $maximum = $today->modify('+' . $this->schedule['max_days_ahead'] . ' days');
        
        if ($parsed < $today || $parsed > $maximum) {
            return false;
        }

        $isWeekend = in_array((int) $parsed->format('N'), [6, 7], true);
        return $this->schedule['allow_weekends'] || !$isWeekend;
    }
}

?>