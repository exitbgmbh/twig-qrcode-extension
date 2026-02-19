<?php

namespace Priotas\Twig\Extension;

use Endroid\QrCode\Exception\ValidationException;
use Endroid\QrCode\Label\Font\Font;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode as EndroidQrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeShrink;
use Endroid\QrCode\Writer\BinaryWriter;
use Endroid\QrCode\Writer\DebugWriter;
use Endroid\QrCode\Writer\EpsWriter;
use Endroid\QrCode\Writer\PdfWriter;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class QrCode extends AbstractExtension
{
    const DEFAULT_IMAGE_SIZE = 200;
    const DEFAULT_TYPE = 'png';
    const SVG_OUTPUT_INLINE = 'inline';
    const SVG_OUTPUT_DATA_URI = 'data_uri';

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('qrcode', [$this, 'qrcode'])
        ];
    }

    /**
     * @return string
     * @throws ValidationException|Exception
     */
    public function qrcode(
        $text,
        $type = self::DEFAULT_TYPE,
        $size = self::DEFAULT_IMAGE_SIZE,
        $label = '',
        $labelFontPath = '',
        $margin = 0,
        $logo = '',
        $logoResizeToWidth = null,
        $logoResizeToHeight = null
    ) {
        switch ($type) {
            case 'svg':
                $writer = new SvgWriter();
                break;
            case 'eps':
                $writer = new EpsWriter();
                break;
            case 'pdf':
                $writer = new PdfWriter();
                break;
            case 'binary':
                $writer = new BinaryWriter();
                break;
            default:
                $writer = new PngWriter();
                break;
        }

        $qrCode = new EndroidQrCode($text);
        $qrCode->setSize($size);
        $qrCode->setMargin($margin);

        $mode = new RoundBlockSizeModeShrink();

        if (!empty($logo)) {
            $logo = new Logo($logo, $logoResizeToWidth, $logoResizeToHeight);
        } else {
            $logo = null;
        }

        $qrCode->setRoundBlockSizeMode($mode);

        $label = Label::create($label);
        if (!empty($labelFontPath)) {
            $labelFont = new Font($labelFontPath);
            $label->setFont($labelFont);
        }

        $result =  $writer->write($qrCode, $logo, $label);

        return $result->getDataUri();
    }
}
