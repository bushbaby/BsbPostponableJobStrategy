<?php

namespace BsbPostponableJobStrategyTest;

use BsbPostponableJobStrategy\Module;
use BsbPostponableJobStrategyTest\Framework\TestCase;

class ModuleTest extends TestCase
{
    public function testFeatureConfigProvider()
    {
        $module = new Module();

        $this->assertInstanceOf('Zend\ModuleManager\Feature\ConfigProviderInterface', $module);
        $this->assertNotEmpty($module->getConfig());
    }

    public function testFeatureDependencyIndicator()
    {
        $module = new Module();

        $this->assertInstanceOf('Zend\ModuleManager\Feature\DependencyIndicatorInterface', $module);
        $this->assertContains('SlmQueueDoctrine', $module->getModuleDependencies());
    }
}
