<?php

namespace Cis\Barcode\Render;

use Cis\Barcode\Barcode;

readonly abstract class BarcodeRender implements RenderInterface
{
    public function __construct(
        protected Barcode $barcode,
    ) {}

    /**
     * @param array $block
     * @return array
     */
    protected function getLabelData(array $block): array
    {
        $label = $block['l'][0];
        $ly = (float)($block['l'][1] ?? 1);
        $lx = (float)($block['l'][2] ?? 0.5);
        return [$label, $ly, $lx];
    }
}
