<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-23
 * Time: 15:38
 */

namespace Oasis\Mlib\Resources;

interface ResourcePoolInterface
{
    public function createResource($key = '');

    public function getResource($key = '');

    public function getConfig($key = '');
}
