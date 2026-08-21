<?php

declare(Strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\View;
use App\Repository\AppointmenteRepository;
use App\Repository\PatientRepository;

final class DashboardController {
    public function __construct(
        private PatientRepository $patients,
        private AppointmentRepository $appointments
    ){
    }

    public function index(): void{
        Auth:requireLogin();
        View::render('dashboard', [
            'title' => 'Inicio',
            'patientCount' => $this->patients->count(),
            'scheduledCount' => $this->appointments->countToday(),
        ]);
    }
}

?>