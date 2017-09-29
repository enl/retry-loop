<?php declare(strict_types=1);

namespace Enl\Retry\Test;

use Enl\Retry\LoopBuilder;
use Enl\Retry\RetryLoop;

class LoopBuilderTest extends RetryLoopTest
{
    protected function buildLoop(int $tries, array $giveUpExceptions = [], $beforeRetry = null): RetryLoop
    {
        $builder = $this->createBuilder($tries, $giveUpExceptions, $beforeRetry);

        return $builder->get();
    }

    public function testRun()
    {
        $result = $this->createBuilder(5)->run($this->worker);
        $this->assertEquals(4, $result);
    }

    /**
     * @param $tries
     * @param $giveUpExceptions
     * @param $beforeRetry
     *
     * @return \Enl\Retry\LoopBuilder
     */
    protected function createBuilder(int $tries, array $giveUpExceptions = [], $beforeRetry = null): LoopBuilder
    {
        $builder = RetryLoop::builder()->retries($tries)->giveUpAt($giveUpExceptions);

        if ($beforeRetry) {
            $builder->beforeRetry($beforeRetry);
        }

        return $builder;
    }
}
