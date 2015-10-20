<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-16
 * Time: 14:33
 */
namespace Oasis\Mlib\Config;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractYamlConfiguration extends AbstractConfiguration
{
    public function loadYaml($filename, $directories)
    {
        $locator   = new FileLocator($directories);
        $yamlFiles = $locator->locate($filename, null, false);

        $rawData = [];
        foreach ($yamlFiles as $file) {
            $config    = Yaml::parse($file);
            $rawData[] = $config;
        }

        $this->processConfigArray($rawData);
    }

}
