<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-16
 * Time: 14:33
 */
namespace Oasis\Mlib\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractYamlConfiguration implements ConfigurationInterface
{
    protected $processedConfig = [];

    /**
     * @return static
     */
    public static function instance()
    {
        static $instances = [];
        if ($instances[static::class] === null) {
            $instances[static::class] = new static();
        }

        return $instances[static::class];
    }

    public function loadYaml($filename, $directories)
    {
        $locator   = new FileLocator($directories);
        $yamlFiles = $locator->locate($filename, null, false);

        $rawData = [];
        foreach ($yamlFiles as $file) {
            $config    = Yaml::parse($file);
            $rawData[] = $config;
        }

        $defProcessor          = new Processor();
        $this->processedConfig = $defProcessor->processConfiguration($this, $rawData);

        $this->assignProcessedConfig();
    }

    abstract public function getConfigTreeBuilder();

    /**
     * Assigns processed config to specific properties
     */
    abstract public function assignProcessedConfig();
}
