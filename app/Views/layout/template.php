<!DOCTYPE html>
<html lang="en">

<head>
    <script defer>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-N2XSVZ4N');
    </script>
    <meta charset="UTF-8">
    <meta name="description" content="<?= isset($metaDeskripsi) ? $metaDeskripsi : 'Lunarea hadir untuk menciptakan ruangan Anda menjadi estetis dengan harga yang terjangkau. Kami menyediakan 4 macam furniture yaitu kursi, rak, meja, dan lemari.'; ?>">
    <meta name="keywords" content="<?= isset($metaKeyword) ? $metaKeyword : 'lunarea furniture,toko furniture'; ?>">
    <meta name="author" content="Lunarea Furniture">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title; ?> | Lunarea Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script> -->
    <!-- <script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script> -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" /> -->
    <!-- icon google -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <?php if ($title == 'Check Out') { ?>
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-aGWfdxs2btRH4xSd"></script>
        <?php if (in_array($user['email'], $emailUji)) { ?>
            <script id="midtrans-script" src="https://api.midtrans.com/v2/assets/js/midtrans-new-3ds.min.js" data-environment="sandbox" data-client-key="SB-Mid-client-aGWfdxs2btRH4xSd" type="text/javascript"></script>
        <?php } else { ?>
            <script id="midtrans-script" src="https://api.midtrans.com/v2/assets/js/midtrans-new-3ds.min.js" data-environment="production" data-client-key="" type="text/javascript"></script>
        <?php } ?>
    <?php } ?>

    <script defer src="https://kit.fontawesome.com/917733e7d4.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="/css/style.css">
    <link rel="icon" href="/logo icon.png">
</head>

<body>
    <!-- <div style="width: 100%; height: 30px" class="d-flex justify-content-center align-items-center bg-danger">
        <p class="m-0 text-light text-sm-center">Sedang dalam pengembangan</p>
    </div> -->
    <div class="toast start-50 translate-middle">
        <div class="toast-body">
            <p>Hello, world! This is a toast message.</p>
            <div class="mt-2 pt-2 border-top">
                <a type="button" class="btn btn-danger btn-sm">Ok</a>
                <button type="button" class="btn btn-secondary btn-sm" onclick="hapusToast()">Batal</button>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <?= $this->include('layout/navbar'); ?>

    <!-- tombol form dan wa -->
    <?php if (!str_contains($title, "Artikel")) { ?>
        <div class="container-melayang d-flex flex-column gap-2 <?= isset($geser_container_melayang) ? 'geser' : ''; ?>">
            <a class="btn-circle" href="/form"><i class="material-icons">insert_comment</i></a>
            <!-- <a class="btn-circle hijau" href="https://api.whatsapp.com/send?phone=628112938160&text=Hallo%20CS%20*Jasmine*%2C%20saya%20ingin%20membeli%20furniture....."><svg viewBox="0 0 32 32" class="whatsapp-ico">
                    <path d=" M19.11 17.205c-.372 0-1.088 1.39-1.518 1.39a.63.63 0 0 1-.315-.1c-.802-.402-1.504-.817-2.163-1.447-.545-.516-1.146-1.29-1.46-1.963a.426.426 0 0 1-.073-.215c0-.33.99-.945.99-1.49 0-.143-.73-2.09-.832-2.335-.143-.372-.214-.487-.6-.487-.187 0-.36-.043-.53-.043-.302 0-.53.115-.746.315-.688.645-1.032 1.318-1.06 2.264v.114c-.015.99.472 1.977 1.017 2.78 1.23 1.82 2.506 3.41 4.554 4.34.616.287 2.035.888 2.722.888.817 0 2.15-.515 2.478-1.318.13-.33.244-.73.244-1.088 0-.058 0-.144-.03-.215-.1-.172-2.434-1.39-2.678-1.39zm-2.908 7.593c-1.747 0-3.48-.53-4.942-1.49L7.793 24.41l1.132-3.337a8.955 8.955 0 0 1-1.72-5.272c0-4.955 4.04-8.995 8.997-8.995S25.2 10.845 25.2 15.8c0 4.958-4.04 8.998-8.998 8.998zm0-19.798c-5.96 0-10.8 4.842-10.8 10.8 0 1.964.53 3.898 1.546 5.574L5 27.176l5.974-1.92a10.807 10.807 0 0 0 16.03-9.455c0-5.958-4.842-10.8-10.802-10.8z" fill-rule="evenodd"></path>
                </svg></a> -->
            <a class="btn-circle hijau" id="btn-wa" href="https://api.whatsapp.com/send?phone=628112938160&text=Hallo%20CS%20*Lunarea*%2C%20saya%20ingin%20membeli%20furniture.....">
                <i class="material-icons text-light">phone</i>
            </a>
        </div>
    <?php } ?>

    <?= $this->renderSection('content'); ?>
    <?= $this->include('layout/footer'); ?>
    <?php if (isset($geser_container_melayang)) { ?>
        <div class="hide-ke-show-block" style="height: 53px; width: 100vw"></div>
    <?php } ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const startTime = Date.now();

            window.addEventListener('beforeunload', function() {
                const endTime = Date.now();
                const duration = (endTime - startTime) / 1000; // duration in seconds

                navigator.sendBeacon('/addtracking', JSON.stringify({
                    durasi: duration,
                    path: window.location.pathname
                }));
            });
        });

        const toastElm = document.querySelector(".toast")
        const toastTeksElm = document.querySelector(".toast p")
        const toastOkElm = document.querySelector(".toast a")
        const toastCloseElm = document.querySelector(".toast button")

        function triggerToast(text, linkAction) {
            toastElm.classList.add("show")
            toastTeksElm.innerHTML = text
            toastOkElm.href = linkAction
            if (!linkAction) {
                toastOkElm.classList.add('d-none');
                toastCloseElm.innerHTML = 'Ok';
            }
        }

        function hapusToast() {
            toastElm.classList.remove("show")
        }

        function bukaDropdown(id) {
            const elementDropdown = document.getElementById(id);
            if (elementDropdown.style.height == "100%") {
                elementDropdown.style.height = "0";
            } else {
                elementDropdown.style.height = "100%";
            }
        }

        const searchBox = document.querySelectorAll(".search-box");
        const searchBoxInput = document.querySelectorAll('.search-input');
        searchBox.forEach((elm, ind) => {
            elm.addEventListener('submit', (e) => {
                e.preventDefault()
                const isinya = searchBoxInput[ind].value.replace(/\s+/g, '-')
                window.location.href = "/find/" + isinya
            })
        })
    </script>
</body>

</html>