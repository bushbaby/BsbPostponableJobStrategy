<?php

namespace BsbPostponableJobStrategyTest\Asset;

use BsbPostponableJobStrategy\Strategy\PostponableJobStrategy as ExtendedPostponableJobStrategy;
use SlmQueueDoctrine\Queue\DoctrineQueueInterface;

class PostponableJobStrategy extends ExtendedPostponableJobStrategy
{
    protected function hasBeenProcessed(DoctrineQueueInterface $queue, $id)
    {
        return true;
    }
}
