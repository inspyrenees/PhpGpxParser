<?php

namespace PhpGpxParser\Parser;

use PhpGpxParser\Models\GpxFile;

interface GpxParserInterface
{
    /**
     * Parse un fichier GPX et retourne un objet GpxFile.
     *
     * @param string $filePath Chemin vers le fichier GPX
     * @return GpxFile
     * @throws \InvalidArgumentException Si le fichier n'est pas lisible
     */
    public function parse(string $filePath): GpxFile;
}
