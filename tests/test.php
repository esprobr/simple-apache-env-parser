<?php
require_once __DIR__ . '/../vendor/autoload.php';

use \Espro\SimpleApacheEnvParser\Parser;

$environment = (new Parser())->parse( __DIR__ . '/sample.env.conf' );

echo "# File: {$environment->getFile()}\n\r";
echo '# Includes found: '.count($environment->getIncludes())."\n\r";
foreach($environment->getIncludes() as $include) {
    echo "  -> {$include}\n\r";
}

$varCount = 0;
$varString = '';
$envString = '';
foreach($environment->getVariables() as $file => $vars) {
    $varString.= "  {$file}\n\r";
    foreach($vars as $key => $value) {
        $varString.= "  -> \"{$key}\" = \"{$value}\"\n\r";
        $envString.= "  -> getenv(\"{$key}\") = \"{$value}\"\n\r";
        $varCount++;
    }
}
echo "# Variables found: {$varCount}\n\r";
echo $varString;

$unprocCount = 0;
$unprocString = '';
foreach($environment->getUnprocessed() as $file => $lines) {
    $unprocString.= "  {$file}\n\r";
    foreach($lines as $line) {
        $unprocString.= "  -> $line\n\r";
        $unprocCount++;
    }
}
echo "# Unprocessed lines: {$unprocCount}\n\r";
echo $unprocString;
echo "# Check getenv \n\r";
echo $envString;

echo "\n\r";