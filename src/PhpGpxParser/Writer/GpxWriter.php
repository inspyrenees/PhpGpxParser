<?php

namespace PhpGpxParser\Writer;

use PhpGpxParser\Models\GpxFile;
use SimpleXMLElement;

class GpxWriter
{
    public function write(GpxFile $gpx, string $path): void
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><gpx version="1.1" creator="App" xmlns="http://www.topografix.com/GPX/1/1"/>');

        foreach ($gpx->getTracks() as $track) {
            $trk = $xml->addChild('trk');
            $trk->addChild('name', htmlspecialchars($track->getName()));

            foreach ($track->getSegments() as $segment) {
                $trkseg = $trk->addChild('trkseg');

                foreach ($segment->getPoints() as $pt) {
                    $trkpt = $trkseg->addChild('trkpt');
                    $trkpt->addAttribute('lat', (string)$pt->getLatitude());
                    $trkpt->addAttribute('lon', (string)$pt->getLongitude());

                    if ($pt->getElevation() !== null) {
                        $trkpt->addChild('ele', (string)$pt->getElevation());
                    }

                    if ($pt->getTime() !== null) {
                        $trkpt->addChild('time', $pt->getTime()->format(DATE_ATOM));
                    }
                }
            }
        }

        $xml->asXML($path);
    }
}
