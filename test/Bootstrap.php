<?php

namespace BsbSlmQueueDoctrineWaitForStrategyTest;

use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

/**
 * Test bootstrap
 */
class Bootstrap
{
    /**
     * @var ServiceManager
     */
    protected static $serviceManager;

    public static function init()
    {
        error_reporting(E_ALL | E_STRICT);
        chdir(__DIR__ . '/..');

        include __DIR__ . '/../vendor/autoload.php';

        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', self::getApplicationConfig());
        $serviceManager->get('ModuleManager')->loadModules();

        static::$serviceManager = $serviceManager;
    }

    public static function getApplicationConfig()
    {
        $config = [];

        if (!$config = @include __DIR__ . '/TestConfiguration.php') {
            $config = require __DIR__ . '/TestConfiguration.php.dist';
        }

        return $config;
    }

    /**
     * @return ServiceManager
     */
    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

}

Bootstrap::init();
