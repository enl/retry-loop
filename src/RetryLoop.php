<?php

namespace Retry;

/**
 * Class RetryLoop
 * @package Retry
 * @author Alex Panshin <deadyaga@gmail.com>
 */
class RetryLoop
{
    /**
     * @var
     */
    private $tries;

    /**
     * @var string[]
     */
    private $giveupExceptions;

    /**
     * @var callable
     */
    private $beforeRetry;

    public function __construct(int $tries, array $giveupExceptions = [], callable $beforeRetry = null)
    {
        if ($tries < 1) {
            throw new \InvalidArgumentException('Invalid `tries` argument for RetryLoop.');
        }

        $this->tries = $tries;
        $this->giveupExceptions = $giveupExceptions;
        $this->beforeRetry = $beforeRetry;
    }

    public static function builder(): LoopBuilder
    {
        return new LoopBuilder();
    }

    /**
     * Runs RetryLoop with given callable as a worker.
     *
     * @param callable $closure
     * @return mixed
     * @throws LoopFailed if tries quota is exceed
     */
    public function run(callable $closure)
    {
        return $this->doRun($this->tries, $closure);
    }

    /**
     * tail-recursive runner of given $closure.
     * It is extracted into separate function in order to make RetryLoop immutable, so you can reuse it.
     *
     * @param $tries
     * @param $closure
     * @param \Exception|null $e
     *
     * @return mixed
     * @throws LoopFailed if tries quota is exceeded
     */
    private function doRun($tries, $closure, \Exception $e = null)
    {
        if ($tries === 0) {
            throw new LoopFailed('Loop failed', 0, $e);
        }

        try {
            return $closure();
        } catch (\Exception $e) {
            // First of all, check whether we should give up
            foreach ($this->giveupExceptions as $exceptionClass) {
                if ($e instanceof $exceptionClass) {
                    throw new LoopFailed('Retry loop gave up because of: ' . $e->getMessage(), 0, $e);
                }
            }

            if ($this->beforeRetry) {
                call_user_func($this->beforeRetry, $e);
            }

            return $this->doRun($tries - 1, $e);
        }
    }
}
