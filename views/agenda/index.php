<section class="page-header">
    <div>
        <h1>Agenda</h1>
        <p>Consulte las citas utilizando los filtros disponibles.</p>
    </div>
</section>

<section class="panel">

    <form method="get" action="<?= e(url('/agenda')) ?>" class="form-grid">

        <div>
            <label for="date">Fecha</label>
            <input id="date" name="date" type="date" value="<?= e($date) ?>"> <!--Selecciona fecha desde la actual-->
        </div>

        <div>
            <label for="doctor_id">Médico</label>

            <select id="doctor_id" name="doctor_id">

                <option value="">Todos</option>

                <?php foreach ($doctors as $doctor): ?> <!--Busca los doctores disponibles en la base de datos-->

                    <option value="<?= e($doctor['id']) ?>" <?= (int) ($doctorId ?? 0) === (int) $doctor['id'] ? 'selected' : '' ?>>
                        <?= e(
                            $doctor['first_name'] //**Con su primer nombre y apellido */
                            . ' '
                            . $doctor['last_name']
                        ) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <div>
            <label for="status">Estado</label>

            <select id="status" name="status">

                <option value="">Todos</option>
                <option value="scheduled" <?= ($status ?? '') === 'scheduled' ? 'selected' : '' ?>>Programada</option> <!--Selecciona el estado de la cita-->
                <option value="completed" <?= ($status ?? '') === 'completed' ? 'selected' : '' ?>>Completada</option>
                <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelada</option>

            </select>
        </div>

        <div>
            <label for="room_id">Consultorio</label>

            <select id="room_id" name="room_id">

                <option value="">Todos</option>
                
                <?php foreach ($rooms as $room): ?> <!--Busca los consultorios disponibles en la base de datos-->

                    <option value="<?= e($room['id']) ?>" <?= (int) ($roomId ?? 0) === (int) $room['id'] ? 'selected' : '' ?>>
                        <?= e(
                            $room['code']
                            . ' - '
                            . $room['name']
                        ) ?>
                    </option>

                <?php endforeach; ?>

            </select>
        </div>

        <div class="form-actions full-width">

            <button class="button primary" type="submit">Filtrar</button>
            <a class="button secondary" href="<?= e(url('/agenda')) ?>">Limpiar</a>

        </div>

    </form>

</section>

<section class="panel">

    <h2>Citas <?php if ($date !== ''): ?>del <?= e(format_date($date)) ?><?php endif; ?></h2>

    <?php if ($appointments === []): ?>

        <p>No se encontraron citas con los filtros seleccionados.</p>

    <?php else: ?>

        <div class="table-wrap">

            <table>

                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Paciente</th>
                        <th>Médico</th>
                        <th>Consultorio</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($appointments as $appointment): ?>

                        <tr>

                            <td><?= e(format_date($appointment['appointment_date'])) ?></td>
                            <td><?= e(format_time($appointment['appointment_time'])) ?></td>
                            <td>
                                <?= e(
                                    $appointment['patient_first_name']
                                    . ' '
                                    . $appointment['patient_last_name']
                                ) ?>
                            </td>

                            <td>
                                <?= e(
                                    $appointment['doctor_first_name']
                                    . ' '
                                    . $appointment['doctor_last_name']
                                ) ?>
                            </td>

                            <td>
                                <?= e(
                                    $appointment['room_code']
                                    . ' - '
                                    . $appointment['room_name']
                                ) ?>
                            </td>
                            <td><?= e($appointment['status']) ?></td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</section>