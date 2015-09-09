<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 16:21
 */
namespace Oasis\Mlib\FlysystemWrappers;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;

interface AppendableAdapterInterface extends AdapterInterface
{
    /**
     * Append to a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function append($path, $contents, Config $config);

    /**
     * Append to a file as a stream.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function appendStream($path);
}
