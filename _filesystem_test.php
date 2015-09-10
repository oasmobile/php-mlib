#! /usr/local/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 16:22
 */

require_once __DIR__ . "/vendor/autoload.php";

$localAdapter = new League\Flysystem\Adapter\Local("/tmp");

$fs = new League\Flysystem\Filesystem($localAdapter);
$fs->put("justin", "abc");
$content = $fs->read("justin");
echo $content . PHP_EOL;

$tmp = tmpfile();
fwrite($tmp, "xyz");
$fs->putStream("justin", $tmp);
$rs     = $fs->readStream("justin");
$result = fread($rs, 10);
echo $result . PHP_EOL;
fclose($tmp);

$appendableAdapter = new \Oasis\Mlib\FlysystemWrappers\AppendableLocal("/tmp");
$afs               = new \Oasis\Mlib\FlysystemWrappers\AppendableFilesystem($appendableAdapter);

$afs->append("justin", "111" . PHP_EOL);
$content = $fs->read("justin");
echo $content . PHP_EOL;

$as = $afs->appendStream("justin");
fwrite($as, "222");
$content = $fs->read("justin");
echo $content . PHP_EOL;
