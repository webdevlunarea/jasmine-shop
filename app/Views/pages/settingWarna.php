<?= $this->extend('layout/template'); ?>
<?= $this->section('content'); ?>
<style>
.theme-preview {
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid var(--admin-border);
    background: var(--theme-soft);
}

.theme-preview-hero {
    min-height: 180px;
    background:
        radial-gradient(circle at 12% 18%, var(--theme-accent) 0 10%, transparent 11%),
        linear-gradient(135deg, var(--theme-primary), var(--theme-sidebar));
    color: white;
}

.theme-preview-card {
    border-radius: 14px;
    background: white;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 14px 34px rgba(31, 42, 68, 0.12);
}

.color-field {
    border: 1px solid var(--admin-border);
    border-radius: 12px;
    padding: 14px;
    background: #fff;
}

.color-field input[type="color"] {
    width: 52px;
    height: 42px;
    padding: 4px;
    border-radius: 10px;
}

.preset-btn {
    border: 1px solid var(--admin-border);
    border-radius: 12px;
    background: white;
    padding: 12px;
    text-align: left;
}

.preset-swatches {
    display: flex;
    gap: 5px;
}

.preset-swatches span {
    width: 24px;
    height: 24px;
    border-radius: 999px;
    border: 1px solid rgba(0, 0, 0, 0.12);
}
</style>

<?php
$presets = [
    [
        'name' => 'Aizome Sakura',
        'desc' => 'Indigo Jepang, sakura, dan ivory hangat.',
        'colors' => [
            'primary' => '#243B6B',
            'primaryHover' => '#1A2C52',
            'soft' => '#FAF7F2',
            'soft1' => '#F3E8DF',
            'soft2' => '#E7D7C9',
            'accent' => '#D98C9A',
            'background' => '#F7F0E8',
            'sidebar' => '#1F2A44',
        ],
    ],
    [
        'name' => 'Kurenai',
        'desc' => 'Merah klasik Jepang dengan krem lembut.',
        'colors' => [
            'primary' => '#9F353A',
            'primaryHover' => '#7D292D',
            'soft' => '#FFF4EF',
            'soft1' => '#F8DFD8',
            'soft2' => '#EBC2BA',
            'accent' => '#D7A98C',
            'background' => '#FFF8F3',
            'sidebar' => '#2D1F2A',
        ],
    ],
    [
        'name' => 'Sumi Gold',
        'desc' => 'Hitam tinta, emas, dan warna kertas washi.',
        'colors' => [
            'primary' => '#2B2B2B',
            'primaryHover' => '#171717',
            'soft' => '#F8F4E8',
            'soft1' => '#EEE4D1',
            'soft2' => '#D9C7A3',
            'accent' => '#C9A227',
            'background' => '#F6F1E7',
            'sidebar' => '#171717',
        ],
    ],
];

$fields = [
    'primary' => ['label' => 'Warna Utama', 'hint' => 'Tombol, link, highlight utama'],
    'primaryHover' => ['label' => 'Warna Hover', 'hint' => 'Hover tombol/link'],
    'soft' => ['label' => 'Background Lembut', 'hint' => 'Area soft / pengganti hijau muda'],
    'soft1' => ['label' => 'Soft 1', 'hint' => 'Aksen lembut tingkat 1'],
    'soft2' => ['label' => 'Soft 2', 'hint' => 'Aksen lembut tingkat 2'],
    'accent' => ['label' => 'Aksen', 'hint' => 'Dekorasi / pembeda tone'],
    'background' => ['label' => 'Background Admin', 'hint' => 'Latar halaman admin'],
    'sidebar' => ['label' => 'Sidebar Admin', 'hint' => 'Warna menu kiri admin'],
];
?>

<div class="konten">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h5 class="jdl-section mb-0">Tampilan Website</h5>
                <h1 class="mb-1">Setting Tone Warna</h1>
                <p class="text-secondary mb-0">Set warna di sini, lalu semua pengunjung akan melihat tone yang sama.</p>
            </div>
            <a href="/" target="_blank" class="btn btn-light d-flex gap-2 align-items-center">
                <i class="material-icons">storefront</i>
                <span>Preview Toko</span>
            </a>
        </div>

        <?php if ($msg) { ?>
            <div class="alert alert-primary" role="alert">
                <?= esc($msg); ?>
            </div>
        <?php } ?>

        <div class="row g-4">
            <div class="col-lg-7">
                <form action="/settingwarna" method="post" class="d-flex flex-column gap-3">
                    <div class="row g-3">
                        <?php foreach ($fields as $name => $field) { ?>
                            <div class="col-md-6">
                                <div class="color-field">
                                    <label class="form-label mb-1" for="<?= $name; ?>"><?= $field['label']; ?></label>
                                    <p class="text-secondary small mb-2"><?= $field['hint']; ?></p>
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="color" class="form-control form-control-color theme-color" id="<?= $name; ?>" name="<?= $name; ?>" value="<?= esc($theme[$name]); ?>" data-target="<?= $name; ?>">
                                        <input type="text" class="form-control theme-hex" value="<?= esc($theme[$name]); ?>" data-target="<?= $name; ?>" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$">
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary1 d-flex gap-2 align-items-center">
                            <i class="material-icons">save</i>
                            <span>Simpan Tone Warna</span>
                        </button>
                        <button type="button" class="btn btn-light" id="reset-japanese">Reset Japanese Default</button>
                    </div>
                </form>
            </div>

            <div class="col-lg-5">
                <div class="theme-preview mb-3">
                    <div class="theme-preview-hero p-4 d-flex flex-column justify-content-between">
                        <div>
                            <p class="mb-1 text-uppercase fw-bold" style="letter-spacing: .12em;">Japanese Tone</p>
                            <h2 class="text-white mb-2">Lunarea Furniture</h2>
                            <p class="mb-0">Nuansa baru yang lebih calm, premium, dan tidak hijau.</p>
                        </div>
                    </div>
                    <div class="p-3">
                        <div class="theme-preview-card p-3">
                            <p class="fw-bold mb-1" style="color: var(--theme-primary);">Contoh komponen toko</p>
                            <p class="text-secondary mb-3">Button, link, navbar mobile, dan highlight mengikuti warna utama.</p>
                            <button class="btn btn-primary1 me-2">Beli Sekarang</button>
                            <button class="btn btn-light">Detail Produk</button>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column gap-2">
                    <p class="fw-bold mb-1">Preset cepat</p>
                    <?php foreach ($presets as $preset) { ?>
                        <button type="button" class="preset-btn" data-colors='<?= json_encode($preset['colors']); ?>'>
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <p class="fw-bold mb-1"><?= esc($preset['name']); ?></p>
                                    <p class="text-secondary small mb-0"><?= esc($preset['desc']); ?></p>
                                </div>
                                <div class="preset-swatches flex-shrink-0">
                                    <span style="background: <?= esc($preset['colors']['primary']); ?>"></span>
                                    <span style="background: <?= esc($preset['colors']['accent']); ?>"></span>
                                    <span style="background: <?= esc($preset['colors']['soft2']); ?>"></span>
                                </div>
                            </div>
                        </button>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const themeDefaults = <?= json_encode($presets[0]['colors']); ?>;
const colorInputs = document.querySelectorAll('.theme-color');
const hexInputs = document.querySelectorAll('.theme-hex');

function readThemeColors() {
    const colors = {};
    colorInputs.forEach((input) => {
        colors[input.dataset.target] = input.value.toUpperCase();
    });
    return colors;
}

function applyTheme(colors) {
    Object.entries(colors).forEach(([key, value]) => {
        const colorInput = document.querySelector(`.theme-color[data-target="${key}"]`);
        const hexInput = document.querySelector(`.theme-hex[data-target="${key}"]`);
        if (colorInput) colorInput.value = value;
        if (hexInput) hexInput.value = value;
    });

    let liveStyle = document.getElementById('theme-live-preview');
    if (!liveStyle) {
        liveStyle = document.createElement('style');
        liveStyle.id = 'theme-live-preview';
        document.head.appendChild(liveStyle);
    }

    liveStyle.innerHTML = `
        :root, * {
            --hijau: ${colors.primary};
            --hijaumuda: ${colors.soft};
            --hijaumuda1: ${colors.soft1};
            --hijaumuda2: ${colors.soft2};
            --theme-primary: ${colors.primary};
            --theme-primary-hover: ${colors.primaryHover};
            --theme-soft: ${colors.soft};
            --theme-soft-1: ${colors.soft1};
            --theme-soft-2: ${colors.soft2};
            --theme-accent: ${colors.accent};
            --theme-background: ${colors.background};
            --theme-sidebar: ${colors.sidebar};
        }
        .admin-body {
            --admin-bg: ${colors.background};
            --admin-primary: ${colors.primary};
            --admin-primary-soft: ${colors.soft};
        }
        .admin-sidebar {
            background: ${colors.sidebar};
        }
    `;
}

colorInputs.forEach((input) => {
    input.addEventListener('input', () => {
        const hexInput = document.querySelector(`.theme-hex[data-target="${input.dataset.target}"]`);
        if (hexInput) hexInput.value = input.value.toUpperCase();
        applyTheme(readThemeColors());
    });
});

hexInputs.forEach((input) => {
    input.addEventListener('input', () => {
        const colorInput = document.querySelector(`.theme-color[data-target="${input.dataset.target}"]`);
        if (colorInput && /^#[0-9A-Fa-f]{6}$/.test(input.value)) {
            colorInput.value = input.value;
            applyTheme(readThemeColors());
        }
    });
});

document.querySelectorAll('.preset-btn').forEach((button) => {
    button.addEventListener('click', () => applyTheme(JSON.parse(button.dataset.colors)));
});

document.getElementById('reset-japanese').addEventListener('click', () => applyTheme(themeDefaults));
</script>
<?= $this->endSection(); ?>
