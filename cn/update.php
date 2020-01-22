<?php

$lines = file('GB-T2260.txt');
// Version
list($label, $version) = explode("\t", trim(array_shift($lines)));
if ($label !== 'VER') {
    echo 'Invalid version';
    exit(1);
}
if (file_exists('patch.txt')) {
    $patch = file('patch.txt');
    $lines = array_merge($lines, $patch);
}
foreach ($lines as $line) {
    list($code, $name) = explode("\t", trim($line));
    $code = intval($code);
    if ($code % 10000 == 0) {
        $states[$code] = $name;
    } else if ($code % 100 == 0) {
        $cities[$code] = $name;
    } else {
        $areas[$code] = $name;
    }
}
ksort($states);
ksort($cities);
ksort($areas);

$content = '<?php'.PHP_EOL;
$content.= '$version = '.var_export($version, true).';'.PHP_EOL;
$content.= '$states = '.var_export($states, true).';'.PHP_EOL;
$content.= '$cities = '.var_export($cities, true).';'.PHP_EOL;
$content.= '$areas = '.var_export($areas, true).';'.PHP_EOL;
file_put_contents('gb-t2260.php', $content);

file_put_contents('gb-t2260.json', json_encode([
    'version' => $version,
    'states' => $states,
    'cities' => $cities,
    'areas' => $areas,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));