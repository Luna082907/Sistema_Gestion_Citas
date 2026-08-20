<section class="empty-state">
    <h1>Error del servidor</h1>
    <p>No fue posible completar la solicitud.</p>
    <?php
    if (!empty($details)):
    ?>
    <pre class ="error-details"><?=e($details)?></pre>
    <?= e($details) ?></pre><?php endif; ?>
    <a class="button primary" href="<?=e(url('/'))?>">Ir al inicio</a>
</section>