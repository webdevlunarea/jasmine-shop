<?php
$adminHeadingTitle = (string)($adminHeadingTitle ?? 'Administrasi');
$adminHeadingGroup = (string)($adminHeadingGroup ?? 'Lunarea admin');
$adminHeadingDescription = (string)($adminHeadingDescription ?? '');
$adminHeadingActions = is_array($adminHeadingActions ?? null) ? $adminHeadingActions : [];
?>
<header class="admin-module-heading">
    <div>
        <p class="admin-section-kicker"><?= esc($adminHeadingGroup); ?></p>
        <h2><?= esc($adminHeadingTitle); ?></h2>
        <?php if ($adminHeadingDescription !== '') { ?><p class="admin-module-description"><?= esc($adminHeadingDescription); ?></p><?php } ?>
    </div>
    <?php if ($adminHeadingActions) { ?>
    <div class="admin-module-actions">
        <?php foreach ($adminHeadingActions as $action) { ?>
        <a class="btn <?= !empty($action['primary']) ? 'btn-primary1' : 'orders-secondary'; ?>" href="<?= esc($action['href'], 'attr'); ?>" <?= !empty($action['external']) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
            <?php if (!empty($action['icon'])) { ?><i class="material-icons" aria-hidden="true"><?= esc($action['icon']); ?></i><?php } ?>
            <span><?= esc($action['label']); ?></span>
        </a>
        <?php } ?>
    </div>
    <?php } ?>
</header>
