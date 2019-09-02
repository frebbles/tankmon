<?php

// Client reporter for Tank Logging

$d1 = $_GET['d1'];
$d2 = $_GET['d2'];
$h = $_GET['h'];
$t = $_GET['t'];
$v = $_GET['v'];

$tstr = time();

$fd1 = fopen("./tanklogd1.csv", "a");
$fd2 = fopen("./tanklogd2.csv", "a");
$fdt = fopen("./tanklogt.csv", "a");
$fdh = fopen("./tanklogh.csv", "a");
$fdv = fopen("./tanklogv.csv", "a");

fwrite($fd1, $tstr.",".$d1."\n");
fwrite($fd2, $tstr.",".$d2."\n");
fwrite($fdt, $tstr.",".$t."\n");
fwrite($fdh, $tstr.",".$h."\n");
fwrite($fdv, $tstr.",".$v."\n");

fclose($fd1);
fclose($fd2);
fclose($fdt);
fclose($fdh);
fclose($fdv);

?>
