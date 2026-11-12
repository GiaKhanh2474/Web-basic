# QA report — 2026-11-12

Scope: repository `GiaKhanh2474/Web-basic`, main commit `8a5d030b835e10cbb255abad074202becd1fef07`, after PR #5. The separate `cellphones-shop` installation is older and was not overwritten. Tests used PHP 8.2/XAMPP and an isolated MySQL database named `web_basic_qa_20261112`.

## Reproduced and fixed

| Priority | Reproduction before fix | Resolution and evidence |
| --- | --- | --- |
| P1 | Apache returned 200 for `database/schema.sql`, `.git/config`, and the `config/` directory. The repository lacked `.htaccess`; PHP's development router alone did not protect Apache. | Added tracked `.htaccess`, disabled directory listing, blocked internal directories and sensitive files. HTTP checks now return 403 on both Apache and the PHP server. |
| P2 | Apache returned 404 for `man-hinh.html`. | Restored rewrite routes to the monitor, cart and product pages; route checks return 200. Same missing-file cause as the P1 issue. |
| P2 | Five PCs stored with `SSD 512GB` or `RAM 16GB` returned zero matches for the 512GB/16GB filters. | Capacity matching accepts prefixes and spaces and requires number/unit boundaries. Tests also exclude 1512GB and 160GB false matches. |
| P1 | `database/pc-products.sql` unconditionally deleted every PC whose slug matched `pc-%-demo`, despite promising to preserve edits. | Removed automatic deletion. Importing twice preserves every field of the fixture and existing QA products; unwanted examples can be removed explicitly. |

## Validation

- `tests/qa.php`: 26 checks — Apache/PHP routes, blocked files, capacity filters, non-destructive repeated PC imports.
- `tests/smoke.php`: 28 checks — session cart, CSRF, price tampering, stock/quantity validation, HTML escaping, insertion and removal of temporary products.
- `tests/homepage.php`: 27 checks — category grouping, search, details, mixed-category cart and navigation.
- `tests/regressions.php`: 20 checks — monitor navigation, filters, remote image markup and omitted screen specifications.
- `scripts/verify-assets.php`: all 22 seeded product pages/images and seven route/protection checks passed.
- PHP and JavaScript syntax checks passed; `git diff --check` passed.

Total: **101 functional checks passed**, plus image/route verification and syntax checks. Temporary fixture changes were deleted or rolled back. The isolated QA database and server are removed after validation. No existing storefront product data was modified.

## Limits

Browser automation could not initialize because its Windows sandbox failed (`setup refresh had errors`). Visual layout, mobile swipe, toast rendering, favorites-dialog interaction and keyboard focus were not verified in a real browser in this run. HTTP/API tests and JavaScript syntax checks do not replace those checks.

## Re-run the added regression suite

Use a disposable database whose name starts with `web_basic_qa_`; `tests/qa.php` refuses other databases. Set `DB_DATABASE` before running setup and before starting the PHP server so both use the same database. Keep `.htaccess` when copying/deploying the repository.

```powershell
$env:DB_DATABASE = 'web_basic_qa_local'
& 'C:\xampp\php\php.exe' scripts/setup.php
# In one terminal, with the same DB_DATABASE:
& 'C:\xampp\php\php.exe' -S 127.0.0.1:8094 router.php
# In a second terminal, set DB_DATABASE again, then:
& 'C:\xampp\php\php.exe' tests/qa.php http://127.0.0.1:8094/ http://localhost/Web-basic-merge-review/
```

The optional second URL checks Apache against the checkout's actual folder name. Apache route checks are read-only. Test data writes are confined to the selected QA database and rolled back.
