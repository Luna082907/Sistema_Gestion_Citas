<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Service\AppointmentService;

final class ApiController{
    public function __construct(private AppointmentService $service){
    }

    public function availability(): void{
        header('Content-Type: application/json; charset=utf-8');
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['message' => 'Debe iniciar sesión.'], JSON_UNESCAPED_UNICODE |
            JSON_THROW_ON_ERROR);
            return;
        }
        $date = trim((string) ($_GET['date'] ?? ''));
        $doctorId = (int) ($_GET['doctor_id'] ?? 0);
        $roomId = (int) ($_GET['room_id'] ?? 0);
        echo json_encode([
            'slots' => $this->service->availableSlots($date, $doctorId, $roomId),
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}