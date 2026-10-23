<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($titulo ?? 'BRAMAJO') ?> - BRAMAJO</title>
<link rel="icon" href="/img/favicon.png">
<?php foreach ($estilos as $hoja): ?>
  <link rel="stylesheet" href="/css/<?= $hoja ?>.css">
<?php endforeach; ?>
<link rel="stylesheet" href="/css/comun.css">
