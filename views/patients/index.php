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
            <h1>Pacientes</h1>
            <p>Busque por documento, nombres o apellidos</p>
            <a href="<?=e(url('/patients/create'))?>" class="button primary">Nuevo paciente</a>
        </div>
    </section>

    <form action="<?=e(url('/patients'))?>" method="get" class="search-form">
        <label for="q" class="sr-only">Termino de busqueda</label>
        <input type="text" name="q" id="q" value="<?=e($term)?>" placeholder="Documento o nombre">
        <button class="button secondary" type="submit">Buscar</button>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Paciente</th>
                    <th>Nacimiento</th>
                    <th>Contacto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patients as $patient): ?>
                <tr>
                    <td><?= e($patient['document_type']) ?> <?= e($patient['document_number']) ?></td>
                    <td><?= e($patient['first_name'] . ' ' . $patient['last_name']) ?></td>
                    <td><?= e(format_date($patient['birth_date'])) ?></td>
                    <td><?= e($patient['phone'] ?: $patient['email'] ?: 'Sin dato') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if ($patients === []): ?><tr><td colspan="4">No se encontraron pacientes.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>


</body>
</html>