<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Data Extractor</title>
    <style>
    body {
        background-color: #121212;
        color: #e0e0e0;
        font-family: 'Consolas', monospace;
        padding: 30px;
        transition: background-color 0.3s, color 0.3s;
    }

    body.light {
        background-color: #fafafa;
        color: #333;
    }

    h1 {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    p,
    h4 {
        margin: 5px 0;
        font-size: 14px;
        color: #bbb;
    }

    button {
        background: transparent;
        border: 1px solid #ff5c5c;
        color: #ff5c5c;
        padding: 8px 16px;
        font-size: 14px;
        margin: 5px 5px 10px 0;
        cursor: pointer;
        transition: 0.2s;
    }

    button:hover {
        background: #ff5c5c;
        color: #121212;
    }

    body.light button {
        border-color: #007bff;
        color: #007bff;
    }

    body.light button:hover {
        background: #007bff;
        color: #fafafa;
    }

    #log {
        margin-top: 20px;
        background: #1e1e1e;
        border: 1px solid #333;
        padding: 15px;
        height: 300px;
        overflow-y: auto;
        font-size: 13px;
        transition: background 0.3s, border-color 0.3s;
    }

    body.light #log {
        background: #fff;
        border-color: #ccc;
        color: #333;
    }

    li {
        margin-bottom: 8px;
        color: #ccc;
    }

    body.light li {
        color: #555;
    }

    .highlight {
        color: #4cd964;
    }

    .error {
        color: #ff4f4f;
    }

    .section {
        margin-top: 30px;
    }

    .loader {
        display: inline-block;
        overflow: hidden;
        white-space: nowrap;
        border-right: 2px solid #89ffb5;
        animation: typing 2s steps(30, end), blink 0.8s step-end infinite alternate;
        max-width: 100%;
    }

    @keyframes typing {
        from {
            width: 0
        }

        to {
            width: 100%
        }
    }

    @keyframes blink {
        50% {
            border-color: transparent;
        }
    }

    /* Progress Bar */
    .progress-container {
        width: 100%;
        background: #333;
        border-radius: 4px;
        overflow: hidden;
        margin: 10px 0;
    }

    #progress-bar {
        width: 0%;
        height: 8px;
        background: #4cd964;
        transition: width 0.3s;
    }

    /* Stats Panel */
    .stats span {
        display: inline-block;
        margin-right: 15px;
        font-size: 14px;
        color: #bbb;
    }

    body.light .stats span {
        color: #555;
    }

    /* Filter Input */
    #filter-log {
        padding: 5px;
        margin-right: 10px;
        border: 1px solid #555;
        background: transparent;
        color: #e0e0e0;
        transition: border-color 0.3s, background 0.3s, color 0.3s;
    }

    body.light #filter-log {
        border-color: #ccc;
        background: #fff;
        color: #333;
    }
    </style>
</head>

<body>
    <h1>Data Extraction</h1>
    <p>Sistem sedang mengekstrak data wilayah dari server eksternal.</p>

    <div class="section">
        <h4>Pagination: <span class="highlight" id="pagination">0</span> / 350</h4>
        <div class="progress-container">
            <div id="progress-bar"></div>
        </div>
        <button id="btn-toggle">Pause</button>
        <button onclick="stopProcess()">Hentikan proses</button>
        <button id="btn-theme">☀️ Light</button>
    </div>

    <div class="section stats">
        <span>Processed: <strong id="stat-count-prov">0</strong> kecamatan</span>
        <span>Saved: <strong id="stat-count-kel">0</strong> kelurahan</span>
        <span>Elapsed: <strong id="stat-elapsed">00:00</strong></span>
    </div>

    <div class="section" id="status-container">
        <p><strong>Target Kecamatan:</strong> <span id="curr-prov">-</span></p>
        <p><strong>Status:</strong> <span id="curr-ket">Menunggu...</span></p>
        <p><strong>Terakhir berhasil:</strong> <span id="last-pagination">-</span></p>
        <p id="terminal-typing" class="loader">Connecting to server...</p>
    </div>

    <div class="section">
        <input type="text" id="filter-log" placeholder="Cari log..." />
        <button id="copy-log">Copy Log</button>
        <button id="download-log">Download Log</button>
    </div>

    <ul id="log"></ul>

    <script>
    const provinsiElm = document.getElementById('log');
    const currProvElm = document.getElementById('curr-prov');
    const currKetElm = document.getElementById('curr-ket');
    const paginationElm = document.getElementById('pagination');
    const lastPaginationElm = document.getElementById('last-pagination');
    const terminalTyping = document.getElementById('terminal-typing');
    const progressBar = document.getElementById('progress-bar');
    const btnToggle = document.getElementById('btn-toggle');
    const btnTheme = document.getElementById('btn-theme');
    const filterLog = document.getElementById('filter-log');
    const btnCopyLog = document.getElementById('copy-log');
    const btnDownloadLog = document.getElementById('download-log');
    const statCountProv = document.getElementById('stat-count-prov');
    const statCountKel = document.getElementById('stat-count-kel');
    const statElapsed = document.getElementById('stat-elapsed');

    let pagination = 1;
    let berhenti = false;
    let startTime = Date.now();
    let totalKel = 0;

    function stopProcess() {
        berhenti = true;
        currKetElm.textContent = 'Proses dihentikan.';
        terminalTyping.textContent = 'Connection terminated.';
        terminalTyping.classList.remove('loader');
    }

    btnToggle.addEventListener('click', () => {
        berhenti = !berhenti;
        btnToggle.textContent = berhenti ? 'Resume' : 'Pause';
        currKetElm.textContent = berhenti ? 'Proses dijeda.' : 'Melanjutkan proses...';
        if (!berhenti) fetchProvinsi();
    });

    btnTheme.addEventListener('click', () => {
        document.body.classList.toggle('light');
        btnTheme.textContent = document.body.classList.contains('light') ? '🌙 Dark' : '☀️ Light';
    });

    filterLog.addEventListener('input', e => {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll('#log li').forEach(li => {
            li.style.display = li.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    btnCopyLog.addEventListener('click', () => {
        const text = Array.from(document.querySelectorAll('#log li'))
            .map(li => li.textContent).join('\n');
        navigator.clipboard.writeText(text);
    });

    btnDownloadLog.addEventListener('click', () => {
        const text = Array.from(document.querySelectorAll('#log li'))
            .map(li => li.textContent).join('\n');
        const blob = new Blob([text], {
            type: 'text/plain'
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'extract-log.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });

    function updateProgress(page, total) {
        const pct = Math.min(100, (page / total) * 100);
        progressBar.style.width = pct + '%';
    }

    function updateStats(newKel) {
        statCountProv.textContent = pagination - 1;
        totalKel += newKel;
        statCountKel.textContent = totalKel;
    }

    setInterval(() => {
        const diff = Date.now() - startTime;
        const m = String(Math.floor(diff / 60000)).padStart(2, '0');
        const s = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
        statElapsed.textContent = `${m}:${s}`;
    }, 1000);

    function startTypingSequence() {
        const steps = [
            "Connecting to server...",
            "Authorizing access...",
            "Accessing database...",
            "Extracting wilayah data...",
            "Fetching kelurahan records..."
        ];
        let i = 0;
        const interval = setInterval(() => {
            terminalTyping.textContent = steps[i];
            i++;
            if (i >= steps.length) {
                clearInterval(interval);
                terminalTyping.textContent = "Extraction in progress...";
            }
        }, 1800);
    }

    async function fetchProvinsi() {
        startTypingSequence();

        for (let i = 0; i < 350; i++) {
            if (berhenti) break;

            lastPaginationElm.textContent = (pagination - 1) === 0 ? "-" : pagination - 1;
            paginationElm.textContent = pagination;
            updateProgress(pagination, 350);

            try {
                const response = await fetch(`/ro/get-kecamatan/${pagination}`);
                const data = await response.json();

                for (let j = 0; j < data.length; j++) {
                    const prov = data[j];
                    if (prov.label.toLowerCase() === "barat") continue;
                    // if (prov.label.toLowerCase() === "Gu") continue;

                    currProvElm.textContent = prov.label;
                    currKetElm.textContent = "Sedang mengambil data...";

                    const fetchKab = await fetch(
                        `/ro/kelurahan/${encodeURIComponent(prov.label.replace(/'/g, ""))}`, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                provinsi_id: prov.provinsi_id,
                                kabupaten_id: prov.kabupaten_id,
                                kecamatan_id: prov.id,
                            }),
                        }
                    );
                    const kabupaten = await fetchKab.json();

                    provinsiElm.innerHTML += `
              <li>
                <span class="highlight">${prov.label}</span> (${prov.id}) → ${kabupaten.panjang} kelurahan disimpan
              </li>
            `;
                    provinsiElm.scrollTop = provinsiElm.scrollHeight;
                    updateStats(kabupaten.panjang);
                }
            } catch (error) {
                provinsiElm.innerHTML += `<li class="error">Error: ${error.message}</li>`;
                provinsiElm.scrollTop = provinsiElm.scrollHeight;
                console.error("Error fetching data:", error);
                stopProcess();
                return;
            }

            pagination++;
        }

        currKetElm.textContent = "Selesai.";
        terminalTyping.textContent = "Extraction completed.";
        terminalTyping.classList.remove("loader");
    }

    fetchProvinsi();
    </script>
</body>

</html>