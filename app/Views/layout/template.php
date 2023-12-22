<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title; ?> | Jasmine Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" /> -->
    <!-- icon google -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-lDi-03j_XL3PVN0_">
    </script>

    <script src="https://kit.fontawesome.com/917733e7d4.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="/css/style.css">
</head>

<body>
    <div class="toast z-3 start-50 translate-middle">
        <div class="toast-body">
            <p>Hello, world! This is a toast message.</p>
            <div class="mt-2 pt-2 border-top">
                <a type="button" class="btn btn-danger btn-sm">Ok</a>
                <button type="button" class="btn btn-secondary btn-sm" onclick="hapusToast()">Batal</button>
            </div>
        </div>
    </div>
    <?= $this->include('layout/navbar'); ?>
    <?= $this->renderSection('content'); ?>
    <?= $this->include('layout/footer'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous">
    </script>
    <script>
        // const scrollKategoriElm = document.querySelectorAll(".scroll-kategori");
        // const scrollKategoriContainer = document.querySelector('.container-kategori');
        const toastElm = document.querySelector(".toast")
        const toastTeksElm = document.querySelector(".toast p")
        const toastOkElm = document.querySelector(".toast a")

        // if (scrollKategoriElm.length > 0) {
        //     scrollKategoriElm[0].onclick = function() {
        //         let i = 1
        //         let cek = false
        //         let intervalId = setInterval(() => {
        //             scrollKategoriContainer.scrollLeft -= 0.35 * i;
        //             if (i >= 26) cek = true
        //             if (!cek) i++;
        //             else i--;
        //         }, 10);
        //         setTimeout(() => {
        //             clearInterval(intervalId);
        //         }, 500);
        //     };
        //     scrollKategoriElm[1].onclick = function() {
        //         let i = 1
        //         let cek = false
        //         let intervalId = setInterval(() => {
        //             scrollKategoriContainer.scrollLeft += 0.35 * i;
        //             if (i >= 26) cek = true
        //             if (!cek) i++;
        //             else i--;
        //         }, 10);
        //         setTimeout(() => {
        //             clearInterval(intervalId);
        //         }, 500);
        //     };
        // }

        function triggerToast(text, linkAction) {
            toastElm.classList.add("show")
            toastTeksElm.innerHTML = text
            toastOkElm.href = linkAction
        }

        function hapusToast() {
            toastElm.classList.remove("show")
        }

        const btnCheckoutElm = document.getElementById('btn-checkout')
        const formCheckoutElm = document.getElementById('form-checkout')
        const formCheckoutNama = document.querySelector('#form-checkout input[name="nama"]')
        const formCheckoutAlamat = document.querySelector('#form-checkout input[name="alamat"]')
        const formCheckoutEmail = document.querySelector('#form-checkout input[name="email"]')
        const formCheckoutNoHp = document.querySelector('#form-checkout input[name="phone"]')
        const formCheckoutPaket = document.querySelector('#form-checkout select[name="paket"]')
        const formCheckout = document.querySelectorAll('#form-checkout .form-control')

        if (btnCheckoutElm) {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
            btnCheckoutElm.addEventListener("click", () => {
                if (formCheckoutNama.value && formCheckoutAlamat.value && formCheckoutEmail.value &&
                    formCheckoutNoHp.value && formCheckoutPaket.value > 0) {

                    formCheckoutNama.classList.remove("is-invalid")
                    formCheckoutAlamat.classList.remove("is-invalid")
                    formCheckoutEmail.classList.remove("is-invalid")
                    formCheckoutNoHp.classList.remove("is-invalid")
                    formCheckoutPaket.classList.remove("is-invalid")

                    btnCheckoutElm.innerHTML = "Loading"
                    const data = {
                        nama: formCheckoutNama.value,
                        alamat: formCheckoutAlamat.value,
                        email: formCheckoutEmail.value,
                        phone: formCheckoutNoHp.value,
                        paket: formCheckoutPaket.value
                    }
                    console.log(data)
                    async function getTokenMditrans() {
                        var formBody = [];
                        for (var property in data) {
                            var encodedKey = encodeURIComponent(property);
                            var encodedValue = encodeURIComponent(data[property]);
                            formBody.push(encodedKey + "=" + encodedValue);
                        }
                        formBody = formBody.join("&");

                        const response = await fetch("actioncheckout", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify(data),
                        });
                        const snaptoken = await response.json();
                        console.log(snaptoken);
                        btnCheckoutElm.innerHTML = "Pesan"
                        window.snap.pay(snaptoken.snapToken);
                    }
                    getTokenMditrans()
                } else {
                    if (!formCheckoutNama.value) formCheckoutNama.classList.add("is-invalid")
                    if (!formCheckoutAlamat.value) formCheckoutAlamat.classList.add("is-invalid")
                    if (!formCheckoutEmail.value) formCheckoutEmail.classList.add("is-invalid")
                    if (!formCheckoutNoHp.value) formCheckoutNoHp.classList.add("is-invalid")
                    if (formCheckoutPaket.value < 0) formCheckoutPaket.classList.add("is-invalid")
                }
            })
        }

        function bukaDropdown(id) {
            const elementDropdown = document.getElementById(id);
            if (elementDropdown.style.height == "100%") {
                elementDropdown.style.height = "0";
            } else {
                elementDropdown.style.height = "100%";
            }
        }

        const searchBox = document.getElementById("search-box");
        const searchBoxInput = document.querySelector('#search-box input')
        searchBox.addEventListener("submit", (e) => {
            e.preventDefault()
            const isinya = searchBoxInput.value
            window.location.href = "/productNama/" + isinya
        })
    </script>
</body>

</html>