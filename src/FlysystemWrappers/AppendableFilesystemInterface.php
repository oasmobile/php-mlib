<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-09
 * Time: 16:21
 */
namespace Oasis\Mlib\FlysystemWrappers;

use League\Flysystem\FilesystemInterface;

interface AppendableFilesystemInterface extends FilesystemInterface
{
    /**
     * Append to a file.
     *
     * @param string $path     The path of the file.
     * @param string $contents The contents to be appended.
     * @param array  $config   An optional configuration array.
     *
     * @throws FileExistsException
     *
     * @return bool True on success, false on failure.
     */
    public function append($path, $contents, array $config = []);

    /**
     * Retrieves an append-stream for a path.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     *
     * @return resource|false The path resource or false on failure.
     */
    public function appendStream($path);
}
