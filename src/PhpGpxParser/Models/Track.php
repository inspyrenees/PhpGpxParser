<?php

namespace PhpGpxParser\Models;

class Track
{
    /** @var Segment[] */
    private array $segments = [];
    private string $name = '';

    public function addSegment(Segment $segment): void
    {
        $this->segments[] = $segment;
    }

    /**
     * @return Segment[]
     */
    public function getSegments(): array
    {
        return $this->segments;
    }

    public function setName(string $trackName): void
    {
        $this->name = $trackName;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
