<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<main class="konten buyer-help-page">
    <div class="container buyer-help-container">
        <header class="buyer-help-heading">
            <p class="buyer-help-eyebrow">Bantuan akun</p>
            <h1>Penghapusan akun Lunarea</h1>
            <p>Anda dapat mengajukan penghapusan akun dan data terkait melalui aplikasi Lunarea Furniture.</p>
        </header>
        <article class="buyer-help-content">
            <h2>Langkah mengajukan penghapusan</h2>
            <ol>
                <li>Buka aplikasi Lunarea Furniture dan masuk ke akun yang ingin dihapus.</li>
                <li>Buka <strong>Akun → Keamanan akun</strong>.</li>
                <li>Pilih <strong>Ajukan penghapusan akun</strong>, baca keterangannya, lalu konfirmasi.</li>
                <li>Permintaan akan dicatat dan sesi akun akan dikeluarkan dari perangkat.</li>
            </ol>
            <h2>Data yang dihapus dan data yang dapat disimpan</h2>
            <p>Data profil dan akses akun akan dihapus atau dipisahkan dari identitas pribadi saat permintaan diselesaikan. Catatan pesanan, pembayaran, pengiriman, retur, dan catatan lain yang diperlukan untuk kewajiban hukum, keamanan, penyelesaian sengketa, atau rekonsiliasi dapat tetap disimpan sesuai kebutuhan dan ketentuan yang berlaku.</p>
            <p>Permintaan ditangani paling lambat 30 hari setelah verifikasi. Catatan permintaan disimpan selama 180 hari setelah penyelesaian untuk audit, lalu dihapus. Pengajuan tidak membatalkan pesanan atau pembayaran yang sedang berjalan.</p>
            <p>Jika tidak dapat masuk ke aplikasi, pulihkan akses melalui fitur <strong>Lupa kata sandi</strong>, kemudian ajukan dari menu keamanan akun.</p>
            <p><a href="/kebijakan-privasi">Baca Kebijakan Privasi Lunarea</a></p>
        </article>
    </div>
</main>
<?= $this->endSection(); ?>
