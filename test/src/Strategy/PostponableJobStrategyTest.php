<?php

namespace BsbPostponableJobStrategyTest\Strategy;

use BsbPostponableJobStrategy\Strategy\PostponableJobStrategy;
use BsbPostponableJobStrategyTest\Asset\PostponableJob;
use PHPUnit_Framework_TestCase;
use SlmQueue\Worker\WorkerEvent;
use SlmQueueDoctrine\Options\DoctrineOptions;
use SlmQueueDoctrine\Queue\DoctrineQueue;

class PostponableJobStrategyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PostponableJobStrategy
     */
    protected $listener;

    public function setUp()
    {
        $smMock         = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $this->listener = new PostponableJobStrategy($smMock);
    }

    public function testListenerInstanceOfAbstractStrategy()
    {
        $this->assertInstanceOf('SlmQueue\Strategy\AbstractStrategy', $this->listener);
    }

    public function testReleaseDelayDefault()
    {
        $this->assertTrue($this->listener->getReleaseDelay() == 15);
    }

    public function testReleaseDelaySetter()
    {
        $this->listener->setReleaseDelay(2);

        $this->assertTrue($this->listener->getReleaseDelay() == 2);
    }

    public function testListensToCorrectEvents()
    {
        $evm = $this->getMock('Zend\EventManager\EventManagerInterface');

        $evm->expects($this->at(0))->method('attach')
            ->with(WorkerEvent::EVENT_PROCESS_JOB, array($this->listener, 'onPostponeJobCheck'), PHP_INT_MAX);

        $this->listener->attach($evm);
    }

    /**
     * Ensures that handler stops when a the Job on the event does not implement PostponableJobInterface
     */
    public function testOnHandlerNonPostponableJob()
    {
        $ev = $this->getMockBuilder('SlmQueue\Worker\WorkerEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $jobMock = $this->getMock('SlmQueue\Job\JobInterface');
        $ev->expects($this->at(0))->method('getJob')->will($this->returnValue($jobMock));

        $this->listener->onPostponeJobCheck($ev);
    }

    /**
     * Data provider that simulates returning falsy values in the __postponeUntil metadata
     *
     * @return array
     */
    public function dataProviderPostponableJobWithFalsyMetadata()
    {
        return [[null], [false], [[]]];
    }

    /**
     * Ensures that handler stops when a PostponableJob has falsy __postponeUntil metadata set
     *
     * @dataProvider dataProviderPostponableJobWithFalsyMetadata
     */
    public function testPostponableJobsWithFalsyMetadata($meta)
    {
        $ev = $this->getMockBuilder('SlmQueue\Worker\WorkerEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $jobMock = $this->getMock('BsbPostponableJobStrategyTest\Asset\PostponableJob');
        $jobMock->expects($this->once())->method('getMetadata')->with('__postponeUntil')->willReturn($meta);

        $ev->expects($this->at(0))->method('getJob')->will($this->returnValue($jobMock));

        $this->listener->onPostponeJobCheck($ev);
    }

    public function dpRoutesThroughHandler()
    {
        // specs, row result set, results
        return [
            [[10, 20], [['status' => 1], ['status' => 1]], [10, 20]],
            [[10, 20], [['status' => 3], ['status' => 1]], [20]],
            [[10, 20], [['status' => 3], ['status' => 3]], []],
            [[10, 20], [null, ['status' => 3]], []],
            [[10, 20], [['status' => 1], null], [10]],
        ];
    }
    /**
     * @dataProvider dpRoutesThroughHandler
     */
    public function testRoutesThroughHandler($postponeUntilSpec, $rowResultSets, $newPostponeUntilSpecs)
    {
        $eventMock = $this->getMockBuilder('SlmQueue\Worker\WorkerEvent')->disableOriginalConstructor()->getMock();
        $queueMock = $this->getMockBuilder('SlmQueueDoctrine\Queue\DoctrineQueue')->disableOriginalConstructor()->getMock();
        $smMock    = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $options   = new DoctrineOptions();
        $connMock  = $this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock();
        $jobMock   = $this->getMock('BsbPostponableJobStrategyTest\Asset\PostponableJob');

        $jobMock->expects($this->at(0))->method('getMetadata')->with('__postponeUntil')->willReturn($postponeUntilSpec);


        $this->listener = new \BsbPostponableJobStrategyTest\Asset\PostponableJobStrategy($smMock);

        $eventMock->expects($this->at(0))->method('getJob')->will($this->returnValue($jobMock));
        $eventMock->expects($this->once())->method('getQueue')->will($this->returnValue($queueMock));
        $queueMock->expects($this->once())->method('getOptions')->willReturn($options);

        $smMock->expects($this->once())->method('get')->with('doctrine.connection.orm_default')->willReturn($connMock);

        foreach($rowResultSets as $index=>$rowResultSet) {
            $connMock->expects($this->at($index))->method('fetchAssoc')->willReturn($rowResultSet);
        }

        if (count($newPostponeUntilSpecs)) {
            $jobMock->expects($this->once())->method('setMetadata')->with('__postponeUntil', $newPostponeUntilSpecs);
            $queueMock->expects($this->once())->method('release');
            $eventMock->expects($this->once())->method('stopPropagation');
        }

        $this->listener->onPostponeJobCheck($eventMock);
    }
}
