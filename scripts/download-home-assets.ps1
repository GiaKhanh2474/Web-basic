param([switch]$Force)
$ErrorActionPreference = 'Stop'
$shopRoot = Split-Path -Parent $PSScriptRoot
$imageRoot = Join-Path $shopRoot 'assets/images/home'
New-Item -ItemType Directory -Path $imageRoot -Force | Out-Null

# New homepage assets only. Existing files are preserved unless -Force is supplied.
# Source format is retained; browsers detect the image payload independently of its suffix.
$assets = @(
    @{ File = 'pc-dell.jpg'; Sources = @(
        'https://huyhoang.vn/uploads/dell-optiplex-7010-mt-tower-2.jpg',
        'https://hanoicomputercdn.com/media/product/75341_pc_dell_optiplex_7010_sff_2.jpg'
    ) },
    @{ File = 'pc-hp.jpg'; Sources = @(
        'https://www.hp.com/es-es/shop/Html/Merch/Images/c08284365_1750x1285.jpg',
        'https://www.hpobchod.sk/library/configuration/product/HP-Pro-Tower-400-G9_0b.jpg'
    ) },
    @{ File = 'pc-asus.jpg'; Sources = @(
        'https://www.tekshop.co.uk/media/catalog/product/1/7/170630-a.jpg',
        'https://www.smartprice.co.za/product-images/huge/648191-ASUS-ExpertCenter-D5-Mini-Tower.jpg'
    ) },
    @{ File = 'pc-msi.jpg'; Sources = @(
        'https://asset.msi.com/resize/image/global/product/product_172431532653d9111b263487662350e96be5e9903a.png62405b38c58fe0f07fcef2367d8a9ba1/1024.png',
        'https://www.usercontent.eu/katimg/hd/1882100040/B938__s.jpg',
        'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full/catalog-image/MTA-182033812/msi_msi_mag_infinite_s3_desktop_-i7-14700f-16gb_ddr5-rtx3050_6gb-1tb_m-2-500w-_kbm_gaming-fan_cooler-win_11-ohs-3_yr-_full01_k5wn8hj.jpg'
    ) },
    @{ File = 'pc-gaming.jpg'; Sources = @(
        'https://images.kabum.com.br/produtos/fotos/sync_mirakl/506823/Desktop-Lenovo-Legion-T5-Intel-I7-13700f-16GB-512GB-SSD-Nvidia-Geforce-Rtx3060-Windows-Home-90UT0018BP_1699477103_gg.jpg',
        'https://www.pc-canada.com/dd2/img/item/300x300-ORIGINAL/9/90UX0016US.jpg'
    ) },
    @{ File = 'laptop-asus.jpg'; Sources = @(
        'https://dlcdnwebimgs.asus.com/gain/0dea7490-edcd-4bfb-80e4-0324085f46fc/',
        'https://in.store.asus.com/media/catalog/product/v/i/vivobook_15_x1504z_x1504v_product_photo_1b_quite_blue_05_1.jpg'
    ) },
    @{ File = 'laptop-dell.jpg'; Sources = @(
        'https://laptopmedia.com/wp-content/uploads/2023/07/1-73.jpg',
        'https://www.ryans.com/storage/products/main/dell-inspiron-15-3530-13th-gen-intel-core-i3-11704867341.webp'
    ) },
    @{ File = 'laptop-hp.jpg'; Sources = @(
        'https://laptopmedia.com/wp-content/uploads/2020/09/1-92.jpg',
        'https://www.laptoparena.net/images/HP_Pavilion_15_Laptop_800R2EA_image_1.jpg'
    ) },
    @{ File = 'laptop-lenovo.jpg'; Sources = @(
        'https://www.gigahertz.com.ph/cdn/shop/files/lenovo-ideapad-slim-3-82xm0007ph-156-fhd-ryzen-7-7730u-amd-radeon-graphics-16gb-ram-512gb-ssd-win-11-lenovo-gigahertz-584713_600x600_crop_center.jpg?v=1726732488',
        'https://www.gigahertz.com.ph/cdn/shop/files/LAP-LENOVOIDEAPAD315ABR882XM003BPHR5-7530U512GBSSD16GBWIN11sGREY.jpg?v=1710124577'
    ) },
    @{ File = 'laptop-acer.jpg'; Sources = @(
        'https://microless.com/cdn/products/34b5c8bee1f5f902b6268cafdd77a5ab-hi.jpg',
        'https://i.citrus.world/imgcache/size_800/uploads/shop/3/d/3d1531be593d3d2500134cb528487f19.jpg'
    ) }
)

$failed = @()
foreach ($asset in $assets) {
    $target = Join-Path $imageRoot $asset.File
    if (-not $Force -and (Test-Path -LiteralPath $target) -and (Get-Item -LiteralPath $target).Length -gt 2000) {
        Write-Output "Already downloaded: $($asset.File)"
        continue
    }
    $downloaded = $false
    foreach ($source in $asset.Sources) {
        try {
            $response = Invoke-WebRequest -Uri $source -UseBasicParsing -TimeoutSec 25 -OutFile $target -PassThru
            if ($response.Headers['Content-Type'] -notmatch 'image/' -or (Get-Item -LiteralPath $target).Length -lt 2000) {
                throw 'Response is not a usable product image.'
            }
            Write-Output "Downloaded $($asset.File) from $source"
            $downloaded = $true
            break
        } catch {
            Write-Warning "Source unavailable for $($asset.File): $($_.Exception.Message)"
        }
    }
    if (-not $downloaded) { $failed += $asset.File }
}
Get-ChildItem -LiteralPath $imageRoot -File | Select-Object Name,Length
if ($failed.Count -gt 0) { throw ('Missing homepage images: ' + ($failed -join ', ')) }
