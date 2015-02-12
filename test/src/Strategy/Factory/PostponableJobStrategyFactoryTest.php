<?php

namespace BsbPostponableJobStrategyTest\Factory;

use BsbPostponableJobStrategy\Strategy\Factory\PostponableJobStrategyFactory;
use PHPUnit_Framework_TestCase as TestCase;

class BsbPostponableJobStrategyFactoryTest extends TestCase
{

    public function testCreateService()
    {
        $smPluginMock = $this->getMock('Zend\ServiceManager\AbstractPluginManager');
        $smMock       = $this->getMock('Zend\ServiceManager\ServiceManager');
        $smPluginMock->expects($this->once())->method('getServiceLocator')->willReturn($smMock);

        $factory = new PostponableJobStrategyFactory();
        $service = $factory->createService($smPluginMock);

        $this->assertInstanceOf('BsbPostponableJobStrategy\Strategy\PostponableJobStrategy', $service);
    }

    public function testCreateServiceWithConstructorOptions()
    {
        $smPluginMock = $this->getMock('Zend\ServiceManager\AbstractPluginManager');
        $smMock       = $this->getMock('Zend\ServiceManager\ServiceManager');
        $smPluginMock->expects($this->once())->method('getServiceLocator')->willReturn($smMock);

        $factory = new PostponableJobStrategyFactory(['release_delay' => 100]);
        $service = $factory->createService($smPluginMock);

        $this->assertEquals(100, $service->getReleaseDelay());
    }

}
