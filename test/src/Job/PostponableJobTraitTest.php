<?php

namespace BsbPostponableJobStrategyTest\Job;

use BsbPostponableJobStrategy\Strategy\Factory\PostponableJobStrategyFactory;
use BsbPostponableJobStrategyTest\Asset\PostponableJob;
use PHPUnit_Framework_TestCase as TestCase;

class BsbPostponableJobTraitTest extends TestCase
{

    /**
     * @var PostponableJob
     */
    private $job;

    protected function setUp()
    {
        $this->job = new PostponableJob();
    }

    /**
     * @dataProvider dpBasics
     */
    public function testBasics($spec, $exceptedCount,$releaseDelay, $exceptedReleaseDelay)
    {
        $calculatedCount = $this->job->postponeUntil($spec, $releaseDelay);

        $this->assertEquals($exceptedCount, $calculatedCount);
        $this->assertCount($exceptedCount, $this->job->getMetadata('__postponeUntil'));

        $this->assertEquals($exceptedReleaseDelay, $this->job->getMetadata('__postponeReleaseDelay'));
    }

    public function dpBasics()
    {
        $aJob = new PostponableJob();
        $aJob->setId(1);
        $bJob = new PostponableJob();
        $bJob->setId(2);

        return [
            [1, 1, null, null],
            [[1, 2, 3, 4, 3, 1], 4, null, null],
            [$aJob, 1, null, null],
            [[$aJob, 2, $bJob], 2, null, null],
            [[$aJob, $bJob, $bJob], 2, null, null],
            [1, 1, 15, 15],
            [1, 1, '15', 15],
            [1, 1, '1.5', 1],
            [1, 1, 'foo', null],
        ];
    }

    public function testThrowsExceptionJobInstanceWithNoID()
    {
        $aJob = new PostponableJob();

        $this->setExpectedException('SlmQueue\Exception\RuntimeException');
        $this->job->postponeUntil($aJob);
    }

}
