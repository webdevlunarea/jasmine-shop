<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <table id="toExcel" class="uitable">
        <thead>
            <tr>
                <th>Id</th>
                <th>Title</th>
                <th>Description</th>
                <th>Availability</th>
                <th>Link</th>
                <th>Image Link</th>
                <th>Price</th>
                <th>Color</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produk as $p) {
                $stok = explode(',', $p['stok']);
                $stokTotal = 0;
                foreach ($stok as $s) {
                    $stokTotal += (int)$s;
                }
            ?>
                <tr>
                    <td><?= $p['id']; ?></td>
                    <td>Lunarea <?= $p['nama']; ?></td>
                    <td><?= $p['deskripsi_nonhtml']; ?></td>
                    <td><?= $stokTotal > 0 ? 'in_stock' : 'out_of_stock'; ?></td>
                    <td>https://lunareafurniture.com/product/<?= $p['path']; ?></td>
                    <td>https://lunareafurniture.com/imgpic/<?= $p['id']; ?></td>
                    <td><?= $p['harga']; ?> IDR</td>
                    <td><?= implode("/", json_decode($p['varian'], true)); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <a id="btnDownload">EXCEL</a>
    <script>
        var dataType = "application/vnd.ms-excel";
        var tableSelect = document.getElementById("toExcel");
        var btnDownload = document.getElementById("btnDownload");
        var tableHTML = tableSelect.outerHTML.replace(/ /g, "%20");

        // Specify file name
        const filename = "Tabel Produk Lunarea.xls";

        // if (navigator.msSaveOrOpenBlob) {
        //     var blob = new Blob(["\ufeff", tableHTML], {
        //         type: dataType,
        //     });
        //     navigator.msSaveOrOpenBlob(blob, filename);
        // } else {
        btnDownload.href = "data:" + dataType + ", " + tableHTML;
        btnDownload.download = filename;
        // }
    </script>
</body>

</html>