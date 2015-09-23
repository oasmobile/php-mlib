<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-23
 * Time: 15:46
 */

namespace Oasis\Mlib\Resources;

abstract class AbstractResourcePoolBase implements ResourcePoolInterface
{
    /**
     * @var array
     */
    protected $resources = [];

    public static function instance()
    {
        /** @var static[] $instances */
        static $instances = [];
        if (!$instances[static::class] instanceof static) {
            $instances[static::class] = new static;
        }

        return $instances[static::class];
    }

    public function getResource($key = '')
    {
        if ($this->resources[$key]) {
            return $this->resources[$key];
        }

        $this->resources[$key] = $this->createResource($key);

        return $this->resources[$key];
    }
}
