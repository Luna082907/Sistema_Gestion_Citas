<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

    <section class="page-header">
        <div>
            <h1>Citas</h1>
            <p>Consulte el historial o filtre por documento el paciente</p>
        </div>
        <a href="<?=e(url('/appointments/create'))?>" class="button primary">Asignar cita</a>
    </section>

    <form action="<?=e(url('/appointments'))?>" method="get" class="search-form">
        <label for="document" class="sr-only">Documento</label>
        <input name="document" id="document" value="<?=e($document)?>" placeholder="Documento del paciente">
        <button type="submit" class="button secundary">Consulltar</button>
        <?php if ($document !==''):?><a href="<?=e(url('/appointments'))?>" class="button secondary">Limpiar</a>
        <?php endif;?>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Numero</th>
                    <th>Fecha y hora</th>
                    <th>Paciente</th>
                    <th>Médico</th>
                    <th>Consultorio</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><a href="<?=e(url('/appointments/'.$appointment['id']))?>">#<?=e($appointment['id'])?></a></td>
                        <td><?=e(format_date($appointment['appointment_date']))?>
                            <?=e(format_time($appointment['appointment_time']))?></td>
                        <td><?=e($appointment['patient_first_name'].' '.$appointment['patient_last_name'])?><br>
                            <small><?=e($appointment['document_number'])?></small></td>
                        <td><?=e($appointment['doctor_first_name'].' '.$appointment['doctor_last_name'])?></td>
                        <td><?= e($appointment['room_code']) ?></td>
                        <td><span class="status <?= e($appointment['status']) ?>"><?= e(appointment_status_label($appointment['status'])) ?></span></td>
                    </tr>
            </tbody>
            <?php endforeach; ?>
            <?php if($appointments === []): ?><tr><td colspan="6">No hay citas para mostras</td></tr>
            <?php endif; ?>
        </table>
    </div>

</body>
</html>