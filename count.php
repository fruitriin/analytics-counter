<?php
require_once __DIR__ . "/vendor/autoload.php";

use AnalyticsCounter\Counter;

$counter = new Counter(__DIR__ . '/analyticsCredentials.json');
echo json_encode($counter->printResults($response));
