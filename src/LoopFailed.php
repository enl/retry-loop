<?php declare(strict_types=1);

namespace Enl\Retry;

/**
 * This exception is thrown when RetryLoop exceeds retry quota
 * @package Retry
 * @author Alex Panshin <deadyaga@gmail.com>
 */
class LoopFailed extends \RuntimeException
{

}
