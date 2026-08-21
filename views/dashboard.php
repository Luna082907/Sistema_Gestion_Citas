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
            <h1>Panel Principal</h1>
            <p>Resumen operativo del sistema de citas</p>
        </div>
    </section>

    <div class="cards">
        <article class="metric-card"><span>Pacientes registrados</span><strong><?=e($patientCount)?></strong></article>
        <article class="metric-card"><span>Citas activas</span><strong><?=e($scheduleCount)?></strong></article>
        <article class="metric-card"><span>Citas para hoy</span><strong><?=e($todayCount)?></strong></article>
    </div>

    <section class="panel">
        <h2>Acciones frecuentes</h2>
        <div class="actions">
            <a href="<?=e(url('/patients/create'))?>" class="button secondary">Registrar paciente</a>
            <a href="<?=e(url('/patients'))?>" class="button secondary">Consultar pacientes</a>
            <a href="<?=e(url('/appointments'))?>" class="button secondary">Consultar citas</a>
        </div>
    </section>

</body>
</html>