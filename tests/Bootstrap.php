<?php

use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\Loader\StandardAutoloader;

chdir(__DIR__);

$previousDir = '.';

while (!file_exists('config/application.config.php')) {
    $dir = dirname(`pwd`);


    if ($previousDir === $dir) {
        throw new RuntimeException(
            'Unable to locate "config/application.config.php":'
            . ' is SxMail in a sub-directory of your application skeleton?'
        );
    }

    $previousDir = $dir;
    chdir($dir);
}

if (!(@include_once __DIR__ . '/../../vendor/autoload.php') && !@(include_once __DIR__ . '/../../../autoload.php')) {
    throw new RuntimeException('vendor/autoload.php could not be found. Did you run `php composer.phar install`?');
}

if (!$config = @include __DIR__ . '/TestConfiguration.php') {
    $config = require __DIR__ . '/TestConfiguration.php.dist';
}

$loader = new StandardAutoloader();
$loader->registerNamespace('SxMail', __DIR__ . '/../src/SxMail');
$loader->registerNamespace('SxMailTest', __DIR__ . '/SxMailTest');
$loader->register();

$serviceManager = new ServiceManager(new ServiceManagerConfig(
    isset($config['service_manager']) ? $config['service_manager'] : array()
));
$serviceManager->setService('ApplicationConfig', $config);

/* @var $moduleManager \Zend\ModuleManager\ModuleManager */
$moduleManager = $serviceManager->get('ModuleManager');
$moduleManager->loadModules();
