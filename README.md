# PhpGpxParser

[![Latest Stable Version](https://img.shields.io/packagist/v/inspyrenees/phpgpxparser.svg)](https://packagist.org/packages/inspyrenees/phpgpxparser)
[![Packagist downloads](https://img.shields.io/packagist/dm/inspyrenees/phpgpxparser.svg)](https://packagist.org/packages/inspyrenees/phpgpxparser)
[![PHP Version](https://img.shields.io/packagist/php-v/inspyrenees/phpgpxparser)](https://www.php.net/)

A modular GPX parser written in PHP, designed to analyze GPS tracks and enhance elevation data using the [IGN (Institut national de l'information géographique et forestière)](https://geoservices.ign.fr) API.

## Features

- Parse GPX files (tracks, segments, points)
- Calculate statistics (distance, elevation gain/loss, speed, time)
- Correct elevation using the official IGN elevation API
- Modular architecture (separate reader, writer, calculators)
- PSR-4 autoloading (compatible with modern PHP projects)

## Installation

```bash
composer require inspyrenees/phpgpxparser
```

## Requirements

- PHP 8.1+
- Symfony HTTP Client
- Geotools Library

## Basic Usage

```php
use PhpGpxParser\PhpGpxParser;

$stats = PhpGpxParser::read('/path/to/your/track.gpx')
    ->stats();

echo "Total Distance: " . $stats->getTotalDistance() . " m";
echo "Elevation Gain: " . $stats->getElevationGain() . " m";
echo "Average Speed: " . $stats->getAvgSpeed() . " km/h";
```

The parser will automatically fetch accurate elevation data for each track point using IGN's open elevation service.

## 🔗 Dependencies

- Symfony HTTP Client for robust network operations
- Geotools for precise geographical calculations

## License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.
