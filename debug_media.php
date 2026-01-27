<?php

use App\Models\Report;

$report = Report::latest()->first();

if (!$report) {
    echo "No reports found.\n";
    exit;
}

echo "Report ID: {$report->id}\n";
echo "Report Name: {$report->name}\n";
echo "isComplete: " . ($report->isComplete() ? 'Yes' : 'No') . "\n";
echo "Has Media 'xml_files': " . ($report->hasMedia('xml_files') ? 'Yes' : 'No') . "\n";

$mediaItems = $report->getMedia('reports');
echo "Media in 'reports' collection: " . $mediaItems->count() . "\n";

foreach ($mediaItems as $media) {
    echo " - ID: {$media->id}\n";
    echo "   Name: {$media->name}\n";
    echo "   File Name: {$media->file_name}\n";
    echo "   Mime Type: {$media->mime_type}\n";
    echo "   Collection: {$media->collection_name}\n";
    echo "   Disk: {$media->disk}\n";
    echo "   Size: {$media->size}\n";
    echo "   Path exists: " . (file_exists($media->getPath()) ? 'Yes' : 'No') . "\n";
}
