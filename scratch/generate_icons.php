<?php

function renderRacingManagerIcon(int $size): GdImage
{
    $img = imagecreatetruecolor($size, $size);
    imagealphablending($img, true);
    imagesavealpha($img, true);

    // Transparent initial canvas
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);

    // Colors
    $scale = $size / 512.0;

    // Draw Rounded Squircle Base
    $radius = (int) (96 * $scale);
    $margin = (int) (16 * $scale);
    $boxW = $size - 2 * $margin;
    $boxH = $size - 2 * $margin;

    // Background Gradient (Dark Zinc/Carbon)
    for ($y = $margin; $y < $size - $margin; $y++) {
        $t = ($y - $margin) / (float) $boxH;
        $r = (int) (28 * (1 - $t) + 9 * $t);
        $g = (int) (25 * (1 - $t) + 9 * $t);
        $b = (int) (23 * (1 - $t) + 11 * $t);
        $color = imagecolorallocate($img, $r, $g, $b);

        // Calculate horizontal inset for rounded corners
        $dy = 0;
        if ($y < $margin + $radius) {
            $dy = $margin + $radius - $y;
        } elseif ($y > $size - $margin - $radius) {
            $dy = $y - ($size - $margin - $radius);
        }
        $dx = 0;
        if ($dy > 0 && $radius > 0) {
            $dx = (int) ($radius - sqrt(max(0, $radius * $radius - $dy * $dy)));
        }

        imageline($img, $margin + $dx, $y, $size - $margin - $dx, $y, $color);
    }

    // Border highlight
    $borderColor = imagecolorallocatealpha($img, 249, 115, 22, 50); // orange tint
    imagerectangle($img, $margin + 2, $margin + 2, $size - $margin - 2, $size - $margin - 2, $borderColor);

    // Speed notches (top left)
    $orangeColor = imagecolorallocate($img, 249, 115, 22);
    $cyanColor = imagecolorallocate($img, 6, 182, 212);
    $whiteColor = imagecolorallocate($img, 255, 255, 255);
    $greenColor = imagecolorallocate($img, 34, 197, 94);
    $amberColor = imagecolorallocate($img, 251, 146, 60);

    // Top Right Status LED
    imagefilledellipse($img, (int) (416 * $scale), (int) (88 * $scale), (int) (24 * $scale), (int) (24 * $scale), $greenColor);
    imagefilledellipse($img, (int) (380 * $scale), (int) (88 * $scale), (int) (18 * $scale), (int) (18 * $scale), $amberColor);
    imagefilledellipse($img, (int) (348 * $scale), (int) (88 * $scale), (int) (14 * $scale), (int) (14 * $scale), $cyanColor);

    // Twin Apex Chevrons (Dual-Car Aerodynamics)
    // 1. Cyan Chevron
    $cyanPoly = [
        (int) (96 * $scale), (int) (352 * $scale),
        (int) (156 * $scale), (int) (160 * $scale),
        (int) (192 * $scale), (int) (160 * $scale),
        (int) (132 * $scale), (int) (352 * $scale),
    ];
    imagefilledpolygon($img, $cyanPoly, $cyanColor);

    // 2. Orange Chevron
    $orangePoly = [
        (int) (152 * $scale), (int) (352 * $scale),
        (int) (212 * $scale), (int) (160 * $scale),
        (int) (248 * $scale), (int) (160 * $scale),
        (int) (188 * $scale), (int) (352 * $scale),
    ];
    imagefilledpolygon($img, $orangePoly, $orangeColor);

    // 3. Monogram "R" in White & Orange
    // Stem
    $stemPoly = [
        (int) (216 * $scale), (int) (160 * $scale),
        (int) (256 * $scale), (int) (160 * $scale),
        (int) (196 * $scale), (int) (352 * $scale),
        (int) (216 * $scale), (int) (352 * $scale),
    ];
    imagefilledpolygon($img, $stemPoly, $whiteColor);

    // Top Bar & Bowl
    imagefilledrectangle($img, (int) (236 * $scale), (int) (160 * $scale), (int) (336 * $scale), (int) (200 * $scale), $whiteColor);
    imagefilledrectangle($img, (int) (224 * $scale), (int) (266 * $scale), (int) (320 * $scale), (int) (300 * $scale), $whiteColor);
    imagefilledellipse($img, (int) (336 * $scale), (int) (230 * $scale), (int) (130 * $scale), (int) (110 * $scale), $whiteColor);

    // Bowl cutout
    $cutoutColor = imagecolorallocate($img, 12, 10, 9);
    imagefilledellipse($img, (int) (324 * $scale), (int) (230 * $scale), (int) (68 * $scale), (int) (54 * $scale), $cutoutColor);

    // Leg of R
    $legPoly = [
        (int) (286 * $scale), (int) (280 * $scale),
        (int) (340 * $scale), (int) (280 * $scale),
        (int) (416 * $scale), (int) (352 * $scale),
        (int) (362 * $scale), (int) (352 * $scale),
    ];
    imagefilledpolygon($img, $legPoly, $whiteColor);

    // Accent speed stripe inside R bowl
    $accentPoly = [
        (int) (296 * $scale), (int) (214 * $scale),
        (int) (340 * $scale), (int) (214 * $scale),
        (int) (328 * $scale), (int) (248 * $scale),
        (int) (284 * $scale), (int) (248 * $scale),
    ];
    imagefilledpolygon($img, $accentPoly, $amberColor);

    return $img;
}

// 1. Generate PNGs
$sizes = [
    'apple-touch-icon.png' => 180,
    'icon-192.png' => 192,
    'icon-512.png' => 512,
];

$publicDir = __DIR__.'/../public';

foreach ($sizes as $filename => $size) {
    $img = renderRacingManagerIcon($size);
    $path = $publicDir.'/'.$filename;
    imagepng($img, $path, 9);
    echo "Generated {$path} ({$size}x{$size})\n";
}

// 2. Generate Favicon ICO containing PNG frames (16, 32, 48)
$icoSizes = [16, 32, 48];
$pngFrames = [];

foreach ($icoSizes as $sz) {
    $img = renderRacingManagerIcon($sz);
    ob_start();
    imagepng($img, null, 9);
    $pngData = ob_get_clean();
    $pngFrames[$sz] = $pngData;
}

// Build standard ICO binary
// Header: 2 bytes reserved (0), 2 bytes type (1 = ICO), 2 bytes count (3)
$icoHeader = pack('vvv', 0, 1, count($icoSizes));
$offset = 6 + (16 * count($icoSizes)); // Header + directory entries

$icoDir = '';
$icoData = '';

foreach ($pngFrames as $sz => $data) {
    $len = strlen($data);
    $bWidth = $sz >= 256 ? 0 : $sz;
    $bHeight = $sz >= 256 ? 0 : $sz;
    $bColorCount = 0;
    $bReserved = 0;
    $wPlanes = 1;
    $wBitCount = 32;
    $dwBytesInRes = $len;
    $dwImageOffset = $offset;

    $icoDir .= pack('CCCCvvVV', $bWidth, $bHeight, $bColorCount, $bReserved, $wPlanes, $wBitCount, $dwBytesInRes, $dwImageOffset);
    $icoData .= $data;
    $offset += $len;
}

$icoBinary = $icoHeader.$icoDir.$icoData;
file_put_contents($publicDir.'/favicon.ico', $icoBinary);
echo "Generated {$publicDir}/favicon.ico with multi-resolution frames (16, 32, 48)\n";
