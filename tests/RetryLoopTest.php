<?php declare(strict_types=1);

namespace Enl\Retry\Test;

use PHPUnit\Framework\TestCase;
use Enl\Retry\LoopFailed;
use Enl\Retry\RetryLoop;

/**
 * Class RetryLoopTest
 * @package Retry
 * @author Alex Panshin <deadyaga@gmail.com>
 */
class RetryLoopTest extends TestCase
{
    private $workerCounter = 0;
    private $retriesCounter = 0;
    protected $worker;
    protected $beforeRetry;

    protected function setUp()
    {
        parent::setUp();

        $this->retriesCounter = $this->workerCounter = 0;

        $this->worker = function() {
            $this->workerCounter++;
            if ($this->workerCounter < 4) {
                throw new StubException('Shit happened');
            }

            return $this->workerCounter;
        };

        $this->beforeRetry = function() {
            $this->retriesCounter++;
        };
    }

    public function testCallsBeforeTries()
    {
        $loop = $this->buildLoop(5, [], $this->beforeRetry);
        $loop->run($this->worker);

        $this->assertEquals(3, $this->retriesCounter, 'beforeTry should be called 3 times');
    }

    public function testGivesUpOnException()
    {
        $this->expectException(LoopFailed::class);
        $this->expectExceptionMessage('Retry loop gave up because of: Shit happened');

        $loop = $this->buildLoop(5, [StubException::class], $this->beforeRetry);
        $loop->run($this->worker);
    }

    public function testRetriesOnFailure()
    {
        $loop = $this->buildLoop(5, [], $this->beforeRetry);
        $loop->run($this->worker);

        $this->assertEquals(4, $this->workerCounter, 'worker should be called 4 times');
    }

    public function testReturnsSuccessfulResult()
    {
        $loop = $this->buildLoop(5, [], $this->beforeRetry);
        $result = $loop->run($this->worker);

        $this->assertEquals(4, $result, 'result should be equal 4');
    }

    public function testThrowsLoopFailed()
    {
        $this->expectException(LoopFailed::class);
        $this->expectExceptionMessage('Loop failed after 2 retries');

        $loop = $this->buildLoop(2);
        $loop->run($this->worker);
    }

    public function testInvalidArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid `tries` argument for RetryLoop.');

        $this->buildLoop(0);
    }

    /**
     * @param $tries
     * @param $giveUpExceptions
     * @param $beforeRetry
     *
     * @return RetryLoop
     */
    protected function buildLoop(int $tries, array $giveUpExceptions = [], $beforeRetry = null): RetryLoop
    {
        return new RetryLoop($tries, $giveUpExceptions, $beforeRetry);
    }
}
