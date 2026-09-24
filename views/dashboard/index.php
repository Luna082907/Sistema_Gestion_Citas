<section class="page-header">
    <div>
        <h1>Panel principal</h1>
        <p>Resumen operativo del sistema de citas.</p>
    </div>
    <a class="button primary" href="<?= e(url('/appointments/create')) ?>">Asignar cita</a>
</section>

<div class="cards">
    <article class="metric-card">
        <span>Citas para hoy</span>
        <strong><?= e($todayCount) ?></strong>
    </article>
    <article class="metric-card">
        <span>Citas pendientes</span>
        <strong><?= e($scheduledCount) ?></strong>
    </article>
    <article class="metric-card">
        <span>Citas atendidas</span>
        <strong><?= e($attendedCount) ?></strong>
    </article>
    <article class="metric-card">
        <span>Citas canceladas</span>
        <strong><?= e($cancelledCount) ?></strong>
    </article>
    <article class="metric-card">
        <span>Pacientes registrados</span>
        <strong><?= e($patientCount) ?></strong>
    </article>
</div>

<section class="panel">
    <h2>Acciones frecuentes</h2>
    <div class="actions">
        <a class="button secondary" href="<?= e(url('/patients/create')) ?>">Registrar paciente</a>
        <a class="button secondary" href="<?= e(url('/doctors/create')) ?>">Registrar medico</a>
        <a class="button secondary" href="<?= e(url('/patients')) ?>">Consultar pacientes</a>
        <a class="button secondary" href="<?= e(url('/appointments')) ?>">Consultar citas</a>
    </div>
</section>