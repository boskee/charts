<?php

namespace Maantje\Phpviz;

use Closure;

class Grid
{
    use MaxLabelWidth;

    public function __construct(
        public readonly int $lines = 5,
        public readonly string $lineColor = '#ccc',
        public readonly string $labelColor = '#333',
        public ?Closure $labelFormatter = null
    ) {
        if (is_null($labelFormatter)) {
            $this->labelFormatter = fn (mixed $label) => number_format($label);
        }
    }

    public function render(float $height, float $width, float $leftMargin, float $minValue, float $maxValue): string
    {
        $numLines = $this->lines;
        $lineSpacing = ($height - 50) / $numLines;
        $svg = '';

        $labelPadding = 10;
        $leftMargin = $leftMargin + $labelPadding + 10;

        for ($i = 0; $i <= $numLines; $i++) {
            $y = $height - 20 - ($i * $lineSpacing);
            $value = $minValue + (($i / $numLines) * ($maxValue - $minValue));

            $labelText = $this->labelFormatter->call($this, $value);
            $labelX = $leftMargin - $labelPadding;

            $labelY = $y + 5;
            $line = <<<SVG
            <line x1="$leftMargin" y1="$y" x2="$width" y2="$y" stroke="$this->lineColor" stroke-width="1"/>
            <text x="$labelX" y="$labelY" font-size="12" fill="$this->labelColor" text-anchor="end">$labelText</text>
            SVG;
            $svg .= $line;
        }

        return $svg;
    }
}
