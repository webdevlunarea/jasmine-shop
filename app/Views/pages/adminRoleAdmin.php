<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<main class="container-fluid px-3 px-lg-4 py-4 admin-role-page">
    <header class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold mb-1" style="letter-spacing:.12em;color:var(--admin-primary)">Kontrol akses</p>
            <h2 class="h3 mb-1">Peran &amp; hak akses</h2>
            <p class="text-muted mb-0">Buat peran sesuai tugas, pilih izin yang diperlukan, lalu tetapkan ke akun admin.</p>
        </div>
        <span class="badge rounded-pill text-bg-dark px-3 py-2">DEV</span>
    </header>

    <?php if (!empty($msg)): ?><div class="alert alert-info" role="status"><?= esc($msg); ?></div><?php endif; ?>

    <?php if (!$ready): ?>
        <div class="alert alert-warning" role="alert"><strong>Setup database belum selesai.</strong> Jalankan SQL RBAC dari <code>docs/security/admin-rbac.sql</code> di database Lunarea, lalu logout dan login kembali.</div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-12 col-xl-7">
                <section class="bg-white border rounded-4 p-3 p-lg-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div><h3 class="h5 mb-1"><?= $selectedRole ? 'Ubah peran kustom' : 'Buat peran kustom'; ?></h3><p class="small text-muted mb-0">Izin kritis seperti pengelolaan peran dan pemeliharaan sistem tetap khusus DEV.</p></div>
                        <?php if ($selectedRole): ?><a class="btn btn-sm btn-outline-secondary" href="/admin/roles">Peran baru</a><?php endif; ?>
                    </div>
                    <form method="post" action="/admin/roles/save">
                        <?php if ($selectedRole): ?><input type="hidden" name="role_key" value="<?= esc($selectedRole['role_key'], 'attr'); ?>"><?php endif; ?>
                        <label class="form-label" for="admin-role-label">Nama peran</label>
                        <input class="form-control mb-3" id="admin-role-label" name="label" maxlength="60" required value="<?= esc($selectedRole['label'] ?? '', 'attr'); ?>" placeholder="Contoh: Pengelola Katalog">
                        <label class="form-label" for="admin-role-description">Keterangan</label>
                        <textarea class="form-control mb-3" id="admin-role-description" name="description" rows="2" maxlength="255" placeholder="Jelaskan tanggung jawab peran ini"><?= esc($selectedRole['description'] ?? ''); ?></textarea>
                        <fieldset>
                            <legend class="form-label fs-6">Pilih hak akses</legend>
                            <div class="row g-2">
                                <?php foreach ($permissionCatalog as $permission => $label): ?>
                                    <div class="col-12 col-md-6">
                                        <label class="d-flex align-items-start gap-2 border rounded-3 p-2 h-100">
                                            <input class="form-check-input mt-1" type="checkbox" name="permissions[]" value="<?= esc($permission, 'attr'); ?>" <?= in_array($permission, $selectedPermissions, true) ? 'checked' : ''; ?>>
                                            <span><strong class="small d-block"><?= esc($label); ?></strong><code class="small text-muted"><?= esc($permission); ?></code></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>
                        <button class="btn btn-primary mt-3" type="submit"><?= $selectedRole ? 'Simpan perubahan' : 'Buat peran'; ?></button>
                    </form>
                </section>
            </div>

            <div class="col-12 col-xl-5">
                <section class="bg-white border rounded-4 p-3 p-lg-4 shadow-sm mb-4">
                    <h3 class="h5 mb-1">Tetapkan peran admin</h3>
                    <p class="small text-muted">Hanya akun dengan role admin website yang tersedia.</p>
                    <form method="post" action="/admin/roles/assign">
                        <label class="form-label" for="admin-role-email">Akun admin</label>
                        <select class="form-select mb-3" id="admin-role-email" name="email" required>
                            <option value="">Pilih akun</option>
                            <?php foreach ($admins as $admin): ?><option value="<?= esc($admin['email'], 'attr'); ?>"><?= esc($admin['email']); ?> · <?= esc($admin['admin_role'] ?: 'ADMIN'); ?></option><?php endforeach; ?>
                        </select>
                        <label class="form-label" for="admin-role-assignment">Peran</label>
                        <select class="form-select mb-3" id="admin-role-assignment" name="admin_role" required>
                            <option value="">Pilih peran baru</option>
                            <?php foreach ($systemRoles as $roleKey => $roleLabel): ?><option value="<?= esc($roleKey, 'attr'); ?>"><?= esc($roleLabel); ?></option><?php endforeach; ?>
                            <?php foreach ($roles as $role): ?><option value="<?= esc($role['role_key'], 'attr'); ?>"><?= esc($role['label']); ?> (Kustom)</option><?php endforeach; ?>
                        </select>
                        <button class="btn btn-outline-primary" type="submit">Simpan penetapan</button>
                    </form>
                </section>

                <section class="bg-white border rounded-4 p-3 p-lg-4 shadow-sm">
                    <h3 class="h5">Peran yang tersedia</h3>
                    <div class="d-grid gap-2">
                        <?php foreach ($systemRoles as $roleKey => $roleLabel): ?><div class="border rounded-3 p-2"><strong><?= esc($roleKey); ?></strong><span class="small text-muted d-block"><?= esc($roleLabel); ?></span></div><?php endforeach; ?>
                        <?php foreach ($roles as $role): ?><div class="border rounded-3 p-2 d-flex justify-content-between gap-2"><span><strong><?= esc($role['label']); ?></strong><span class="small text-muted d-block"><?= esc($role['role_key']); ?> · <?= esc($role['description']); ?></span></span><a class="btn btn-sm btn-outline-secondary align-self-start" href="/admin/roles?edit=<?= rawurlencode($role['role_key']); ?>">Ubah</a></div><?php endforeach; ?>
                        <?php if (!$roles): ?><p class="small text-muted mb-0">Belum ada peran kustom.</p><?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
        <p class="small text-muted mt-3 mb-0">Perubahan akses aktif setelah admin terkait logout dan login kembali. Aktivitas pembuatan peran dan penetapan admin dicatat.</p>
    <?php endif; ?>
</main>
<?= $this->endSection(); ?>
