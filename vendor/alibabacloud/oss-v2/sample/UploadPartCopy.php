<?php

require_once __DIR__ . '/../vendor/autoload.php';

use AlibabaCloud\Oss\V2 as Oss;

// parse args
$optsdesc = [
    "region" => ['help' => 'The region in which the bucket is located.', 'required' => True],
    "endpoint" => ['help' => 'The domain names that other services can use to access OSS.', 'required' => False],
    "bucket" => ['help' => 'The name of the bucket', 'required' => True],
    "key" => ['help' => 'The name of the object', 'required' => True],
    "src-bucket" => ['help' => 'The name of the source bucket', 'required' => False],
    "src-key" => ['help' => 'The name of the source object', 'required' => True],
];
$longopts = \array_map(function ($key) {
    return "$key:";
}, array_keys($optsdesc));
$options = getopt("", $longopts);
foreach ($optsdesc as $key => $value) {
    if ($value['required'] === True && empty($options[$key])) {
        $help = $value['help'];
        echo "Error: the following arguments are required: --$key, $help";
        exit(1);
    }
}

$region = $options["region"];
$bucket = $options["bucket"];
$key = $options["key"];
$srcKey = $options["src-key"];

// Loading credentials values from the environment variables
$credentialsProvider = new Oss\Credentials\EnvironmentVariableCredentialsProvider();

// Using the SDK's default configuration
$cfg = Oss\Config::loadDefault();
$cfg->setCredentialsProvider($credentialsProvider);
$cfg->setRegion($region);
if (isset($options["endpoint"])) {
    $cfg->setEndpoint($options["endpoint"]);
}

$client = new Oss\Client($cfg);

$initRequest = new Oss\Models\InitiateMultipartUploadRequest($bucket, $key);
$initResult = $client->initiateMultipartUpload($initRequest);

if (!empty($options["src-bucket"])) {
    $srcBucket = $options["src-bucket"];
} else {
    $srcBucket = $bucket;
}

$headResult = $client->headObject(new Oss\Models\HeadObjectRequest(
    $srcBucket,
    $srcKey
));

$length = $headResult->contentLength;

$partSize = 64 * 1024 * 1024;
$partsNum = intdiv($length, $partSize) + intval(1);

$parts = array();

for ($i = 1; $i <= $partsNum; $i++) {
    $partRequest = new Oss\Models\UploadPartCopyRequest(
        $bucket,
        $key,
        $i,
        $initResult->uploadId
    );

    $partRequest->sourceRange = getPartRange($length, $partSize, $i);

    $partRequest->sourceBucket = $srcBucket;
    $partRequest->sourceKey = $srcKey;
    $partResult = $client->uploadPartCopy($partRequest);
    $part = new Oss\Models\UploadPart(
        $i,
        $partResult->etag,
    );
    array_push($parts, $part);
}

printf(
    "upload part copy success\n"
);

function getPartRange(int $totalSize, int $partSize, int $partNumber): string
{
    $start = ($partNumber - 1) * $partSize;
    $end = min($partNumber * $partSize - 1, $totalSize - 1);
    return sprintf('bytes=%d-%d', $start, $end);
}