<?php
require_once __DIR__ . "/vendor/autoload.php";

use AnalyticsCounter\Counter;

$counter = new Counter();
$counter->printResults($response);
