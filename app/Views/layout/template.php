<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title; ?> | Jasmine Furniture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous">
    </script>
    <script>
    const scrollKategoriElm = document.querySelectorAll(".scroll-kategori");
    const scrollKategoriContainer = document.querySelector('.container-kategori');
    const toastElm = document.querySelector(".toast")
    const toastTeksElm = document.querySelector(".toast p")
    const toastOkElm = document.querySelector(".toast a")

    scrollKategoriElm[0].onclick = function() {
        let i = 1
        let cek = false
        let intervalId = setInterval(() => {
            scrollKategoriContainer.scrollLeft -= 0.35 * i;
            if (i >= 26) cek = true
            if (!cek) i++;
            else i--;
        }, 10);
        setTimeout(() => {
            clearInterval(intervalId);
        }, 500);
    };
    scrollKategoriElm[1].onclick = function() {
        let i = 1
        let cek = false
        let intervalId = setInterval(() => {
            scrollKategoriContainer.scrollLeft += 0.35 * i;
            if (i >= 26) cek = true
            if (!cek) i++;
            else i--;
        }, 10);
        setTimeout(() => {
            clearInterval(intervalId);
        }, 500);
    };

    function triggerToast(text, linkAction) {
        toastElm.classList.add("show")
        toastTeksElm.innerHTML = text
        toastOkElm.href = linkAction
    }

    function hapusToast() {
        toastElm.classList.remove("show")
    }


    const elmformsearch = document.getElementById("search-box");
    const elminputsearch = document.getElementById("search-input");
    elmformsearch.addEventListener("submit", (e) => {
        e.preventDefault();
        window.location.href = "/productNama/" + elminputsearch.value
        // async function cariProduk() {
        //     var bodynya = {
        //         cari: elminputsearch.value,
        //         apalah: "cek",
        //     };
        //     var formBody = [];
        //     for (var property in bodynya) {
        //         var encodedKey = encodeURIComponent(property);
        //         var encodedValue = encodeURIComponent(bodynya[property]);
        //         formBody.push(encodedKey + "=" + encodedValue);
        //     }
        //     formBody = formBody.join("&");

        //     const response = await fetch("", {
        //         method: "POST",
        //         headers: {
        //             "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
        //         },
        //         body: formBody,
        //     });
        //     const barang = await response.json();
        //     console.log(barang);

        //     elmContainer.innerHTML = "";
        //     if (barang.data.length > 0) {
        //         barang.data.forEach((element) => {
        //             const cardElm = document.createElement("a");
        //             cardElm.classList.add("card-produk");
        //             const divElm = document.createElement("div");
        //             const imgElm = document.createElement("img");
        //             const blob = new Blob([new Uint8Array(element.gambar.data)], {
        //                 type: "image/webp",
        //             });
        //             const blobUrl = URL.createObjectURL(blob);
        //             imgElm.src = blobUrl;
        //             const h3Elm = document.createElement("h3");
        //             h3Elm.classList.add("mb-0");
        //             h3Elm.innerHTML = element.nama;

        //             divElm.appendChild(imgElm);
        //             cardElm.appendChild(divElm);
        //             cardElm.appendChild(h3Elm);

        //             cardElm.addEventListener("click", () => {
        //                 window.location.href = "/detailproduk.html?id=" + element.id;
        //             });

        //             elmContainer.appendChild(cardElm);
        //         });
        //     } else {
        //         const pElm = document.createElement("p");
        //         pElm.innerText = "Data tidak ditemukan";
        //         pElm.classList.add("text-center");
        //         elmContainer.appendChild(pElm);
        //     }
        // }
        // cariProduk();
    });
    </script>
</body>

</html>