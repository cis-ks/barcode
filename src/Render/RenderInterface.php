<?php

namespace Cis\Barcode\Render;

interface RenderInterface
{
    public function render(string $symbology, string $data, array $options);
}
