<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-16
 * Time: 19:02
 */

namespace Oasis\Mlib\Resources;

use League\Flysystem\Plugin\ListPaths;
use Oasis\Mlib\FlysystemWrappers\AppendableFilesystem;
use Oasis\Mlib\FlysystemWrappers\AppendableLocal;
use Symfony\Component\Finder\Finder;

abstract class AbstractLocalDataStorageResource extends AbstractResourcePoolBase
{
    public function createResource($key = '')
    {
        $adapter = new AppendableLocal($this->getConfig($key));
        $fs      = new AppendableFilesystem($adapter);
        $fs->addPlugin(new ListPaths());

        return $fs;
    }

    public function finder($key = '')
    {
        /** @var AppendableFilesystem $fs */
        $fs = $this->getResource($key);
        /** @var AppendableLocal $adapter */
        $adapter = $fs->getAdapter();

        $finder = new Finder();
        $finder->in($adapter->applyPathPrefix(""));

        return $finder;
    }

    /**
     * @param string $key
     *
     * @return AppendableFilesystem
     */
    public function getResource($key = '')
    {
        return parent::getResource($key);
    }

}
