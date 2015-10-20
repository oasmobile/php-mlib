<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-10-20
 * Time: 10:40
 */

namespace Oasis\Mlib\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

abstract class AbstractConfiguration implements ConfigurationInterface
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

    public function processConfigArray(array $configArray)
    {
        $defProcessor          = new Processor();
        $this->processedConfig = $defProcessor->processConfiguration($this, $configArray);

        $this->assignProcessedConfig();
    }

    abstract public function getConfigTreeBuilder();

    /**
     * Assigns processed config to specific properties
     */
    abstract public function assignProcessedConfig();
}
