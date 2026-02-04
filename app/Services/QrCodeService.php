<?php

namespace App\Services;

//use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Endroid\QrCode\{QrCode, Logo\Logo, Encoding\Encoding, Writer\PngWriter, ErrorCorrectionLevel};
use Illuminate\Support\Facades\Http;
use Mpdf\Tag\Q;

class QrCodeService
{
    protected $defaultSize = 200;
    protected $defaultFormat = 'base64';
    protected $logoPath;
    protected $logoSize = 0.3;
    protected $style = 'round';
    protected $eye = 'circle';
    protected $gradientStart = '#4158D0';
    protected $gradientEnd = '#C850C0';
    protected $defaultImageUrl = 'https://sipo.ptrekaindo.co.id/assets/img/logo_reka_circlewhite2.png';

    public function __construct()
    {
        $this->logoPath = public_path('assets/img/logo_reka_circlewhite2.png');
    }

    /**
     * Generate QR Code dengan logo
     */

    /**
     * Convert hex color to RGB array [r, g, b]
     */
    protected function hexToRgb(string $hex): array
    {
        // Remove # if present
        $hex = ltrim($hex, '#');

        // Handle 3-digit hex
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        // Parse RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return [$r, $g, $b];
    }

    public function generateWithLogo(string $data, ?int $size = null, ?string $format = null): string
    {
        $size = $size ?? $this->defaultSize;
        $format = $format ?? $this->defaultFormat;

        $rgbStart = $this->hexToRgb($this->gradientStart);
        $rgbEnd   = $this->hexToRgb($this->gradientEnd);

        $writer = new PngWriter();

        $result = null;
        $lastException = null;
        $errorLevels = [
            ErrorCorrectionLevel::Low,
            ErrorCorrectionLevel::Medium,
            ErrorCorrectionLevel::Quartile,
            ErrorCorrectionLevel::High,
        ];
        foreach ($errorLevels as $level) {
            // Just to ensure the level is valid
            try {
                $qrText = new QrCode(
                    data: $data,
                    encoding: new Encoding('UTF-8'),
                    errorCorrectionLevel: $level,
                );
                $logo = new Logo(
                    path: $this->logoPath,
                    resizeToWidth: 130,
                );

                $result = $writer->write($qrText, $logo);
                $writer->validateResult($result, $data);
                break;
            } catch (\Exception $e) {
                $lastException = $e;
            }
        }

        if (!$result) {
            throw new \Exception(
                'Gagal generasi QR Code, ' . 'Correction level terakhir: ' . $level . ', Error: ' .
                    $lastException->getMessage()
            );
        }

        $qrBase64 = base64_encode($result->getString());

        return $qrBase64;

        // return QrCode::format($format)
        //     ->size($size)
        //     ->style($this->style)
        //     ->eye($this->eye)
        //     ->gradient(
        //         $rgbStart[0],
        //         $rgbStart[1],
        //         $rgbStart[2],
        //         $rgbEnd[0],
        //         $rgbEnd[1],
        //         $rgbEnd[2],
        //         'vertical'
        //     )
        //     ->errorCorrection('H')
        //     ->merge($this->logoPath, $this->logoSize, true)
        //     ->generate($data);
    }

    /**
     * Generate QR Code tanpa logo
     */
    public function generate(string $data, ?int $size = null, ?string $format = null): string
    {
        $size = $size ?? $this->defaultSize;
        $format = $format ?? $this->defaultFormat;

        return QrCode::format($format)
            ->size($size)
            ->style($this->style)
            ->eye($this->eye)
            ->gradient($this->gradientStart, $this->gradientEnd, 'vertical')
            ->errorCorrection('H')
            ->generate($data);
    }

    // Method chainable untuk customization (optional)
    public function setStyle(string $style): self
    {
        $this->style = $style;
        return $this;
    }

    public function setEye(string $eye): self
    {
        $this->eye = $eye;
        return $this;
    }

    public function setGradient(string $start, string $end): self
    {
        $this->gradientStart = $start;
        $this->gradientEnd = $end;
        return $this;
    }

    public function setSize(int $size): self
    {
        $this->defaultSize = $size;
        return $this;
    }

    public function setFormat(string $format): self
    {
        $this->defaultFormat = $format;
        return $this;
    }

    public function setLogo(string $path, float $size = 0.3): self
    {
        $this->logoPath = $path;
        $this->logoSize = $size;
        return $this;
    }
}
