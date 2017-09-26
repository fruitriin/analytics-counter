<?php
require_once __DIR__ . "/../vendor/autoload.php";

use AnalyticsCounter\Counter;

$_POST["id"] = "75037548";
//$_POST["start_day"] = "7daysAgo";
//$_POST["end_day"] = "today";

$_POST['days'] = [
    ['start' => '2005-01-01', 'end' => 'today'],
    ['start' => 'today', 'end' => 'today'],
    ['start' => 'yesterday', 'end' => 'yesterday'],
];

const credentialfile = __DIR__ . "/../analyticsCredential.json";
const testViewId = "e493d260bab99055cdc18bfbfc88ed513956313b";

$counter = new Counter(credentialfile);
$result = $counter->printResults();
echo json_encode($result);



