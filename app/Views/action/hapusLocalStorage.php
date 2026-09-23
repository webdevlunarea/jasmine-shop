<script>
    window.localStorage.removeItem('notifVoucher');
    window.location.replace(<?= json_encode($tujuan, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>);
</script>
