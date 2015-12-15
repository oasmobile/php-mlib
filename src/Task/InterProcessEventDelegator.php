<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-10-14
 * Time: 10:07
 */

namespace Oasis\Mlib\Task;

use Oasis\Mlib\Data\DataPacker;
use Oasis\Mlib\Event\Event;
use Oasis\Mlib\Event\EventDispatcherInterface;
use Oasis\Mlib\Event\EventDispatcherTrait;

class InterProcessEventDelegator implements EventDispatcherInterface
{
    use EventDispatcherTrait;

    /** @var Runnable */
    protected $runnable;

    protected $isInChild = false;
    protected $sockets;

    /** @var DataPacker */
    protected $packer;

    function __construct(Runnable $runnable)
    {
        $this->runnable = $runnable;

        $this->sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        stream_set_blocking($this->sockets[0], 0);
        stream_set_blocking($this->sockets[1], 0);

        $this->packer = new DataPacker();
        $this->packer->attachStream($this->getStream());
    }

    function __destruct()
    {
        $this->close();
    }

    public function close()
    {
        if ($this->sockets) {
            fclose($this->sockets[0]);
            fclose($this->sockets[1]);
        }
        $this->sockets = null;
    }

    public function poll()
    {
        while ($evt = $this->receive()) {
            //mdebug("Object got in poll: " . mdump($evt));
            $this->runnable->dispatch($evt);
        }
    }

    public function send($data)
    {
        $this->packer->packToStream($data);
    }

    public function receive()
    {
        return $this->packer->unpackFromStream();
    }

    public function getStream()
    {
        return ($this->isInChild ? $this->sockets[1] : $this->sockets[0]);
    }

    public function activateInChildProcess()
    {
        $this->isInChild = true;
        $this->packer->attachStream($this->getStream());
        $this->runnable->setDelegateDispatcher($this);
    }

    public function dispatch($event, $context = null)
    {
        if (!$event instanceof Event) {
            $event = new Event(strval($event));
        }
        if ($context) {
            $event->setContext($context);
        }
        if ($this->isInChild) {
            $this->send($event);
        }
        else {
            throw new \LogicException("InterProcessEventDelegator should not dispatch any event in parent");
        }
    }
}
