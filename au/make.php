<?php

$version = '2016';
$sa3 = $sa4 = $states = [];
$lines = file('SA3_2016_AUST.csv');
// Remove header
array_shift($lines);
foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) {
        continue;
    }
    $data = explode(',', $line);
    $remainder = $data[2] % 100;
    if ($remainder == 97 || $remainder == 99) {
        continue;
    }
    $stateCode = $data[6] * 10000;
    $sa4Code = $data[2] * 100;
    $sa3Code = $data[0];
    $sa3[$sa3Code] = $data[1];
    $sa4[$sa4Code] = $data[3];
    $states[$stateCode] = $data[7];
}

$content = '<?php'.PHP_EOL;
$content.= '$version = '.var_export($version, true).';'.PHP_EOL;
$content.= '$states = '.var_export($states, true).';'.PHP_EOL;
$content.= '$sa4 = '.var_export($sa4, true).';'.PHP_EOL;
$content.= '$sa3 = '.var_export($sa3, true).';'.PHP_EOL;
file_put_contents('sa3.php', $content);

file_put_contents('sa3.json', json_encode([
    'version' => $version,
    'states' => $states,
    'sa4' => $sa4,
    'sa3' => $sa3,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$areas = [];
$files = scandir('.');
foreach ($files as $file) {
    if (substr($file, 0, 9) != 'LGA_2016_') {
        continue;
    }
    $state = substr($file, 9, -4);
    $lines = file($file);
    // Remove header
    array_shift($lines);
    foreach ($lines as $line) {
        $data = explode(',', trim($line));
        $remainder = $data[1] % 100;
        if ($remainder == 97 || $remainder == 99) {
            continue;
        }
        $areas[$data[1]] = $data[2];
    }
}
ksort($areas);

$content = '<?php'.PHP_EOL;
$content.= '$version = '.var_export($version, true).';'.PHP_EOL;
$content.= '$states = '.var_export($states, true).';'.PHP_EOL;
$content.= '$areas = '.var_export($areas, true).';'.PHP_EOL;
file_put_contents('lga.php', $content);

file_put_contents('lga.json', json_encode([
    'version' => $version,
    'states' => $states,
    'areas' => $areas,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
