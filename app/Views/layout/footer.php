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
                        <img src="/img/pembayaran/cimb.webp" width="43" height="13" />
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
                <div class="d-flex gap-1 align-items-center">
                    <p class="m-0">Pembayaran dilindungi oleh</p>
                    <a href="https://midtrans.com" target="_blank"><img src="/img/logo/midtrans 2.webp" style="width: 100px" /></a>
                </div>
            </div>
            <div class="d-flex gap-2" style="flex: 2;">
                <div style="flex: 1;">
                    <div class="fw-700 fs-3 mb-2"><strong>Lunarea</strong></div>
                    <ul class="footer__left-menu">
                        <!-- <li><a href="/">Beranda</a></li> -->
                        <li><a href="/about">Tentang</a></li>
                        <!-- <li><a href="/all">Produk</a></li> -->
                        <li><a href="<?= session()->get('isLogin') ? "/account" : "/login" ?>"><?= session()->get('isLogin') ? "Akun" : "Masuk" ?></a>
                        </li>
                        <li><a href="/kebijakan-privasi">Kebijakan Privasi</a></li>
                        <li><a href="/syarat-dan-ketentuan">Syarat & Ketentuan</a></li>
                        <li><a href="/faq">FAQ</a></li>
                        <li><a href="/contact">Hubungi Kami</a></li>
                    </ul>
                </div>
                <div style="flex: 1;">
                    <div class="fw-700 fs-3 mb-2"><strong>Ikuti kami</strong></div>
                    <ul class="footer__left-menu">
                        <li>
                            <svg class="socialSvg" viewBox="0 0 17 17">
                                <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217zm0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334z"></path>
                            </svg><a href="https://www.instagram.com/Lunareafurniture.official">Instagram</a>
                        </li>
                        <li class="d-flex align-items-center">
                            <svg class="socialSvg" width="512px" height="512px" viewBox="0 0 512 512" id="icons" xmlns="http://www.w3.org/2000/svg">
                                <path d="M412.19,118.66a109.27,109.27,0,0,1-9.45-5.5,132.87,132.87,0,0,1-24.27-20.62c-18.1-20.71-24.86-41.72-27.35-56.43h.1C349.14,23.9,350,16,350.13,16H267.69V334.78c0,4.28,0,8.51-.18,12.69,0,.52-.05,1-.08,1.56,0,.23,0,.47-.05.71,0,.06,0,.12,0,.18a70,70,0,0,1-35.22,55.56,68.8,68.8,0,0,1-34.11,9c-38.41,0-69.54-31.32-69.54-70s31.13-70,69.54-70a68.9,68.9,0,0,1,21.41,3.39l.1-83.94a153.14,153.14,0,0,0-118,34.52,161.79,161.79,0,0,0-35.3,43.53c-3.48,6-16.61,30.11-18.2,69.24-1,22.21,5.67,45.22,8.85,54.73v.2c2,5.6,9.75,24.71,22.38,40.82A167.53,167.53,0,0,0,115,470.66v-.2l.2.2C155.11,497.78,199.36,496,199.36,496c7.66-.31,33.32,0,62.46-13.81,32.32-15.31,50.72-38.12,50.72-38.12a158.46,158.46,0,0,0,27.64-45.93c7.46-19.61,9.95-43.13,9.95-52.53V176.49c1,.6,14.32,9.41,14.32,9.41s19.19,12.3,49.13,20.31c21.48,5.7,50.42,6.9,50.42,6.9V131.27C453.86,132.37,433.27,129.17,412.19,118.66Z" />
                            </svg><a href="https://www.tiktok.com/@lunareafurnitureofficial">Tiktok</a>
                        </li>
                        <li class="d-flex align-items-center">
                            <svg class="socialSvg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                <path d="M331.5 235.7c2.2 .9 4.2 1.9 6.3 2.8c29.2 14.1 50.6 35.2 61.8 61.4c15.7 36.5 17.2 95.8-30.3 143.2c-36.2 36.2-80.3 52.5-142.6 53h-.3c-70.2-.5-124.1-24.1-160.4-70.2c-32.3-41-48.9-98.1-49.5-169.6V256v-.2C17 184.3 33.6 127.2 65.9 86.2C102.2 40.1 156.2 16.5 226.4 16h.3c70.3 .5 124.9 24 162.3 69.9c18.4 22.7 32 50 40.6 81.7l-40.4 10.8c-7.1-25.8-17.8-47.8-32.2-65.4c-29.2-35.8-73-54.2-130.5-54.6c-57 .5-100.1 18.8-128.2 54.4C72.1 146.1 58.5 194.3 58 256c.5 61.7 14.1 109.9 40.3 143.3c28 35.6 71.2 53.9 128.2 54.4c51.4-.4 85.4-12.6 113.7-40.9c32.3-32.2 31.7-71.8 21.4-95.9c-6.1-14.2-17.1-26-31.9-34.9c-3.7 26.9-11.8 48.3-24.7 64.8c-17.1 21.8-41.4 33.6-72.7 35.3c-23.6 1.3-46.3-4.4-63.9-16c-20.8-13.8-33-34.8-34.3-59.3c-2.5-48.3 35.7-83 95.2-86.4c21.1-1.2 40.9-.3 59.2 2.8c-2.4-14.8-7.3-26.6-14.6-35.2c-10-11.7-25.6-17.7-46.2-17.8H227c-16.6 0-39 4.6-53.3 26.3l-34.4-23.6c19.2-29.1 50.3-45.1 87.8-45.1h.8c62.6 .4 99.9 39.5 103.7 107.7l-.2 .2zm-156 68.8c1.3 25.1 28.4 36.8 54.6 35.3c25.6-1.4 54.6-11.4 59.5-73.2c-13.2-2.9-27.8-4.4-43.4-4.4c-4.8 0-9.6 .1-14.4 .4c-42.9 2.4-57.2 23.2-56.2 41.8l-.1 .1z" />
                            </svg><a href="https://www.threads.net/@lunareafurniture.official">Threads</a>
                        </li>
                        <li class="d-flex align-items-center">
                            <svg class="socialSvg" viewBox="0 0 512 512" height="1.7em" xmlns="http://www.w3.org/2000/svg">
                                <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path>
                            </svg><a href="https://x.com/official14312">Twitter</a>
                        </li>
                        <li class="d-flex align-items-center">
                            <svg class="socialSvg" xmlns="http://www.w3.org/2000/svg" width="24" viewBox="0 0 24 24" height="24">
                                <path d="M16.75,9H13.5V7a1,1,0,0,1,1-1h2V3H14a4,4,0,0,0-4,4V9H8v3h2v9h3.5V12H16Z"></path>
                            </svg><a href="https://www.facebook.com/profile.php?id=61560396845112&locale=id_ID">Facebook</a>
                        </li>
                        <li class="d-flex align-items-center">
                            <svg class="socialSvg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                <path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z"></path>
                            </svg><a href="https://www.youtube.com/@LunareaFurnitureOfficial">Youtube</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div style="flex: 2;">
                <div class="footer__left-menu">
                    <div class="fw-700 fs-3 mb-2"><strong>Kontak kami</strong></div>
                    <p class="mb-1" style="color: #555">
                        Senin - Jumat | 08.00 - 16.00 WIB
                    </p>
                    <p class="mb-1" style="color: #555">
                        Sabtu | 08.00 - 14.00 WIB
                    </p>
                    <a href="https://api.whatsapp.com/send?phone=628112938160&text=Hai%20CS%20*Lunarea*%2C%20saya%20ingin%20membeli%20furniture....." class="mb-1 d-flex gap-2 align-items-center" style="color: #555">
                        <i class="material-icons" style="font-size: 14px;">phone</i>
                        <p class="m-0">08112938160</p>
                    </a>
                    <a href="mailto:cs@lunareafurniture.com" class="mb-1 d-flex gap-2 align-items-center" style="color: #555">
                        <i class="material-icons" style="font-size: 14px;">email</i>
                        <p class="m-0">cs@lunareafurniture.com</p>
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
                            <a href="https://www.tiktok.com/@lunareafurnitureofficial" title="Tokopedia" target="blank"><img src="/img/logo/tiktok.webp" class="marketplace">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-muted pb-3">
            <div class="d-block text-center">
                <div class="mt-4 py-2">
                    <p class="m-0" style="font-size: 10px; letter-spacing: 2px">©<?= date("Y"); ?> | L U N A R E A. All rights reserved.</p>
                </div>
            </div>
        </div>

    </div>
</footer>
<!-- end footer baru -->