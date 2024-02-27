<!-- footer baru -->
<footer class="footer footer-transparent d-print-non">
    <div class="container">
        <div class="row pt-5">
            <div class="col-12 col-md-4 mb-3">
                <div class="fw-700 fs-3 mb-2"><strong>Metode Pembayaran</strong></div>
                <div class="footer__partner__payment">
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/bca.png" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/bni.webp" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/bri.png" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/mandiri.png" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/permatabank.png" />
                    </div>
                    <!-- <div class="footer__partner__img">
                        <img src="/img/pembayaran/dob.png" />
                    </div> -->
                    <!-- <div class="footer__partner__img">
                        <img src="/img/pembayaran/klikbca.png" />
                    </div> -->
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/klikpay.png" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/mastercard.png" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/visa.png" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/jcb.png" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/amex.png" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/qris.png" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/shopeepay.png" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/gopay.png" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/alfamart.png" />
                    </div>
                    <div class="footer__partner__img">
                        <img src="/img/pembayaran/indomaret.png" />
                    </div>
                </div>
            </div>
            <div class="col-8 col-md-3 mb-3">
                <div class="fw-700 fs-3 mb-2"><strong>Jasmine Furniture</strong></div>
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
            <div class="col-4 col-md-2 mb-3">
                <div class="fw-700 fs-3 mb-2"><strong>Ikuti Kami</strong></div>
                <ul class="footer__left-menu">
                    <li><a href="https://www.instagram.com/jasminefurniture.official/?igsh=MTZyZ25mMXp5MHJlMw%3D%3D">Instagram</a></li>
                    <li><a href="https://www.tiktok.com/@jasminefurnitureofficial?_t=8joAv1G8zwY&_r=1">Tiktok</a></li>
                    <li><a href="https://www.youtube.com/@JasmineFurnitureOfficial">Youtube</a></li>
                </ul>
            </div>

            <div class="col-12 col-md-3">
                <div class="footer__service-customer">
                    <div class="mb-1">
                        <strong class="title">Kami siap melayani anda</strong>
                    </div>
                    <p class="mb-1" style="color: #555">
                        Mulai pukul 08.00 - 16.00 WIB<br />( Hari Senin - Sabtu )
                    </p>
                    <a href="https://wa.me/+628112938160">
                        <p class="mb-1" style="color: #555">
                            <span class="me-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2">
                                    </path>
                                </svg></span>08112938160
                        </p>
                    </a>
                    <a href="mailto:infojasmine@jasminefurniture.co.id">
                        <p class="mb-1" style="color: #555">
                            <span class="me-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                    <polyline points="3 7 12 13 21 7"></polyline>
                                </svg></span>infojasmine@jasminefurniture.co.id
                        </p>
                    </a>

                    <!-- <p class="mb-1" style="color: #555">
                        <a href="https://www.instagram.com/melodyfurniture/" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="#555" fill="none" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <rect x="4" y="4" width="16" height="16" rx="4"></rect>
                                <circle cx="12" cy="12" r="3"></circle>
                                <line x1="16.5" y1="7.5" x2="16.5" y2="7.501"></line>
                            </svg></a>
                        <a href="https://www.facebook.com/Melody-Furniture-110447617409510" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="#555" fill="none" stroke-linecap="round" stroke-linejoin="round" class="icon">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M7 10v4h3v7h4v-7h3l1 -4h-4v-2a1 1 0 0 1 1 -1h3v-4h-3a5 5 0 0 0 -5 5v2h-3">
                                </path>
                            </svg></a>
                        <a href="https://www.instagram.com/melodyfurniture/" target="_blank"><svg fill="#555" stroke-linecap="round" stroke-width="12" stroke="#555" width="24" height="24" stroke-linejoin="round" viewBox="0 0 256 256" xmlns="http://www.w3.org/2000/svg">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M224,80a52.059,52.059,0,0,1-52-52,4.0002,4.0002,0,0,0-4-4H128a4.0002,4.0002,0,0,0-4,4V156a24,24,0,1,1-34.28418-21.69238,3.99957,3.99957,0,0,0,2.28369-3.61279L92,89.05569a3.99948,3.99948,0,0,0-4.70117-3.938A72.00522,72.00522,0,1,0,172,156l-.00049-42.56348A99.27749,99.27749,0,0,0,224,128a4.0002,4.0002,0,0,0,4-4V84A4.0002,4.0002,0,0,0,224,80Zm-4,39.915a91.24721,91.24721,0,0,1-49.66455-17.1792,4.00019,4.00019,0,0,0-6.33594,3.24707L164,156A64,64,0,1,1,84,94.01223l-.00049,34.271A32.00156,32.00156,0,1,0,132,156V32h32.13184A60.09757,60.09757,0,0,0,220,87.86819Z" />
                            </svg></a>
                    </p> -->
                    <div class="mt-2">
                        <p class="mb-1">
                            kami juga tersedia di
                        </p>
                        <div>
                            <a href="#" title="Shopee" target="blank"><img src="/img/logo/shopee.png" class="marketplace">
                            </a>
                            <a href="#" title="Tokopedia" target="blank"><img src="/img/logo/tokopedia.png" class="marketplace">
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
                    <a href="https://midtrans.com" target="_blank"><img src="https://melodyfurniture.co.id/assets/v2/img/payments/midtrans_logo3.png" style="width: 140px" /></a>
                </div>
            </div>
        </div>
    </div>
    <div class="container py-3 border-top border-secondary">
        <p class="text-center mb-2">
            <small>Hak Cipta © by Creative Jasmine Furniture ® All Right
                Reserved</small>
        </p>
    </div>
</footer>
<!-- end footer baru -->