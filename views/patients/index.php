<section class="page-header">
    <div>
        <h1>Pacientes</h1>
        <p>Busque por documento, nombres o apellidos.</p>
    </div>
    <a class="button primary" href="<?= e(url('/patients/create')) ?>">Nuevo paciente</a>
</section>

<form class="search-form" method="get" action="<?= e(url('/patients')) ?>">
    <label class="sr-only" for="q">Término de búsqueda</label>
    <input id="q" name="q" value="<?= e($term) ?>" placeholder="Documento o nombre">
    <button class="button secondary" type="submit">Buscar</button>
    <?php if ($term !== ''): ?>
        <a class="button secondary" href="<?= e(url('/patients')) ?>">Limpiar</a>
    <?php endif; ?>
</form>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Documento</th>
                <th>Paciente</th>
                <th>Nacimiento</th>
                <th>Contacto</th>
                <th>Estado</th>
                <th>Opción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($patients as $patient): ?>
                <tr>
                    <td><?= e($patient['document_type']) ?> <?= e($patient['document_number']) ?></td>
                    <td><?= e($patient['first_name'] . ' ' . $patient['last_name']) ?></td>
                    <td><?= e(format_date($patient['birth_date'])) ?></td>
                    <td><?= e($patient['phone'] ?: $patient['email'] ?: 'Sin dato') ?></td>
                    <td><?= e(($patient['active'] ?? '1') == 1 ? 'Activo' : 'Inactivo') ?></td>
                    <td>
                        <a class="button secondary" href="<?= e(url('/patients/'. $patient['id'].'/edit'))?>">Editar</a>
                    </td>
                    <td>
                        <form method="POST" action="<?= e(url('/patients/' . $patient['id'] . '/delete')) ?>" style="display:inline;" data-confirm='¿Está seguro de eliminar este paciente?'>
                            <?= csrf_field() ?>
                            <button type="submit" class="button danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($patients === []): ?>
                <tr>
                    <td colspan="7">
                        <?= $term === '' ? 'No hay pacientes registrados.' : 'No se encontraron pacientes para la búsqueda.' ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total > 0): ?>
    <?php
        $pageUrl = static function (int $target) use ($term): string {
            $query = ['page' => $target];
            if ($term !== '') {
                $query['q'] = $term;
            }
            return url('/patients') . '?' . http_build_query($query);
        };

        $window = 2;
        $start = max(1, $page - $window);
        $end = min($totalPages, $page + $window);
    ?>
    <nav class="pagination" aria-label="Paginación de pacientes">
        <?php if ($page > 1): ?>
            <a class="button secondary" href="<?= e($pageUrl($page - 1)) ?>" rel="prev">Anterior</a>
        <?php else: ?>
            <span class="button secondary is-disabled" aria-disabled="true">Anterior</span>
        <?php endif; ?>

        <span class="pagination-pages">
            <?php if ($start > 1): ?>
                <a class="page-link" href="<?= e($pageUrl(1)) ?>">1</a>
                <?php if ($start > 2): ?><span class="page-ellipsis">…</span><?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="page-link is-current" aria-current="page"><?= e($i) ?></span>
                <?php else: ?>
                    <a class="page-link" href="<?= e($pageUrl($i)) ?>"><?= e($i) ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?><span class="page-ellipsis">…</span><?php endif; ?>
                <a class="page-link" href="<?= e($pageUrl($totalPages)) ?>"><?= e($totalPages) ?></a>
            <?php endif; ?>
        </span>

        <?php if ($page < $totalPages): ?>
            <a class="button secondary" href="<?= e($pageUrl($page + 1)) ?>" rel="next">Siguiente</a>
        <?php else: ?>
            <span class="button secondary is-disabled" aria-disabled="true">Siguiente</span>
        <?php endif; ?>

        <span class="pagination-info">
            Página <?= e($page) ?> de <?= e($totalPages) ?> · <?= e($total) ?> pacientes
        </span>
    </nav>
<?php endif; ?>