<?php

namespace Retry;

/**
 * Class LoopBuilder
 * @package Retry
 * @author Alex Panshin <deadyaga@gmail.com>
 */
class LoopBuilder
{
    /**
     * @var int
     */
    private $retries;

    /**
     * @var string[]
     */
    private $giveupExceptions = [];

    /**
     * @var callable
     */
    private $beforeRetry;

    /**
     * if given exception is thrown during `run()` Loop will give up even if there are unused retries.
     *
     * @param string|array $classNames
     * @return $this
     */
    public function giveUpAt($classNames): LoopBuilder
    {
        foreach ((array)$classNames as $className) {
            $this->giveupExceptions[] = $className;
        }

        return $this;
    }

    /**
     * Hook to perform some actions before retry: reconnection, just sleep etc...
     *
     * @param callable $callable
     * @return $this
     */
    public function beforeRetry(callable $callable): LoopBuilder
    {
        $this->beforeRetry = $callable;
        return $this;
    }

    /**
     * Sets the number of retries
     * @param int $retries
     * @return $this
     */
    public function retries(int $retries): LoopBuilder
    {
        $this->retries = $retries;
        return $this;
    }

    /**
     * Returns built RetryLoop
     * @return RetryLoop
     */
    public function get(): RetryLoop
    {
        return new RetryLoop($this->retries, $this->giveupExceptions, $this->beforeRetry);
    }

    /**
     * Creates RetryLoop and runs it
     * Call of `run` does not affect internal state of nor LoopBuilder nor RetryLoop,
     * you can reuse those instances
     *
     * @param callable $worker
     * @return mixed
     * @throws \Retry\LoopFailed
     */
    public function run(callable $worker)
    {
        return $this->get()->run($worker);
    }
}
