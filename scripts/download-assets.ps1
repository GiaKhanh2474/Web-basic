param([switch]$Force)

$ErrorActionPreference = 'Stop'
$shopRoot = Split-Path -Parent $PSScriptRoot
$imageRoot = Join-Path $shopRoot 'assets/images'
New-Item -ItemType Directory -Path $imageRoot -Force | Out-Null

# Product images are downloaded once, then served locally by the PHP shop.
# The actual image format is retained by the CDN; browsers sniff JPEG/PNG/WebP.
$products = @(
    @{ File = 'xiaomi-a27qi.webp'; Slug = 'man-hinh-xiaomi-a27qi-2026-27-inch'; Fallback = 'https://a.allegroimg.com/original/11fe52/c4d9467f47b9a3babbec091b47e3/Monitor-XIAOMI-A27QI-2026-27-2560x1440px-IPS-120Hz' },
    @{ File = 'lg-ultragear.webp'; Slug = 'man-hinh-lg-ultragear-27g411a-b-27-inch'; Direct = 'https://cdn2.cellphones.com.vn/x/media/catalog/product/m/_/m_n_h_nh_gaming_lg_ultragear_27g411a-b_27_inch_-_1.png' },
    @{ File = 'dell-ultrasharp.webp'; Slug = 'man-hinh-dell-u2424h-24-inch' },
    @{ File = 'asus-tuf.webp'; Slug = 'man-hinh-gaming-asus-tuf-vg259q5a-25-inch' },
    @{ File = 'msi-pro.webp'; Slug = 'man-hinh-msi-pro-mp243l-e14-24-inch' },
    @{ File = 'samsung-odyssey.webp'; Slug = 'man-hinh-gaming-samsung-odyssey-g5-ls27cg552-27-inch' },
    @{ File = 'aoc-gaming.webp'; Slug = 'man-hinh-aoc-gaming-24g50z2-24-inch' },
    @{ File = 'asus-proart.webp'; Slug = 'man-hinh-asus-proart-pa278qev-27-inch' },
    @{ File = 'xiaomi-a24i.webp'; Slug = 'man-hinh-xiaomi-a24i-24-inch' },
    @{ File = 'lg-4k.webp'; Slug = 'man-hinh-lg-32ur500-32-inch' },
    @{ File = 'msi-gaming.webp'; Slug = 'man-hinh-msi-g275-e14-27-inch' },
    @{ File = 'dell-office.webp'; Slug = 'man-hinh-dell-p2725h-27-inch' }
)

foreach ($product in $products) {
    $target = Join-Path $imageRoot $product.File
    if (-not $Force -and (Test-Path -LiteralPath $target) -and (Get-Item -LiteralPath $target).Length -gt 2000) {
        Write-Output "Already downloaded: $($product.File)"
        continue
    }
    try {
        $candidates = @()
        if ($product.Direct) { $candidates += $product.Direct }
        $html = ''
        try {
            $page = Invoke-WebRequest -Uri ('https://cellphones.com.vn/' + $product.Slug + '.html') -UseBasicParsing -TimeoutSec 15
            $html = [System.Net.WebUtility]::HtmlDecode($page.Content).Replace('\/', '/')
        } catch { Write-Warning "Product page unavailable: $($product.File)" }
        $metaTags = [regex]::Matches($html, '<meta\b[^>]*>', 'IgnoreCase')
        foreach ($tag in $metaTags) {
            if ($tag.Value -match '(?:property|name)=["'']og:image["'']') {
                $contentMatch = [regex]::Match($tag.Value, 'content=["'']([^"'']+)["'']')
                if ($contentMatch.Success) { $candidates += $contentMatch.Groups[1].Value }
            }
        }
        foreach ($match in [regex]::Matches($html, 'https?[^"''\s<>]+/media/catalog/product/[^"''\s<>]+\.(?:png|jpg|jpeg|webp)', 'IgnoreCase')) {
            $candidates += $match.Value
        }
        if ($product.Fallback) { $candidates += $product.Fallback }
        $downloaded = $false
        foreach ($candidate in ($candidates | Select-Object -Unique | Select-Object -First 5)) {
            if ($candidate -match 'placehold|placehoder') { continue }
            # Fetch the original catalog size rather than the 200px social thumbnail.
            $candidate = $candidate -replace '(cdn2\.cellphones\.com\.vn)/[0-9]+x(?:[0-9]+)?/media/', '$1/x/media/'
            try {
                $response = Invoke-WebRequest -Uri $candidate -UseBasicParsing -TimeoutSec 25 -OutFile $target -PassThru
                if ((Get-Item -LiteralPath $target).Length -gt 2000 -and $response.Headers['Content-Type'] -match 'image/') {
                    Write-Output "Downloaded $($product.File) from $candidate"
                    $downloaded = $true
                    break
                }
            } catch { Write-Warning "Candidate unavailable for $($product.File)" }
        }
        if (-not $downloaded) { Write-Warning "No usable catalog image: $($product.File)" }
    } catch { Write-Warning "$($product.File): $($_.Exception.Message)" }
}

try {
    Invoke-WebRequest -Uri 'https://cellphones.com.vn/media/wysiwyg/Man-hinh/Xiaomi/27-inch/man-hinh-xiaomi-a27qi-2026-27-inch-3.jpg' -UseBasicParsing -TimeoutSec 25 -OutFile (Join-Path $imageRoot 'xiaomi-detail.webp')
    Write-Output 'Downloaded xiaomi-detail.webp'
} catch { Write-Warning 'Xiaomi detail scene unavailable.' }

Get-ChildItem -LiteralPath $imageRoot -File | Select-Object Name,Length
