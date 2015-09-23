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

abstract class AbstractLocalDataStorageResource extends AbstractResourcePoolBase
{
    public function createResource($key = '')
    {
        $adapter = new AppendableLocal($this->getConfig($key));
        $fs      = new AppendableFilesystem($adapter);
        $fs->addPlugin(new ListPaths());

        return $fs;
    }
}
