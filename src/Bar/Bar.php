<?php

namespace Maantje\Charts\Bar;

use Maantje\Charts\Chart;
use Maantje\Charts\SVG\Fragment;
use Maantje\Charts\SVG\Rect;
use Maantje\Charts\SVG\Text;

class Bar implements BarContract
{
    public function __construct(
        public ?string $name = null,
        public float $value = 0,
        public ?string $yAxis = null,
        public string $color = '#3498db',
        public ?float $width = 100,
        public ?string $labelColor = null,
        public ?int $fontSize = null,
        public ?string $fontFamily = null,
        public int $labelMarginY = 30,
        public ?int $radius = null,
        public ?int $labelRotation = null,
    ) {}

    public function render(Chart $chart, float $x, float $maxBarWidth): string
    {
        $width = min($this->width ?? $maxBarWidth, $maxBarWidth);
        $y = $chart->yForAxis($this->value, $this->yAxis);

        if (! is_null($this->width)) {
            $x += ($maxBarWidth - $width) / 2;
        }

        if ($this->labelRotation) {
            $labelX = $x - ($width / 2);
        } else {
            $labelX = $x + $width / 2;
        }

        return new Fragment([
            new Rect(
                x: $x,
                y: $y,
                width: $width,
                height: $chart->bottom() - $y,
                fill: $this->color,
                rx: $this->radius ?? 0,
                ry: $this->radius ?? 0,
                title: $this->value,
            ),
            $this->name ? new Text(
                content: $this->name,
                x: $labelX,
                y: $chart->bottom() + $this->labelMarginY,
                fontFamily: $this->fontFamily ?? $chart->fontFamily,
                fontSize: $this->fontSize ?? $chart->fontSize,
                fill: $this->labelColor ?? $chart->color,
                textAnchor: $this->labelRotation ? 'end' : 'middle',
                transform: $this->labelRotation ? "rotate({$this->labelRotation} {$labelX} {$chart->bottom()})" : null,
            ) : null,
        ]);
    }

    public function value(): float
    {
        return $this->value;
    }
}


namespace Maantje\Charts\Bar;

use Maantje\Charts\Chart;
use Maantje\Charts\SVG\Fragment;
use Maantje\Charts\SVG\Rect;
use Maantje\Charts\SVG\Text;

class Bar implements BarContract
{
    public function __construct(
        public ?string $name = null,
        public float   $value = 0,
        public ?string $yAxis = null,
        public string  $color = '#3498db',
        public ?float  $width = 100,
        public ?string $labelColor = null,
        public ?int    $fontSize = null,
        public ?string $fontFamily = null,
        public int     $labelMarginY = 30,
        public ?int    $radius = null,
        public ?int    $labelRotation = null,
    )
    {
    }

    public function render(Chart $chart, float $x, float $maxBarWidth): string
    {
        $width = min($this->width ?? $maxBarWidth, $maxBarWidth);
        $y = $chart->yForAxis($this->value, $this->yAxis);

        if (!is_null($this->width)) {
            $x += ($maxBarWidth - $width) / 2;
        }

        $labelX = $x + $width; // Align label at the end of the bar
        $labelY = $chart->bottom() + $this->labelMarginY;
        $fontSize = $this->fontSize ?? $chart->fontSize;
        $availableWidth = $chart->bottomSpace(); // Maximum allowed width for the label

        // Estimate text width based on font size and character count
        $approxCharWidth = $fontSize * 0.6; // Rough estimate: 60% of font size per character
        $maxChars = floor($availableWidth / $approxCharWidth);

        // Trim text if it exceeds available space
        $labelText = $this->name;
        if ($labelText && mb_strlen($labelText) > $maxChars) {
            $labelText = mb_substr($labelText, 0, $maxChars - 3) . '...'; // Keep space for "..."
        }

        return new Fragment([
            new Rect(
                x: $x,
                y: $y,
                width: $width,
                height: $chart->bottom() - $y,
                fill: $this->color,
                rx: $this->radius ?? 0,
                ry: $this->radius ?? 0,
                title: $this->value,
            ),
            $labelText ? new Text(
                content: $labelText,
                x: $labelX,
                y: $labelY,
                fontFamily: $this->fontFamily ?? $chart->fontFamily,
                fontSize: $fontSize,
                fill: $this->labelColor ?? $chart->color,
                textAnchor: 'end', // Align text so the right edge is at x
                transform: $this->labelRotation ? "rotate({$this->labelRotation} {$labelX} {$labelY})" : null,
            ) : null,
        ]);
    }

    public function value(): float
    {
        return $this->value;
    }
}
