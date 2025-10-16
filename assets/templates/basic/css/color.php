<?php
header("Content-Type:text/css");
function checkHexColor($color)
{
    return preg_match('/^#[a-f0-9]{6}$/i', $color);
}

if (isset($_GET['color']) and $_GET['color'] != '') {
    $color = "#" . $_GET['color'];
}

if (!$color or !checkHexColor($color)) {
    $color = "#336699";
}
if (isset($_GET['secondColor']) and $_GET['secondColor'] != '') {
    $secondColor = "#" . $_GET['secondColor'];
}

if (!$secondColor or !checkHexColor($secondColor)) {
    $secondColor = "#336699";
}

if (isset($_GET['merchant']) and $_GET['merchant'] != '') {
    $merchantColor = "#" . $_GET['merchant'];
}

if (!$merchantColor or !checkHexColor($merchantColor)) {
    $merchantColor = "#14b8a6";
}

if (isset($_GET['agent']) and $_GET['agent'] != '') {
    $agentColor = "#" . $_GET['agent'];
}

if (!$agentColor or !checkHexColor($agentColor)) {
    $agentColor = "#14b8a6";
}

function hexToHsl($hex)
{
    $hex   = str_replace('#', '', $hex);
    $red   = hexdec(substr($hex, 0, 2)) / 255;
    $green = hexdec(substr($hex, 2, 2)) / 255;
    $blue  = hexdec(substr($hex, 4, 2)) / 255;
    $cmin  = min($red, $green, $blue);
    $cmax  = max($red, $green, $blue);
    $delta = $cmax - $cmin;
    if ($delta == 0) {
        $hue = 0;
    } elseif ($cmax === $red) {
        $hue = (($green - $blue) / $delta);
    } elseif ($cmax === $green) {
        $hue = ($blue - $red) / $delta + 2;
    } else {
        $hue = ($red - $green) / $delta + 4;
    }
    $hue = round($hue * 60);
    if ($hue < 0) {
        $hue += 360;
    }
    $lightness  = (($cmax + $cmin) / 2);
    $saturation = $delta === 0 ? 0 : ($delta / (1 - abs(2 * $lightness - 1)));
    if ($saturation < 0) {
        $saturation += 1;
    }
    $lightness  = round($lightness * 100);
    $saturation = round($saturation * 100);
    $hsl['h']   = $hue;
    $hsl['s']   = $saturation;
    $hsl['l']   = $lightness;
    return $hsl;
}
?>

:root{
--base-h: <?php echo hexToHsl($color)['h']; ?>;
--base-s: <?php echo hexToHsl($color)['s']; ?>%;
--base-l: <?php echo hexToHsl($color)['l']; ?>%;
--base-two-h: <?php echo hexToHsl($secondColor)['h']; ?>;
--base-two-s: <?php echo hexToHsl($secondColor)['s']; ?>%;
--base-two-l: <?php echo hexToHsl($secondColor)['l']; ?>%;
}

:root:has(.agent-dashboard){
    --base-h: <?php echo hexToHsl($agentColor)['h']; ?>;
    --base-s: <?php echo hexToHsl($agentColor)['s']; ?>%;
    --base-l: <?php echo hexToHsl($agentColor)['l']; ?>%;
}
.dashboard.agent-dashboard .sidebar-menu-list__item.active>a {
    background: <?php echo $agentColor ?>;
}
:root:has(.merchant-dashboard){
    --base-h: <?php echo hexToHsl($merchantColor)['h']; ?>;
    --base-s: <?php echo hexToHsl($merchantColor)['s']; ?>%;
    --base-l: <?php echo hexToHsl($merchantColor)['l']; ?>%;
}
.dashboard.merchant-dashboard .sidebar-menu-list__item.active>a {
    background: <?php echo $merchantColor ?>;
}