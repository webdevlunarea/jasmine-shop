<!-- footer baru -->
<footer class="footer footer-transparent d-print-non">
    <div class="container">
        <div class="pt-5 gap-2 baris-ke-kolom">
            <div style="flex: 2;">
                <div class="fw-700 fs-3 mb-2"><strong>Metode pembayaran</strong></div>
                <div class="footer__partner__payment">
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/bca.webp" width="43.4" height="13.78" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/bni.webp" width="43" height="16" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/bri.webp" width="43" height="10" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/mandiri.webp" width="43" height="13" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/permatabank.webp" width="43" height="11" />
                    </div>
                    <!--<div class="footer__partner__img">-->
                    <!--    <img src="/img/pembayaran/mastercard.webp" width="30" height="23" />-->
                    <!--</div>-->
                    <!--<div class="footer__partner__img">-->
                    <!--    <img src="/img/pembayaran/visa.webp" width="43" height="14"/>-->
                    <!--</div>-->
                    <!--<div class="footer__partner__img">-->
                    <!--    <img src="/img/pembayaran/qris.webp" width="43" height="16"/>-->
                    <!--</div>-->
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/shopeepay.webp" width="43" height="20" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/gopay.webp" width="43" height="9" />
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2" style="flex: 2;">
                <div style="flex: 1;">
                    <div class="fw-700 fs-3 mb-2"><strong>Lunarea</strong></div>
                    <ul class="footer__left-menu">
                        <li><a href="/">Beranda</a></li>
                        <li><a href="/about">Tentang</a></li>
                        <li><a href="/all">Produk</a></li>
                        <li><a href="<?= session()->get('isLogin') ? "/account" : "/login" ?>"><?= session()->get('isLogin') ? "Akun" : "Masuk" ?></a>
                        </li>
                        <li><a href="/kebijakan-privasi">Kebijakan Privasi</a></li>
                        <li><a href="/syarat-dan-ketentuan">Syarat & Ketentuan</a></li>
                        <li><a href="/faq">FAQ</a></li>
                    </ul>
                </div>
                <div style="flex: 1;">
                    <div class="fw-700 fs-3 mb-2"><strong>Ikuti kami</strong></div>
                    <ul class="footer__left-menu">
                        <li><a href="https://www.instagram.com/Lunareafurniture.official">Instagram</a></li>
                        <li><a href="https://www.tiktok.com/@lunareafurnitureofficial">Tiktok</a></li>
                        <li><a href="https://www.youtube.com/@LunareaFurnitureOfficial">Youtube</a></li>
                    </ul>
                </div>
            </div>
            <div style="flex: 2;">
                <div class="footer__service-customer">
                    <div class="fw-700 fs-3 mb-2"><strong>Kontak kami</strong></div>
                    <p class="mb-1" style="color: #555">
                        Senin - Sabtu | 08.00 - 16.00 WIB
                    </p>
                    <a href="https://wa.me/+628112938160">
                        <div class="mb-1 d-flex gap-2 align-items-center" style="color: #555">
                            <i class="material-icons" style="color: #555;">phone</i>
                            <a class="m-0" style="color: #555">08112938160</a>
                        </div>
                    </a>
                    <a href="mailto:cs@lunareafurniture.com">
                        <div class="mb-1 d-flex gap-2 align-items-center" style="color: #555">
                            <i class="material-icons" style="color: #555;">email</i>
                            <a class="m-0" style="color: #555">cs@Lunareafurniture.co.id</a>
                        </div>
                    </a>

                    <div class="mt-2">
                        <p class="mb-1">
                            tersedia juga di
                        </p>
                        <div>
                            <a href="https://shopee.co.id/lunareafurniture_" title="Shopee" target="blank"><img src="/img/logo/shopee.webp" class="marketplace">
                            </a>
                            <a href="https://www.tokopedia.com/Lunareafurnitureofc" title="Tokopedia" target="blank"><img src="/img/logo/tokopedia.webp" class="marketplace">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-muted">
            <div class="d-block text-center">
                <div class="mt-4 mb-3 p-2 pe-0 ps-0">
                    <span class="fw-700 mr-2">Pembayaran dilindungi</span>:
                    <a href="https://midtrans.com" target="_blank"><img src="/img/logo/midtrans.webp" style="width: 140px" /></a>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- end footer baru -->