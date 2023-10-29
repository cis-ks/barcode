# barcode.php

### Generate barcodes from a single PHP file. MIT license.

  * Output to PNG, GIF, JPEG, or SVG.
  * Generates UPC-A, UPC-E, EAN-13, EAN-8, Code 39, Code 93, Code 128, GS1 128, Codabar, ITF, QR Code, GS1 QR Code, DataMatrix and GS1 DataMatrix.


### Use the Library in  your own work. Ensure that you have loaded the autoloader

```
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
```
### Data:

For UPC or EAN, use `*` for missing digit. 
For Codabar, use `ABCD` or `ENT*` for start and stop characters. 
For QR, encode in Shift-JIS for kanji mode.

### Options:

Add this data as key/value pair into the options-Array.

`w` - Width of image. Overrides `sf` or `sx`.

`h` - Height of image. Overrides `sf` or `sy`.

`sf` - Scale factor. Default is 1 for linear barcodes or 4 for matrix barcodes.

`sx` - Horizontal scale factor. Overrides `sf`.

`sy` - Vertical scale factor. Overrides `sf`.

`p` - Padding. Default is 10 for linear barcodes or 0 for matrix barcodes.

`pv` - Top and bottom padding. Default is value of `p`.

`ph` - Left and right padding. Default is value of `p`.

`pt` - Top padding. Default is value of `pv`.

`pl` - Left padding. Default is value of `ph`.

`pr` - Right padding. Default is value of `ph`.

`pb` - Bottom padding. Default is value of `pv`.

`bc` - Background color in `#RRGGBB` format.

`cs` - Color of spaces in `#RRGGBB` format.

`cm` - Color of modules in `#RRGGBB` format.

`tc` - Text color in `#RRGGBB` format. Applies to linear barcodes only.

`tf` - Text font for SVG output. Default is monospace. Applies to linear barcodes only.

`ts` - Text size. For SVG output, this is in points and the default is 10. For PNG, GIF, or JPEG output, this is the GD library built-in font number from 1 to 5 and the default is 1. Applies to linear barcodes only.

`th` - Distance from text baseline to bottom of modules. Default is 10. Applies to linear barcodes only.

`ms` - Module shape. One of: `s` for square, `r` for round, or `x` for X-shaped. Default is `s`. Applies to matrix barcodes only.

`md` - Module density. A number between 0 and 1. Default is 1. Applies to matrix barcodes only.

`wq` - Width of quiet area units. Default is 1. Use 0 to suppress quiet area.

`wm` - Width of narrow modules and spaces. Default is 1.

`ww` - Width of wide modules and spaces. Applies to Code 39, Codabar, and ITF only. Default is 3.

`wn` - Width of narrow space between characters. Applies to Code 39 and Codabar only. Default is 1.

#### Keywords:

`\FNC1`
- used in Data as a part of the data string.
- when used, it is replaced with a Group separator &lt;GS&gt; ASCII char 29 in encoded data string.
- it should be used to terminate variable length GS1 Application Identifiers in GS1 128 and GS1 DataMatrix barcodes.
- available in barcode symbology:
```
    EAN_128     GS1_DMTX_S    GS1_QR_L   GS1_QR_Q
    GS1_DMTX    GS1_DMTX_R    GS1_QR_M   GS1_QR_H
```
- Do not confuse this with &lt;FNC1&gt; character. Initially, it was intended to use the &lt;FNC1&gt; character as a separator character, but since according to [this stackoverflow answer](https://stackoverflow.com/questions/31318648/what-is-the-actual-hex-binary-value-of-the-gs1-fnc1-character/31322815#31322815) by Terry Burton, FNC1 is a non-data character that requires special treatment. And I don't want to fizzle around this when it is not necessary. Instead, I decided to use the &lt;GS&gt; character. According to the [GS1 General Specifications](https://www.gs1.org/standards/barcodes-epcrfid-id-keys/gs1-general-specifications), the &lt;FNC1&gt; and &lt;GS&gt; characters are in the role of a separator character substitutes. The `\FNC1` remained as a keyword for its uniqueness. If you need, you can replace it with `\GS` or any other keyword you consider unique.
