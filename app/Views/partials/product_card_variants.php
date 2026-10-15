<?php
// Compact preview only. Full variant selection remains on the linked product page.
$cardVariants = is_array($cardVariants ?? null) ? $cardVariants : [];
$cardVariants = array_values(array_unique(array_filter(array_map(static function ($value) {
    return is_scalar($value) ? trim((string) $value) : '';
}, $cardVariants), static function ($value) { return $value !== ''; })));
$variantPreview = implode(' · ', array_slice($cardVariants, 0, 2));
$remainingVariants = max(0, count($cardVariants) - 2);
?>
<div class="product-card-variants varian" role="group"<?= $cardVariants ? ' title="' . esc(implode(' · ', $cardVariants), 'attr') . '" aria-label="' . esc('Varian tersedia: ' . implode(', ', $cardVariants), 'attr') . '"' : ' aria-hidden="true"'; ?>>
    <span class="product-card-variant-preview"><?= esc($variantPreview); ?></span>
    <?php if ($remainingVariants > 0) { ?>
    <span class="product-card-variant-count">+<?= $remainingVariants; ?> varian</span>
    <?php } ?>
</div>
