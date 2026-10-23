<?php
// Un enfrentamiento con el estilo de resultados del diseño. El marcador solo se ve si la ronda está cerrada,
// salvo en la gestión, donde el organizador ve todo.
$verMarcador = $ronda['estado'] === 'CERRADA' || !empty($gestion);
?>
<?php if ($cruce['id_visitante'] === null): ?>
    <article class="resultado-individual">
        <p><?= e($nombres[$cruce['id_local']] ?? '') ?></p>
        <strong>Pasa libre</strong>
    </article>
<?php else: ?>
    <article class="resultado">
        <div class="resultado__participante">
            <p><?= e($nombres[$cruce['id_local']] ?? '') ?></p>
            <strong><?= $verMarcador && $cruce['puntos_local'] !== null ? $cruce['puntos_local'] : '-' ?></strong>
        </div>
        <span class="resultado__separador">VS<br><small><?= fecha($cruce['fecha_hora'], true) ?></small></span>
        <div class="resultado__participante">
            <p><?= e($nombres[$cruce['id_visitante']] ?? '') ?></p>
            <strong><?= $verMarcador && $cruce['puntos_visitante'] !== null ? $cruce['puntos_visitante'] : '-' ?></strong>
        </div>
        <?php if (!empty($gestion) && ($ronda['estado'] === 'ABIERTA' || $puedeCorregir)): ?>
            <a class="boton-chico" href="/gestion/resultado?id=<?= $cruce['id_enfrentamiento'] ?>"><?= $cruce['puntos_local'] === null ? 'Cargar' : 'Corregir' ?></a>
        <?php endif; ?>
    </article>
<?php endif; ?>
