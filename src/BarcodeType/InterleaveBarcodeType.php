<?php

namespace Cis\Barcode\BarcodeType;

abstract class InterleaveBarcodeType extends BarcodeType
{
    /**
     * @param $blocks
     * @return array
     */
    protected function interleaveBlocks($blocks): array
    {
        $data = [];
        $num_blocks = count($blocks);
        for ($offset = 0; true; $offset++) {
            $break = true;
            for ($i = 0; $i < $num_blocks; $i++) {
                if (isset($blocks[$i][$offset])) {
                    $data[] = $blocks[$i][$offset];
                    $break = false;
                }
            }
            if ($break) {
                break;
            }
        }
        return $data;
    }
}
