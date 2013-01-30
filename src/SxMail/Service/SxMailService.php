<?php

namespace SxMail\Service;

use SxMail\SxMail;
use Zend\Config\Config;
use Zend\View\Renderer\RendererInterface;

class SxMailService
{

    /**
     * @var \Zend\Config\Config
     */
    protected $config = array();

    /**
     *
     * @var \Zend\View\Renderer\RendererInterface
     */
    protected $viewRenderer;

    /**
     * Construct the service.
     *
     * @param   \Zend\View\Renderer\RendererInterface   $viewRenderer
     * @param   array                                   $config
     */
    public function __construct(RendererInterface $viewRenderer, array $config)
    {
        if (empty($config['configs']['default'])) {
            $config['configs']['default'] = array();
        }
        $this->viewRenderer = $viewRenderer;
        $this->config       = new Config($config, true);
    }

    /**
     * Get the default config, merged with an option overriding config.
     *
     * @param   string  $configKey
     *
     * @return  array   The merged configuration
     */
    public function getConfig($configKey = null)
    {
        $default = clone $this->config->configs->default;

        if (null !== $configKey) {
            $default->merge(clone $this->config->configs->{$configKey});
        }

        return $default->toArray();
    }

    /**
     * Prepare a new email instance.
     *
     * @param   string  $configKey
     *
     * @return  \SxMail\SxMail
     */
    public function prepare($configKey = null)
    {
        return new SxMail($this->viewRenderer, $this->getConfig($configKey));
    }
}
