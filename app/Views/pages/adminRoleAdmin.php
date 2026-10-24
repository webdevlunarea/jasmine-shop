<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<main class="konten admin-module-page">
 <div class="container admin-role-page">
    <?= view('partials/admin_page_heading', ['adminHeadingTitle'=>'Peran & hak akses', 'adminHeadingGroup'=>'Pengaturan keamanan', 'adminHeadingDescription'=>'Buat peran sesuai tugas, pilih izin yang diperlukan, lalu tetapkan ke akun admin.'], ['saveData'=>false]); ?>

    <?php if (!empty($msg)): ?><div class="alert alert-info" role="status"><?= esc($msg); ?></div><?php endif; ?>

    <?php if (!$ready): ?>
        <div class="alert alert-warning" role="alert"><strong>Setup database belum selesai.</strong> Jalankan SQL RBAC dari <code>docs/security/admin-rbac.sql</code> di database Lunarea, lalu logout dan login kembali.</div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-12 col-xl-7">
                <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div><h3 class="h5 mb-1"><?= $selectedRole ? 'Ubah peran kustom' : 'Buat peran kustom'; ?></h3><p class="small text-muted mb-0">Izin kritis seperti pengelolaan peran dan pemeliharaan sistem tetap khusus DEV.</p></div>
                        <?php if ($selectedRole): ?><a class="btn orders-secondary" href="/admin/roles">Peran baru</a><?php endif; ?>
                    </div>
                    <form method="post" action="/admin/roles/save">
                        <?= csrf_field(); ?>
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
                                        <label class="admin-role-permission d-flex align-items-start gap-2 border rounded-3 p-3 h-100">
                                            <input class="form-check-input mt-1" type="checkbox" name="permissions[]" value="<?= esc($permission, 'attr'); ?>" <?= in_array($permission, $selectedPermissions, true) ? 'checked' : ''; ?>>
                                            <span><strong class="small d-block"><?= esc($label); ?></strong><code class="small text-muted"><?= esc($permission); ?></code></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>
                        <button class="btn btn-primary1 mt-3" type="submit"><?= $selectedRole ? 'Simpan perubahan' : 'Buat peran'; ?></button>
                    </form>
                </section>
            </div>

            <div class="col-12 col-xl-5">
                <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4">
                    <h3 class="h5 mb-1">Tetapkan peran admin</h3>
                    <p class="small text-muted">Hanya akun dengan role admin website yang tersedia.</p>
                    <form method="post" action="/admin/roles/assign">
                        <?= csrf_field(); ?>
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
                        <button class="btn orders-secondary" type="submit">Simpan penetapan</button>
                    </form>
                </section>

                <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
                    <h3 class="h5">Peran yang tersedia</h3>
                    <div class="d-grid gap-2">
                        <?php foreach ($systemRoles as $roleKey => $roleLabel): ?><div class="border rounded-3 p-2"><strong><?= esc($roleKey); ?></strong><span class="small text-muted d-block"><?= esc($roleLabel); ?></span></div><?php endforeach; ?>
                        <?php foreach ($roles as $role): ?><div class="border rounded-3 p-2 d-flex justify-content-between gap-2"><span><strong><?= esc($role['label']); ?></strong><span class="small text-muted d-block"><?= esc($role['role_key']); ?> · <?= esc($role['description']); ?></span></span><a class="btn orders-secondary align-self-start" href="/admin/roles?edit=<?= rawurlencode($role['role_key']); ?>">Ubah</a></div><?php endforeach; ?>
                        <?php if (!$roles): ?><p class="small text-muted mb-0">Belum ada peran kustom.</p><?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
        <p class="small text-muted mt-3 mb-0">Perubahan akses aktif setelah admin terkait logout dan login kembali. Aktivitas pembuatan peran dan penetapan admin dicatat.</p>
        <details class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mt-3">
            <summary class="fw-semibold">Riwayat perubahan peran dan izin</summary>
            <?php if (!$roleLogs): ?><p class="small text-muted mt-3 mb-0">Belum ada perubahan peran yang tercatat.</p><?php else: ?>
                <div class="table-responsive mt-3"><table class="table table-sm align-middle mb-0"><thead><tr><th>Waktu (UTC)</th><th>Pelaku</th><th>Peristiwa</th><th>Peran</th><th>Akun terkait</th><th>Rincian</th></tr></thead><tbody>
                    <?php foreach ($roleLogs as $log): ?><tr><td><?= esc($log['created_at']); ?></td><td><?= esc($log['actor_email']); ?></td><td><?= esc($log['action']); ?></td><td><?= esc($log['role_key']); ?></td><td><?= esc($log['target_email'] ?? '—'); ?></td><td><code><?= esc($log['details']); ?></code></td></tr><?php endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
        </details>
    <?php endif; ?>
 </div>
</main>
<?= $this->endSection(); ?>
