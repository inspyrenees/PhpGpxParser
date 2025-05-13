<?php

namespace PhpGpxParser\Parser;

use PhpGpxParser\Models\Track;
use SimpleXMLElement;

class TrackParser
{
    private SegmentParser $segmentParser;

    public function __construct(SegmentParser $segmentParser)
    {
        $this->segmentParser = $segmentParser;
    }

    public function parseTrack(\SimpleXMLElement $trk): Track
    {
        $track = new Track();
        $track->setName(isset($trk->name) ? (string)$trk->name : '');

        foreach ($trk->trkseg as $trkseg) {
            $segment = $this->segmentParser->parseSegment($trkseg);
            $track->addSegment($segment);
        }

        return $track;
    }
}
