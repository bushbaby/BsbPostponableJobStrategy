BsbPostponableJobStrategy
==================================

BsbPostponableJobStrategy is a strategy for the [SlmQueueDoctrine](https://github.com/juriansluiman/SlmQueueDoctrine)
ZF2 module that provides the ability for jobs to postpone their execution until other jobs have successfully been 
processed.

[![Latest Stable Version](https://poser.pugx.org/bushbaby/slmqueuedoctrine-postponablejobstrategy/v/stable.svg)](https://packagist.org/packages/bushbaby/slmqueuedoctrine-postponablejobstrategy)
[![Total Downloads](https://poser.pugx.org/bushbaby/slmqueuedoctrine-postponablejobstrategy/downloads.svg)](https://packagist.org/packages/bushbaby/slmqueuedoctrine-postponablejobstrategy)
[![Latest Unstable Version](https://poser.pugx.org/bushbaby/slmqueuedoctrine-postponablejobstrategy/v/unstable.svg)](https://packagist.org/packages/bushbaby/slmqueuedoctrine-postponablejobstrategy)
[![License](https://poser.pugx.org/bushbaby/slmqueuedoctrine-postponablejobstrategy/license.svg)](https://packagist.org/packages/bushbaby/slmqueuedoctrine-postponablejobstrategy)

[![Build Status](https://travis-ci.org/bushbaby/BsbPostponableJobStrategy.svg?branch=master)](https://travis-ci.org/bushbaby/BsbPostponableJobStrategy)
[![Code Coverage](https://scrutinizer-ci.com/g/bushbaby/BsbPostponableJobStrategy/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/bushbaby/BsbPostponableJobStrategy/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bushbaby/BsbPostponableJobStrategy/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/bushbaby/BsbPostponableJobStrategy/?branch=master)

- - - - 

## Rationale

I needed a simple way to ensure a particular order of job processing. This is not a problem if you have just one worker 
as those jobs are executed in the order they where added to the queue (FIFO). However when multiple workers process 
multiple queue's this can't be garanteed anymore.

## Installation

BsbPostponableJobStrategy requires Composer. To install it into your project, just add the following line into your composer.json file:

```
composer.phar require "bushbaby/slmqueuedoctrine-postponablejobstrategy:~1.0"
```

Enable the module by adding BsbPostponableJobStrategy in your application.config.php file. 

## Configuration

By enabling this module a new strategy is registered into the strategy manager of SlmQueue. You should then enable it 
by adding it some configuration to the appropiate worker queues. A suggested place is the slm_queue.global.php in your 
autoload configuration directory.

example: enabled the PostponableJobStrategy for the queue called default with a release delay of 30 seconds

```
'worker_strategies' => array(
    'default' => array( // per worker
    ),
    'queues' => array( // per queue
        'default' => array(
            'BsbPostponableJobStrategy\Strategy\PostponableJobStrategy' => array(
                'release_delay' => 30
            ),
        ),
    ),
),
```

### PostponableJobStrategy

The PostponableJobStrategy accepts one option.

release_delay (int) number of seconds used as delay option while releasing the job back into the queue this 

## Usage

The jobs that need to 'wait' with execution until some other job is done must implement the PostponableJobInterface. I 
suggest you use the provided PostponableJobTrait.

```
class MyJob extends SlmAbstractJob implements PostponableJobInterface
{
    use PostponableJobTrait;
    
    ...
}
```

Then while queueing Jobs you tell your postponable job about jobs that should be processed first:

```
$postponableJob     = $jobManager->get('MyJob')->setContent(...);

$job     = $jobManager->get('SomeJob')->setContent(...);
$queue->push($job);

$postponableJob->postponeUntil($job);

$queue->push($postponableJob);
```

### Known limitation

SlmQueueDoctrine has specific queue options (deleted_lifetime and buried_lifetime) to delete jobs from the database when 
they have been processed. When this lifetime is immediate (or very short) it (might) becomes impossible to get the 
status of a processed job. We therefore assume it has been successfully executed and therefore the current job execution 
is not postponed.
