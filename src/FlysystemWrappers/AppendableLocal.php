<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 16:25
 */
namespace Oasis\Mlib\FlysystemWrappers;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Config;

class AppendableLocal extends Local
    implements AppendableAdapterInterface
{
    /**
     * @inheritdoc
     */
    public function __construct($root, $writeFlags = LOCK_EX, $linkHandling = self::DISALLOW_LINKS)
    {
        parent::__construct($root, $writeFlags, $linkHandling);
    }

    /**
     * @inheritdoc
     */
    public function append($path, $contents, Config $config)
    {
        if (!$this->has($path)) {
            return $this->write($path, $contents, $config);
        }

        $location = $this->applyPathPrefix($path);

        $orig     = file_get_contents($location);
        $contents = $orig . $contents;

        if (($size = file_put_contents($location, $contents, $this->writeFlags)) === false) {
            return false;
        }

        $type   = 'file';
        $result = compact('contents', 'type', 'size', 'path');

        if ($visibility = $config->get('visibility')) {
            $result['visibility'] = $visibility;
            $this->setVisibility($path, $visibility);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function appendStream($path)
    {
        $location = $this->applyPathPrefix($path);
        $this->ensureDirectory(dirname($location));
        $stream = fopen($location, 'a');

        return compact('stream', 'path');
    }
}
