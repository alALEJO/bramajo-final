<?php if ($mensaje): ?>
  <p class="mensaje <?= $mensaje['tipo'] ?>"><?= e($mensaje['texto']) ?></p>
<?php endif; ?>
