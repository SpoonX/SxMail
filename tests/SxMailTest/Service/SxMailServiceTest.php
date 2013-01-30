<?php

namespace SxMailTest;

use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config as ServiceManagerConfig;

class SxMailServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * Supply other tests with a configured ServiceManager.
     *
     * @return  \Zend\ServiceManager\ServiceManager
     */
    protected function getServiceManager()
    {
        $serviceManager = new ServiceManager(
            new ServiceManagerConfig(include __DIR__ . '/_files/services.config.php')
        );

        $viewRenderer   = $this->getMock('Zend\View\Renderer\RendererInterface', array('render', 'getEngine', 'setResolver'));
        $viewManager    = $this->getMock('Zend\Mvc\View\Console\ViewManager', array('getRenderer'));

        $viewManager
                ->expects($this->once())
                ->method('getRenderer')
                ->will($this->returnValue($viewRenderer));

        $serviceManager->setService('view_manager', $viewManager);
        $serviceManager->setService('Config', include __DIR__ . '/_files/module.config.php');

        return $serviceManager;
    }

    /**
     * This test checks if the exception gets triggered when there's no config.
     */
    public function testConstructFallbackNoConfig()
    {
        $serviceManager = $this->getServiceManager();

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Config', array());

        $sxmail = $serviceManager->get('SxMail\Service\SxMail');

        $this->assertEquals(array(), $sxmail->getConfig());
    }

    /**
     * Test the constructor and the service factory
     */
    public function testConstructAndServiceFactory()
    {
        $serviceManager = $this->getServiceManager();
        $sxMail         = $serviceManager->get('SxMail\Service\SxMail');

        $this->assertInstanceOf('SxMail\Service\SxMailService', $sxMail);
    }

    /**
     * Test the merged config
     */
    public function testMergedConfig()
    {
        $serviceManager = $this->getServiceManager();
        $sxMail         = $serviceManager->get('SxMail\Service\SxMail');
        $config         = $sxMail->getConfig(null);
        $moduleConfig   = include __DIR__ . '/_files/module.config.php';
        $mergedConfig   = $sxMail->getConfig('mergedConfig');

        $this->assertEquals($moduleConfig['sxmail']['configs']['default'], $config);
        $this->assertEquals($moduleConfig['sxmail']['configs']['mergedConfig'], $mergedConfig);
    }

    /**
     * Test if prepare works.
     */
    public function testPrepare()
    {
        $serviceManager = $this->getServiceManager();
        $sxMail         = $serviceManager->get('SxMail\Service\SxMail');
        $sxMailInstance = $sxMail->prepare();

        $this->assertInstanceOf('SxMail\SxMail', $sxMailInstance);
    }
}
