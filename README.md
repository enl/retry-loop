RetryLoop
=========

Retry Loop is widely used concept which can be easily explained with "retry on failure and give up after several retries".

Installation
------------

```bash
composer require enl/retry-loop
```

Usage
-----

For example, we need to push some data into remote service with the following pseudocode:

```php
$response = $serviceClient->push($uri, $data);
```

But what if this remove service is temporarily down or network connection is unstable in this particular moment?

The common solution is to use `try-catch`. But what about several retries?

This code looks much better and does the trick:

```php
$loop = new RetryLoop($retries = 5);
$response = $loop->run(function() use ($serviceClient, $uri, $data) {
    return $serviceClient->push($uri, $data);
});
```

Give up on some exceptions
--------------------------

Sometimes, you need to give up on several exceptions, for example, if your client throws 'BadRequestException', you should give up trying to push the data. In order to achieve this, you can specify second parameter for `RetryLoop` constructor:

```php
$loop = new RetryLoop($retries = 5, $giveUpAt = [BadRequestException::class]);
$response = $loop->run(function() use ($serviceClient, $uri, $data) {
    return $serviceClient->push($uri, $data);
});
```

In case of `BadRequestException`, `RetryLoop` will throw `LoopFailed` exception with actual exception as previous.

Before Retry hook
-----------------

If you need to perform some action before retry (logging, reconnection, whatever you need), just use `$beforeRetry` parameter:

```php
$loop = new RetryLoop($retries = 5, $giveUpAt = [BadRequestException::class], function($e) {
    $this->log('info', 'Exception caught by retry loop, retrying: '.$e->getMessage());
});
$response = $loop->run(function() use ($serviceClient, $uri, $data) {
    return $serviceClient->push($uri, $data);
});
```

RetryLoop is immutable!
-----------------------

RetryLoop class itself is immutable and does not store any internal state except given constructor parameters, so that you can easily reuse single loop instance for several retries, if it is necessary

### Builder

Moreover, there is a `LoopBuilder` that provides fluent interface for loop building:

```php
$loop = RetryLoop::builder
    ->retries(5)
    ->giveUpAt(BadRequestException::class)
    ->giveUpAt(SomeOtherException::class)
    ->giveUpAt([YetAnotherException::class])
    ->beforeRetry(function() { sleep(5); })
    ->get(); // or just `run` and get result if builder is not needed after that.
```


