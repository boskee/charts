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

        if (!is_null($this->width)) {
            $x += ($maxBarWidth - $width) / 2;
        }

        // **Determine label position**
        $labelX = $this->labelRotation ? $x - ($width / 2) : $x + $width / 2;

        // **Shorten label only if a name is present**
        $shortenedName = null;
        if (!empty(trim($this->name))) {
            $maxLabelWidth = $this->labelRotation ? $chart->availableHeight() : ($maxBarWidth * 1.5);
            $maxLabelHeight = $chart->bottomSpace(); // Space below bars for labels

            $shortenedName = $this->shortenLabel(
                $this->fontSize ?? $chart->fontSize,
                $maxLabelWidth,
                $maxLabelHeight,
                $labelX // Pass label position to prevent negative x
            );
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
            $shortenedName ? new Text(
                content: $shortenedName,
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

    /**
     * Shortens the label incrementally if it exceeds the available space in both X and Y dimensions.
     *
     * @param int $fontSize The font size
     * @param float $maxWidth The maximum width allowed
     * @param float $maxHeight The maximum height allowed
     * @param float $labelX The label's current x-position
     * @return string The shortened text with ellipsis if needed
     */
    private function shortenLabel(int $fontSize, float $maxWidth, float $maxHeight, float $labelX): string
    {
        if (empty(trim($this->name))) {
            return '';
        }

        $ellipsis = '...';
        $minChars = 3; // Ensure we always keep at least a few characters
        $text = $this->name;

        // Approximate character width per font size
        $avgCharWidth = $fontSize * 0.6;
        $textWidth = strlen($text) * $avgCharWidth;
        $textHeight = $fontSize;

        // Convert rotation to radians
        $angle = deg2rad($this->labelRotation ?? 0);

        // Compute rotated bounding box dimensions
        $rotatedWidth = abs($textWidth * cos($angle)) + abs($textHeight * sin($angle));
        $rotatedHeight = abs($textWidth * sin($angle)) + abs($textHeight * cos($angle));

        // **Check if it fits immediately in both dimensions**
        if (
            $rotatedWidth <= $maxWidth &&
            $rotatedHeight <= $maxHeight &&
            $labelX - ($rotatedWidth / 2) >= 0
        ) {
            return $text; // No truncation needed
        }

        // **Ensure label stays within the chart and does not overflow left**
        $availableWidth = min($maxWidth, $labelX * 2); // Prevent negative x overflow
        $maxEllipsisWidth = strlen($ellipsis) * $avgCharWidth;

        // **Iteratively shorten text until it fits both X & Y constraints**
        while (strlen($text) > $minChars) {
            $shortenedText = substr($text, 0, -1); // Remove last character
            $shortenedWidth = strlen($shortenedText) * $avgCharWidth;
            $ellipsizedWidth = $shortenedWidth + $maxEllipsisWidth;

            $rotatedEllipsizedWidth = abs($ellipsizedWidth * cos($angle)) + abs($textHeight * sin($angle));
            $rotatedEllipsizedHeight = abs($ellipsizedWidth * sin($angle)) + abs($textHeight * cos($angle));

            // **If it fits within both X & Y space, return it**
            if (
                $rotatedEllipsizedWidth <= $availableWidth &&
                $rotatedEllipsizedHeight <= $maxHeight
            ) {
                return $shortenedText . $ellipsis; // Once it fits, return it with "..."
            }

            // Continue shortening
            $text = $shortenedText;
        }

        return substr($text, 0, $minChars) . $ellipsis; // Ensure we always return a meaningful value
    }

    public function value(): float
    {
        return $this->value;
    }
}