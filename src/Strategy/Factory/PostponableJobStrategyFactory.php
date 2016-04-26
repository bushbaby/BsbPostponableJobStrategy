<?php

namespace BsbPostponableJobStrategy\Strategy\Factory;

use BsbPostponableJobStrategy\Strategy\PostponableJobStrategy;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * BsbPostponableJobStrategyFactory
 */
final class PostponableJobStrategyFactory implements FactoryInterface
{
    protected $options;

    public function __construct(array $options = null)
    {
        $this->options = $options;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return PostponableJobStrategy
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();
        $strategy       = new PostponableJobStrategy($serviceLocator, $this->options);

        return $strategy;
    }
}
