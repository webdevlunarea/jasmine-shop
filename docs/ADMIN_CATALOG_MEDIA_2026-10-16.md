# Admin catalog media — 16 October 2026

## Scope and root cause

- Product photo selection rebuilt every file input after a change. The preview survived in a blob URL, but native FileList values were discarded before multipart submission.
- Existing preview data URLs labelled all bytes as WebP, regardless of actual format. Upload errors were silently skipped and photo-to-variant mappings were written before all uploads were checked.
- Product creation remains owned by MyLuna. This patch adds/replaces photos for existing synced products; it does not reopen product creation in Lunarea.

## Changed behavior

- Persistent server-rendered gallery inputs; add photo slots up to the existing 50-column limit, preview, replace, and cancel only a new selection. No saved-photo deletion is introduced.
- New product, banner and category uploads must contain real WebP/AVIF bytes. No extension-only renaming and no automatic JPEG/PNG conversion. Existing legacy pictures remain readable and are not bulk-converted.
- Products/banners: 4 MiB per new file; categories: 2 MiB. Product/category batches: 12 MiB. Images: at most 4096 px per side and 16 megapixels. Lower PHP, proxy or web-server limits still apply.
- All product uploads, discount values and photo mappings are validated before writes. Product/gallery/mapping updates share a database transaction. Category uploads are validated as a batch; only newly generated files are removed on failure, never existing files.
- Variant mapping cannot point to an empty photo when photos exist. Additional saved slots remain visible independently of the master variant count. Creating a gallery record retains a legacy cover stored only in `barang.gambar`.
- Admin POST handlers for changed upload flows require admin session and CSRF verification through the existing guard. No global CSRF configuration change.
- Product gallery images use `/imgproduct/{id}/{slot}?v={content-hash}`, actual MIME, ETag, bounded freshness and `nosniff`. Only public catalog assets use this cache policy; customer pictures and payment proofs are excluded.
- Native PHP upload failures produce actionable feedback rather than an apparent successful save. Preview validation is independent of Bootstrap/TinyMCE availability.

## Runtime compatibility

AVIF is accepted only when the runtime can validate its MIME and dimensions. PHP documents accurate AVIF dimensions starting in PHP 8.2: https://www.php.net/manual/en/function.getimagesize.php . The local PHP 8.1 fixture verifies rejection when AVIF dimensions are unavailable; it does not claim successful AVIF upload on production. Use WebP on such a runtime. GD decoding is additionally checked when that codec is available. No encoder or package installation is required.

## Verification and deployment

- 350 PHP tests / 2,130 assertions and 21 Node tests passed locally.
- Browser fixture checks at 1440px and 390px: two native selected files remain in FormData, adding slots retains previous files, variant previews update, invalid PNG is rejected, reset preserves other selections, banner/category previews work, no page horizontal overflow.
- Synthetic browser checks do not submit to production or prove production database persistence. Existing form action/name/default contracts remain checked; field order may change for improved task hierarchy.
- No SQL, migration, environment changes, shipment resend, or MyLuna changes are needed.
- Before production validation: confirm the web PHP runtime supports WebP/AVIF validation, check `upload_max_filesize`, `post_max_size`, `max_file_uploads`, database packet limits and transactional table support. Category upload directory must be writable by the web worker. Do not use blanket permission changes.
- Deploy source and both new admin assets together. Verify one existing product with two small WebP files, reload and compare both admin and customer views. Test AVIF separately on the actual web runtime. Confirm error feedback with a PNG; no images/discount/mapping should be saved in that rejected request.
- Rollback: revert the code commit through normal deployment. No tables are added. Valid saved WebP/AVIF blobs remain in existing columns; new category files are not removed by rollback. Old code does not support new AVIF category URLs, so retain that route/MIME support or restore the prior category configuration deliberately if rollback is necessary.

Article/editor images, voucher/email posters, customer avatars and payment evidence are outside this catalog upload patch. Their formats and workflows are unchanged.
