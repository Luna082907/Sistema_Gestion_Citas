<section class="page-header">
    <div>
        <h1>Médicos</h1>
        <p>Busque por documento, nombres o apellidos.</p>
    </div>
    <a class="button primary" href="<?= e(url('/doctors/create')) ?>">Nuevo Médico</a>
</section>

<form action="<?=e(url('/doctors'))?>" method="get" class="search-form">
        <label for="q" class="sr-only">Termino de busqueda</label>
        <input type="text" name="q" id="q" value="<?=e($term)?>" placeholder="Documento o nombre">
        <button class="button secondary" type="submit">Buscar</button>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Licencia</th>
                    <th>Doctores</th>
                    <th>Especialidad</th>
                    <th>Estado</th>
                    <th>¿Quiere?</th>
                    <th>¿Desea?</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($doctors as $doctor): ?>
                <tr>
                    <td><?= e($doctor['license_number']) ?></td>
                    <td><?= e($doctor['first_name'] . ' ' . $doctor['last_name']) ?></td>
                    <td><?= e($doctor['specialty']) ?></td>
                    <td><?= e($doctor['active'] )?></td>
                    <td><a class="button secondary" href="<?= e(url('/doctors/' . $doctor['id'] . '/edit')) ?>">Editar</a></td>
                    <td>
                        <form
                            method="POST"
                            action="<?= e(url('/doctors/' . $doctor['id'] . '/delete')) ?>"
                            style="display:inline;"
                            data-confirm="¿Esta seguro?"
                            >
                            <?= csrf_field() ?>
                            <button type="submit" class="button danger">
                            Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if ($doctors === []): ?><tr><td colspan="5">No se encontraron médicos.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
