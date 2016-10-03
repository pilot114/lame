<?php

require 'Parser.php';
require 'Compiler.php';

list($cl, $iFile, $oFile) = $argv;
$iFile = $iFile ?: "source.lame";
$oFile = $oFile ?: "run.exe";

$source = file_get_contents($iFile);
$p = new Parser($source);
$c = new Compiler($p->getCode());
$dump = $c->createPE();

$fp = fopen($oFile, 'wb');
fwrite($fp, $dump);
fclose($fp);
