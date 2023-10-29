<?php

namespace Cis\Barcode\Render;

readonly class SvgRender extends BarcodeRender
{
    /**
     * @param string $symbology
     * @param string $data
     * @param array $options
     * @return string
     */
    public function render(string $symbology, string $data, array $options): string
    {
        $svgTemplate = <<<SVG
<?xml version="1.0"?>
<svg xmlns="http://www.w3.org/2000/svg"  width="%s" height="%s" viewBox="0 0 %1\$s %2\$s">
    <g>%s%s</g>
</svg>
SVG;

        list($code, $widths, $width, $height, $x, $y, $w, $h) = $this->barcode->encodeCalculateSize(
            $symbology,
            $data,
            $options
        );
        $bgcolor = ($options['bc'] ?? 'white');
        $colors = [
            ($options['cs'] ?? ''),
            ($options['cm'] ?? 'black'),
            ($options['c2'] ?? '#FF0000'),
            ($options['c3'] ?? '#FFFF00'),
            ($options['c4'] ?? '#00FF00'),
            ($options['c5'] ?? '#00FFFF'),
            ($options['c6'] ?? '#0000FF'),
            ($options['c7'] ?? '#FF00FF'),
            ($options['c8'] ?? 'white'),
            ($options['c9'] ?? 'black'),
        ];
        return sprintf(
            $svgTemplate,
            $width,
            $height,
            $bgcolor ? sprintf(
                '<rect x="0" y="0" width="%s" height="%s" fill=\"%s\" />',
                $width,
                $height,
                htmlspecialchars($bgcolor)
            ) : '',
            $this->renderSvg($code, $x, $y, $w, $h, $colors, $widths, $options)
        );
    }

    /**
     * @param array $code
     * @param ...$params
     * @return string
     */
    private function renderSvg(array $code, ...$params): string
    {
        if ($code && isset($code['g']) && $code['g']) {
            return match ($code['g']) {
                'l' => $this->renderLinear($code, ...$params),
                'm' => $this->renderMatrix($code, ...$params)
            };
        }
        return '';
    }

    /**
     * @param array $code
     * @param int $x
     * @param int $y
     * @param int $w
     * @param int $h
     * @param array $colors
     * @param array $widths
     * @param array $options
     * @return string
     */
    private function renderMatrix(array $code,
                                  int   $x,
                                  int   $y,
                                  int   $w,
                                  int   $h,
                                  array $colors,
                                  array $widths,
                                  array $options): string
    {
        $shape = strtolower(($options['ms'] ?? ''));
        $density = (float)($options['md'] ?? 1);
        list($width, $height) = $this->barcode->matrixCalculateSize($code, $widths);
        if ($width && $height) {
            $scale = min($w / $width, $h / $height);
            if ($scale > 1) {
                $scale = floor($scale);
            }
            $x = floor($x + ($w - $width * $scale) / 2);
            $y = floor($y + ($h - $height * $scale) / 2);
        } else {
            $scale = 1;
            $x = floor($x + $w / 2);
            $y = floor($y + $h / 2);
        }

        $scaleSvg = $scale != 1 ? sprintf(" scale(%d %d)", $scale, $scale) : '';
        $tx = htmlspecialchars(sprintf("translate(%s %s)%s", $x, $y, $scaleSvg));

        $x = $code['q'][3] * $widths[0];
        $y = $code['q'][0] * $widths[0];
        $wh = $widths[1];
        $svg = '';
        foreach ($code['b'] as $by => $row) {
            $y1 = $y + $by * $wh;
            foreach ($row as $bx => $color) {
                $x1 = $x + $bx * $wh;
                $mc = $colors[$color];
                if ($mc) {
                    $svg .= $this->matrixDot($x1, $y1, $wh, $wh, $mc, $shape, $density);
                }
            }
        }
        return sprintf("<g transform=\"%s\">%s</g>", $tx, $svg);
    }

    /**
     * @param array $code
     * @param int $x
     * @param int $y
     * @param int $w
     * @param int $h
     * @param array $colors
     * @param array $widths
     * @param array $options
     * @return string
     */
    public function renderLinear(array $code,
                                 int   $x,
                                 int   $y,
                                 int   $w,
                                 int   $h,
                                 array $colors,
                                 array $widths,
                                 array $options): string
    {
        $textheight = (int)($options['th'] ?? 10);
        $textfont = ($options['tf'] ?? 'monospace');
        $textsize = (int)($options['ts'] ?? 10);
        $textcolor = ($options['tc'] ?? 'black');
        $width = 0;
        foreach ($code['b'] as $block) {
            foreach ($block['m'] as $module) {
                $width += $module[1] * $widths[$module[2]];
            }
        }
        if ($width) {
            $scale = $w / $width;
            if ($scale > 1) {
                $scale = floor($scale);
                $x = floor($x + ($w - $width * $scale) / 2);
            }
        } else {
            $scale = 1;
            $x = floor($x + $w / 2);
        }

        $scaleSvg = $scale != 1 ? sprintf(' scale(%d 1)', $scale) : '';
        $tx = sprintf('translate(%s %s)%s', $x, $y, $scaleSvg);

        $svg = '';
        $x = 0;
        foreach ($code['b'] as $block) {
            $svg = '<g>';
            $mx = $x;
            list($block, $label, $ly, $lx, $mh) = $this->getLinearLabel($block, $h, $textheight);
            $this->generateLinearBlocks($block['m'], $colors, $widths, $mx, $mh, $svg);
            $this->generateLinearLabel($label, $x, $mx, $lx, $ly, $textfont, $textsize, $textcolor, $svg);
            $svg .= '</g>';
            $x = $mx;
        }

        return sprintf('<g transform="%s">%s</g>', htmlspecialchars($tx), $svg);
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $w
     * @param int $h
     * @param string $mc
     * @param string $ms
     * @param float $md
     * @return string
     */
    public function matrixDot(int $x, int $y, int $w, int $h, string $mc, string $ms, float $md): string
    {
        switch ($ms) {
            case 'r':
                $cx = $x + $w / 2;
                $cy = $y + $h / 2;
                $rx = $w * $md / 2;
                $ry = $h * $md / 2;
                return sprintf(
                    "<ellipse cx=\"%d\" cy=\"%d\" rx=\"%s\" ry=\"%s\" fill=\"%s\"/>",
                    $cx,
                    $cy,
                    $rx,
                    $ry,
                    $mc
                );
            case 'x':
                $x1 = $x + (1 - $md) * $w / 2;
                $y1 = $y + (1 - $md) * $h / 2;
                $x2 = $x + $w - (1 - $md) * $w / 2;
                $y2 = $y + $h - (1 - $md) * $h / 2;
                return sprintf(
                        '<g><line x1="%s" y1="%s" x2="%s" y2="%s" stroke="%s" stroke-width="%s"/>' .
                        '<line x1="%1$s" y1="%4$s" x2="%3$s" y2="%2$s" stroke="%5$s" stroke-width="%6$s"/></g>',
                    $x1,
                    $y1,
                    $x2,
                    $y2,
                    $mc,
                    $md / 5,
                );
            default:
                $x += (1 - $md) * $w / 2;
                $y += (1 - $md) * $h / 2;
                $w *= $md;
                $h *= $md;
                return sprintf(
                    "<rect x=\"%d\" y=\"%d\" width=\"%d\" height=\"%d\" fill=\"%s\"/>",
                    $x,
                    $y,
                    $w,
                    $h,
                    $mc
                );
        }
    }

    /**
     * @param mixed $block
     * @param int $h
     * @param int $textheight
     * @return array
     */
    private function getLinearLabel(mixed $block, int $h, int $textheight): array
    {
        if (isset($block['l'])) {
            list($label, $ly, $lx) = $this->getLabelData($block);
            $mh = min($h, $h + ($ly - 1) * $textheight);
            $ly = $h + $ly * $textheight;
        } else {
            $label = null;
            $mh = $h;
        }
        return [$block, $label, $ly ?? null, $lx ?? null, $mh];
    }

    /**
     * @param $m
     * @param $colors
     * @param $widths
     * @param mixed $mx
     * @param mixed $mh
     * @param string $svg
     * @return void
     */
    private function generateLinearBlocks($m, $colors, $widths, mixed &$mx, mixed $mh, string &$svg): void
    {
        foreach ($m as $module) {
            $mc = htmlspecialchars($colors[$module[0]]);
            $mw = $module[1] * $widths[$module[2]];
            if ($mc) {
                $svg .= sprintf('<rect x="%s" y="0" width="%s" height="%s" fill="%s"/>', $mx, $mw, $mh, $mc);
            }
            $mx += $mw;
        }
    }

    /**
     * @param mixed $label
     * @param mixed $x
     * @param mixed $mx
     * @param mixed $lx
     * @param mixed $ly
     * @param mixed $textfont
     * @param int $textsize
     * @param mixed $textcolor
     * @param string $svg
     * @return void
     */
    private function generateLinearLabel(mixed  $label,
                                         mixed  $x,
                                         mixed  $mx,
                                         mixed  $lx,
                                         mixed  $ly,
                                         mixed  $textfont,
                                         int    $textsize,
                                         mixed  $textcolor,
                                         string &$svg): void
    {
        if (!is_null($label)) {
            $lx = ($x + ($mx - $x) * $lx);
            $svg .= sprintf(
                '<text x="%s" y="%s" text-anchor="middle" font-family="%s" font-size="%s" fill="%s">%s</text>',
                $lx,
                $ly,
                htmlspecialchars($textfont),
                htmlspecialchars($textsize),
                htmlspecialchars($textcolor),
                htmlspecialchars($label)
            );
        }
    }
}
