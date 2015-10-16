#! /usr/local/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 16:22
 */

use Oasis\Mlib\Cli\CommandLineArgParser;

require_once __DIR__ . "/vendor/autoload.php";

$clap = CommandLineArgParser::parser()
                            ->add('o')->aliasTo('output')->requiresValue()->usage('just output fjelkjlkj kldfjdlkjflkl fjfkdjfklf ldf dkfjdkfd kfjdkfdkfjkdlsjklf')->end()
                            ->add('p')->cannotBeFollowed()->isMandatory()->end()
                            ->add('java')->hasDefaultValue('script')->end()
                            ->run();
$clap->debug();

