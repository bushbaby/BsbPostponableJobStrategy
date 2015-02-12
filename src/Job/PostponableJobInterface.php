<?php

namespace BsbPostponableJobStrategy\Job;

use SlmQueue\Exception\RuntimeException;
use SlmQueue\Job\JobInterface;
use Traversable;

interface PostponableJobInterface
{
    /**
     * @param JobInterface|int|array|Traversable $spec         a single or a list of Jobs or integers representing job
     *                                                         id's
     * @param int                                $releaseDelay number of seconds used as delay option while releasing
     *                                                         the job back into its queue. Note this will override the
     *                                                         option defined in the strategy.
     * @throws RuntimeException                                Thrown when a JobInterface instance has no id
     * @return int the total number of added entries
     */
    public function postponeUntil($spec, $releaseDelay = null);
}
