<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-15
 * Time: 11:45
 */
namespace Oasis\Mlib\Data;

class ArrayDataProvider extends AbstractDataProvider
{
    protected $data = [];

    function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    protected function getValue($key)
    {
        if (!array_key_exists($key, $this->data)) {
            return null;
        }

        return $this->data[$key];
    }
}
