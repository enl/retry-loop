<?php

namespace Retry;

/**
 * This exception is thrown when RetryLoop exceeds retry quota
 * @package Retry
 * @author Alex Panshin <deadyaga@gmail.com>
 */
class LoopFailed extends \RuntimeException
{

}
