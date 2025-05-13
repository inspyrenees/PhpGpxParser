<?php
namespace PhpGpxParser\Parser;

use PhpGpxParser\Models\GpxFile;
use PhpGpxParser\Exception\GpxParserException;

class GpxReader
{
    private TrackParser $trackParser;

    public function __construct(TrackParser $trackParser)
    {
        $this->trackParser = $trackParser;
    }

    /**
     * Lit un fichier GPX depuis un chemin et le convertit en objet GpxFile
     *
     * @param string $path Chemin vers le fichier GPX
     * @return GpxFile
     * @throws GpxParserException Si le fichier est invalide ou inaccessible
     */
    public function readFromFile(string $path): GpxFile
    {
        if (!file_exists($path)) {
            throw new GpxParserException("Le fichier GPX n'existe pas: $path");
        }

        $xmlContent = file_get_contents($path);
        if ($xmlContent === false) {
            throw new GpxParserException("Impossible de lire le fichier GPX: $path");
        }

        return $this->readFromString($xmlContent);
    }

    /**
     * Lit un contenu XML GPX et le convertit en objet GpxFile
     *
     * @param string $xmlContent Contenu XML du GPX
     * @return GpxFile
     * @throws GpxParserException Si le XML est invalide
     */
    public function readFromString(string $xmlContent): GpxFile
    {
        $xml = simplexml_load_string($xmlContent);
        if ($xml === false) {
            dd($xml);
            throw new GpxParserException("Format XML invalide");
        }

        return $this->readFromXml($xml);
    }

    /**
     * Convertit un SimpleXMLElement en objet GpxFile
     *
     * @param \SimpleXMLElement $xml Élément XML du GPX
     * @return GpxFile
     */
    public function readFromXml(\SimpleXMLElement $xml): GpxFile
    {
        $gpx = new GpxFile();

        foreach ($xml->trk as $trk) {
            $track = $this->trackParser->parseTrack($trk);
            $gpx->addTrack($track);
        }

        return $gpx;
    }
}
