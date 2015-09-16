<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-16
 * Time: 20:58
 */
namespace Oasis\Mlib\SymfonyWrappers\Http;

use Symfony\Component\HttpFoundation\Request;

class JsonResponse extends \Symfony\Component\HttpFoundation\JsonResponse
{
    public function prepare(Request $request)
    {
        set_exception_handler(function (\Exception $e) {
            $this->setStatusCode(self::HTTP_INTERNAL_SERVER_ERROR);
            $this->setData([
                               "rc"      => ($e->getCode() == 0 ? 0xffff : $e->getCode()),
                               "message" => $e->getMessage(),
                           ]);
            $this->send();
            exit(1);
        });

        return parent::prepare($request);
    }

}
