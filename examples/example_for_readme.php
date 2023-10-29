<?php

use Cis\Barcode\Barcode;
use Cis\Barcode\BarcodeType\Code39;
use Cis\Barcode\Exceptions\CreateImageException;
use Cis\Barcode\Render\ImageRender;
use Cis\Barcode\Render\SvgRender;
use Cis\Barcode\Symbology;

$barcode = new Barcode(new Code39());
$svgRender = new SvgRender($barcode);
$imageRender = new ImageRender($barcode);

/* Define necessary Options */
$options = [];

/* Generate PNG for Barcode and save it to File */
try {
    $imageRender->render(Symbology::EAN_13, '9780201376548', $options)->toPng('/tmp/9780201376548_barcode.png');
} catch (CreateImageException $e) {
    echo $e->getMessage();
}

/* Retrieve the data directly and send it as output */
try {
    $imageData = $imageRender->render(Symbology::CODE_128, '9780201376548', $options)->toJpeg();
    header('Content-Type: image/jpeg');
    echo $imageData;
} catch (CreateImageException $e) {
    echo $e->getMessage();
}

/* Generate SVG and write it to output */
$svg = $svgRender->render(Symbology::QR, 'Simple Encode QR-Data', $options);
echo $svg;

/* Generate SVG and write it to a file */
$svg = $svgRender->render(Symbology::QR, 'Simple Encode QR-Data', $options);
file_put_contents('/tmp/barcode.svg', $svg);


