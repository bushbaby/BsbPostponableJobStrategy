<?php

namespace BsbPostponableJobStrategy\Job;

use SlmQueue\Exception\RuntimeException;
use SlmQueue\Job\JobInterface;
use Traversable;

trait PostponableJobTrait
{
    /**
     * @inheritdoc
     */
    public function postponeUntil($spec, $releaseDelay = null)
    {
        $added = 0;

        if (is_integer($spec)) {
            $added += $this->addPostponeUntil($spec);
        }

        if ($spec instanceof JobInterface) {
            if (!$spec->getId()) {
                throw new RuntimeException(
                    "JobInterface does not have an id. You should push it to a queue first."
                );
            }

            $added += $this->addPostponeUntil($spec->getId());
        }

        if (is_array($spec) || $spec instanceof Traversable) {
            foreach ($spec as $value) {
                $added += $this->postponeUntil($value);
            }
        }

        if ((int) $releaseDelay) {
            $this->setMetadata('__postponeReleaseDelay', (int) $releaseDelay);
        }

        return $added;
    }

    /**
     * @param int $jobId An id of a job
     * @return int the number of the id's added
     */
    private function addPostponeUntil($jobId)
    {
        $postpone   = $this->getMetadata('__postponeUntil', []);
        $count      = count($postpone);
        $postpone[] = (int) $jobId;
        $postpone   = array_unique($postpone);

        $this->setMetadata('__postponeUntil', $postpone);

        return count($postpone) - $count;
    }

    /**
     * Set metadata
     *
     * @param  string|int|array|\Traversable $spec
     * @param  mixed $value
     */
    abstract function setMetadata($spec, $value = null);

    /**
     * Get metadata
     *
     * @param  null|string|int $key
     * @return mixed
     */
    abstract function getMetadata($key = null, $default = null);
}
