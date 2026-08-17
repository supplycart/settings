<?php

declare(strict_types=1);

$report = $argv[1] ?? '.phpunit.cache/coverage.xml';
$minimum = (float) ($argv[2] ?? 85);
$document = new DOMDocument;

if (! $document->load($report)) {
    fwrite(STDERR, "Unable to read Clover coverage report: {$report}\n");
    exit(1);
}

$metrics = (new DOMXPath($document))->query('/coverage/project/metrics')->item(0);
$statements = (int) $metrics?->attributes?->getNamedItem('statements')?->nodeValue;
$covered = (int) $metrics?->attributes?->getNamedItem('coveredstatements')?->nodeValue;

if ($statements === 0) {
    fwrite(STDERR, "Coverage report contains no executable lines.\n");
    exit(1);
}

$coverage = ($covered / $statements) * 100;
printf("Line coverage: %.2f%% (%d/%d), required: %.2f%%\n", $coverage, $covered, $statements, $minimum);
exit($coverage + 0.00001 >= $minimum ? 0 : 1);
