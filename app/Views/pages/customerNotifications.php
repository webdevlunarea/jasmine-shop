<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<?php
$activeAccountMenu = 'notifications';
$statusLabel = static fn(string $status): string => match ($status) {
    'sent' => 'Baru',
    'read' => 'Sudah dibaca',
    'awaiting_device' => 'Menunggu perangkat',
    default => 'Pemberitahuan',
};
$displayDate = static function ($value): string {
    if (!$value) return '';
    try {
        return (new DateTimeImmutable((string) $value, new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('d M Y, H:i') . ' WIB';
    } catch (Throwable) {
        return '';
    }
};
?>
<div class="konten account-shell">
    <div class="container">
        <div class="account-layout">
            <?= $this->include('partials/account_nav'); ?>
            <main class="account-content-card" aria-labelledby="notifications-title">
                <header class="d-flex justify-content-between align-items-start gap-3 flex-wrap account-page-heading">
                    <div>
                        <h1 id="notifications-title">Pemberitahuan</h1>
                        <p class="text-secondary mb-0">Info pesanan dan kabar Lunarea untuk akun Anda.</p>
                    </div>
                    <?php if ($available && $unread > 0) { ?>
                        <form action="/notifications/read" method="post">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="all" value="1">
                            <button type="submit" class="btn btn-outline-success">Tandai semua dibaca</button>
                        </form>
                    <?php } ?>
                </header>

                <?php if ($message) { ?><div class="alert alert-info" role="status"><?= esc($message); ?></div><?php } ?>

                <?php if (!$available) { ?>
                    <div class="account-empty-state">
                        <h2 class="h5">Pemberitahuan belum tersedia</h2>
                        <p class="text-secondary mb-0">Silakan coba lagi nanti.</p>
                    </div>
                <?php } elseif (!$notifications) { ?>
                    <div class="account-empty-state">
                        <span class="material-icons" aria-hidden="true">notifications_none</span>
                        <h2 class="h5">Belum ada pemberitahuan</h2>
                        <p class="text-secondary mb-0">Pemberitahuan terkait pesanan dan akun akan muncul di sini.</p>
                    </div>
                <?php } else { ?>
                    <div class="customer-notification-list" aria-label="Daftar pemberitahuan">
                        <?php foreach ($notifications as $item) { ?>
                            <?php $isUnread = ($item['status'] ?? '') === 'sent'; ?>
                            <article class="customer-notification-item <?= $isUnread ? 'is-unread' : ''; ?>">
                                <div class="customer-notification-copy">
                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                        <h2 class="h6 mb-0"><?= esc($item['title'] ?? 'Pemberitahuan Lunarea'); ?></h2>
                                        <span class="customer-notification-status"><?= esc($statusLabel((string) ($item['status'] ?? ''))); ?></span>
                                    </div>
                                    <p class="mb-1 text-secondary"><?= nl2br(esc($item['body'] ?? '')); ?></p>
                                    <time class="small text-secondary" datetime="<?= esc((string) ($item['created_at'] ?? ''), 'attr'); ?>"><?= esc($displayDate($item['created_at'] ?? null)); ?></time>
                                </div>
                                <?php if ($isUnread) { ?>
                                    <form action="/notifications/read" method="post" class="customer-notification-action">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?= (int) $item['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success">Tandai dibaca</button>
                                    </form>
                                <?php } ?>
                            </article>
                        <?php } ?>
                    </div>
                    <?php if ($lastPage > 1) { ?>
                        <nav class="d-flex justify-content-between align-items-center gap-2 mt-3" aria-label="Halaman pemberitahuan">
                            <?php if ($page > 1) { ?><a class="btn btn-outline-success" href="/notifications?page=<?= $page - 1; ?>">Sebelumnya</a><?php } else { ?><span></span><?php } ?>
                            <span class="small text-secondary">Halaman <?= (int) $page; ?> dari <?= (int) $lastPage; ?></span>
                            <?php if ($page < $lastPage) { ?><a class="btn btn-outline-success" href="/notifications?page=<?= $page + 1; ?>">Berikutnya</a><?php } else { ?><span></span><?php } ?>
                        </nav>
                    <?php } ?>
                <?php } ?>
            </main>
        </div>
    </div>
</div>
<style>
.customer-notification-list { border-top: 1px solid var(--theme-soft-2); }
.customer-notification-item { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:18px 0; border-bottom:1px solid var(--theme-soft-2); }
.customer-notification-item.is-unread { border-left:3px solid var(--theme-primary); padding-left:14px; }
.customer-notification-copy { min-width:0; }
.customer-notification-status { padding:3px 7px; border-radius:4px; background:var(--theme-soft); color:var(--theme-primary); font-size:.7rem; }
.customer-notification-action { flex:0 0 auto; }
@media(max-width:575.98px) { .customer-notification-item { flex-direction:column; } .customer-notification-action { align-self:flex-start; } }
</style>
<?= $this->endSection(); ?>
