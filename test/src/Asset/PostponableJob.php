<?php

namespace BsbPostponableJobStrategyTest\Asset;

use BsbPostponableJobStrategy\Job\PostponableJobInterface;
use BsbPostponableJobStrategy\Job\PostponableJobTrait;
use SlmQueue\Job\AbstractJob;

class PostponableJob extends AbstractJob implements PostponableJobInterface
{
    use PostponableJobTrait;

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
    }
}
