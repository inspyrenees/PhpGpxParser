<?php

namespace PhpGpxParser\Parser;

use PhpGpxParser\Models\Segment;

class SegmentParser
{
    private PointParser $pointParser;

    public function __construct(PointParser $pointParser)
    {
        $this->pointParser = $pointParser;
    }

    public function parseSegment(\SimpleXMLElement $trkseg): Segment
    {
        $segment = new Segment();

        foreach ($trkseg->trkpt as $trkpt) {
            $point = $this->pointParser->parsePoint($trkpt);
            $segment->addPoint($point);
        }

        return $segment;
    }
}

