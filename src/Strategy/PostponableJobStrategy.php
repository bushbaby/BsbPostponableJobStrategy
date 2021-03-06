<?php

namespace BsbPostponableJobStrategy\Strategy;

use BsbPostponableJobStrategy\Job\PostponableJobInterface;
use Doctrine\DBAL\Types\Type;
use SlmQueue\Strategy\AbstractStrategy;
use SlmQueue\Worker\WorkerEvent;
use SlmQueueDoctrine\Queue\DoctrineQueue;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PostponableJobStrategy extends AbstractStrategy
{

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var int Time in seconds we use for the delay option while releasing a job back into the queue
     */
    protected $releaseDelay = 15;

    public function __construct(ServiceLocatorInterface $serviceLocator, array $options = null)
    {
        $this->serviceLocator = $serviceLocator;

        parent::__construct($options);
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(
            WorkerEvent::EVENT_PROCESS_JOB,
            [$this, 'onPostponeJobCheck'],
            PHP_INT_MAX
        );
    }

    /**
     * @return int
     */
    public function getReleaseDelay()
    {
        return $this->releaseDelay;
    }

    /**
     * @param int $releaseDelay
     */
    public function setReleaseDelay($releaseDelay)
    {
        $this->releaseDelay = $releaseDelay;
    }

    /**
     * @param WorkerEvent $event
     * @return void
     */
    public function onPostponeJobCheck(WorkerEvent $event)
    {
        $job = $event->getJob();

        if (!$job instanceof PostponableJobInterface) {
            return;
        }

        if (!$postponeUntil = $job->getMetadata('__postponeUntil')) {
            return;
        }

        /** @var DoctrineQueue $queue */
        $queue        = $event->getQueue();
        $queueOptions = $queue->getOptions();
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->serviceLocator->get($queueOptions->getConnection());
        $queueOptions->getTableName();

        // remove id's that seem to have been processed
        $postponeUntil = array_filter($postponeUntil, function ($id) use ($connection, $queueOptions) {
            $sql = 'SELECT `status` FROM ' . $queueOptions->getTableName() . ' WHERE id = ?';
            $row = $connection->fetchAssoc($sql, [$id], [Type::SMALLINT]);

            // assume garbage collection has occured and job was processed successfully
            if (!$row) {
                return false;
            }

            return $row['status'] != DoctrineQueue::STATUS_DELETED;
        });

        // check for postponeUntil's that have been buried (failed by exception) fail this one too
        $ok = null;
        foreach ($postponeUntil as $key => $postponeUntilId) {
            $sql = 'SELECT `status` FROM ' . $queueOptions->getTableName() . ' WHERE id = ?';
            $row = $connection->fetchAssoc($sql, [$postponeUntilId], [Type::SMALLINT]);

            // assume garbage collection has occured and job was processed successfully
            if (!$row) {
                continue;
            }

            $ok = $row['status'] != DoctrineQueue::STATUS_BURIED;

            if ($ok === false) {
                $queue->bury(
                    $job,
                    [
                        'message' => sprintf(
                            'This postponed job has been buried because it depends on the execution ' .
                            'of a job (%s) that has been buried',
                            $postponeUntilId
                        )
                    ]
                );
                $event->stopPropagation();
                return;
            }
        }

        if ($postponeUntil) {
            $postponeUntil = array_values($postponeUntil);
            // store still to be processed job's
            $job->setMetadata('__postponeUntil', $postponeUntil);
            $queue->release(
                $job,
                ['delay' => $job->getMetadata('__postponeReleaseDelay', $this->releaseDelay)]
            );
            $event->stopPropagation();
        }
    }
}
