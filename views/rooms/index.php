<section class="page-header">
    <div>
        <h1>Consultorios</h1>
        <p>Busque por nombre.</p>
    </div>
    <a class="button primary" href="<?= e(url('/rooms/create')) ?>">Nuevo consultorio</a>
</section>

<form class="search-form" method="get" action="<?= e(url('/rooms')) ?>">
    <label class="sr-only" for="q">Término de búsqueda</label>
    <input id="q" name="q" value="<?= e($term) ?>" placeholder="Nombre">
    <button class="button secondary" type="submit">Buscar</button>
    <?php if ($term !== ''): ?>
        <a class="button secondary" href="<?= e(url('/rooms')) ?>">Limpiar</a>
    <?php endif; ?>
</form>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Estado</th>
                <th>Opción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rooms as $room): ?>
                <tr>
                    <td><?= e($room['name']) ?></td>
                    <td><?= e(($room['active'] ?? '1') == 1 ? 'Activo' : 'Inactivo') ?></td>
                    <td>
                        <a class="button secondary" href="<?= e(url('/rooms/'. $room['id'].'/edit'))?>">Editar</a>
                    </td>
                    <td>
                        <form method="POST" action="<?= e(url('/patients/' . $patient['id'] . '/delete')) ?>" style="display:inline;" data-confirm='¿Está seguro de eliminar este paciente?'>
                            <?= csrf_field() ?>
                            <button type="submit" class="button danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($rooms === []): ?>
                <tr>
                    <td colspan="7">
                        <?= $term === '' ? 'No hay consultorios registrados.' : 'No se encontraron consultorios para la búsqueda.' ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>