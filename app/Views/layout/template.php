<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title; ?> | Jual.an</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>
    <?= $this->include('layout/navbar'); ?>
    <?= $this->renderSection('content'); ?>
    <?= $this->include('layout/footer'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script>
        const scrollKategoriElm = document.querySelectorAll(".scroll-kategori");
        const scrollKategoriContainer = document.querySelector('.container-kategori');

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
    </script>
</body>

</html>