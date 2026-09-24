<?php /**Decide que debe hacer a partir de una accion recibida */

declare(strict_types=1); 

namespace App\Controller; /**Indica a que pertenece la clase */

use App\Core\Auth;
use App\Core\View;
use App\Repository\AppointmentRepository; /**Conexion con los demás repositorios*/
use App\Repository\DoctorRepository;
use App\Repository\RoomRepository;

final class AgendaController /** */
{
    public function __construct(
        private AppointmentRepository $appointments,
        private DoctorRepository $doctors,
        private RoomRepository $rooms
    ) {
    }

    public function index(): void{ /**Funcion publica llamada index que (void) no devuelve valores */
        Auth::requireLogin(); /**Indica que solo la persona que ha iniciado sesion puede realizar el procedimiento */

        $date = trim((string) ($_GET['date'] ?? date('Y-m-d'))); /**$date es un string que solicita la fecha, ?? significa que si no recibe fecha, utiliza la actual*/
        $doctorId = trim ((string) ($_GET['doctor_id'] ?? '')); /**$doctor_id es un string que solicita un doctor, ?? significa que si no recibe uno, utiliza todos*/
        $status = trim ((string) ($_GET['status'] ?? ''));
        $roomId = trim ((string) ($_GET['room_id'] ?? ''));

        $filters = [ /**Lo que contiene el filtro */
            'date' => $date,
            'doctor_id' => $doctorId,
            'status' => $status,
            'room_id' => $roomId,
        ];

        $appointments = $this->appointments->listByAgenda($filters); /**Obtiene los datos correspondientes de los filtros */
        $doctors = $this->doctors->active();
        $rooms = $this->rooms->active();

        View::render('agenda/index', [
            'title' => 'Agenda',
            'date' => $date,
            'doctorId' => $doctorId,
            'status' => $status,
            'roomId' => $roomId,
            'appointments' => $appointments,
            'doctors' => $doctors,
            'rooms' => $rooms,
        ]);

    }

}