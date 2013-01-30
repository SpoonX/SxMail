<?php

namespace SxMail;

use Zend\Mail\Message;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\RendererInterface;
use SxMail\Exception\InvalidArgumentException;
use SxMail\Exception\RuntimeException;

class SxMail
{
    /**
     * @var array SxMail configuration.
     */
    protected $config;

    /**
     *
     * @var \Zend\View\Renderer\RendererInterface
     */
    protected $viewRenderer;

    /**
     * @var \Zend\View\Model\ViewModel
     */
    protected $layout;

    /**
     * Construct SxMail.
     *
     * @param   \Zend\View\Renderer\RendererInterface    $viewRenderer
     * @param   array                                   $config
     */
        public function __construct(RendererInterface $viewRenderer, $config)
    {
        $this->viewRenderer = $viewRenderer;
        $this->config       = $config;

        $this->setLayout();
    }

    /**
     * Set the layout.
     *
     * @param   mixed   $layout Either null (looks in config), ViewModel, or string.
     *
     * @throws  \SxMail\Exception\InvalidArgumentException
     */
    public function setLayout($layout = null)
    {
        if (null !== $layout && !is_string($layout) && !($layout instanceof ViewModel)) {
            throw new InvalidArgumentException(
                'Invalid value supplied for setLayout.'+
                'Expected null, string, or Zend\View\Model\ViewModel.'
            );
        }

        if (null === $layout && empty($this->config['message']['layout'])) {
            return;
        }

        if (null === $layout) {
            $layout = (string) $this->config['message']['layout'];

            unset($this->config['message']['layout']);
        }

        if (is_string($layout)) {

            $template   = $layout;
            $layout     = new ViewModel;

            $layout->setTemplate($template);
        }

        $this->layout = $layout;
    }

    /**
     * Get the layout.
     *
     * @return  \Zend\View\Model\ViewModel  Returns null when there's no layout.
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Manipulate the body based on configuration options.
     *
     * @param   mixed   $body
     *
     * @return  string
     */
    protected function manipulateBody($body)
    {
        // Make sure we have a string.
        if ($body instanceof ViewModel) {
            $body = $this->viewRenderer->render($body);
        } elseif (null === $body) {
            $body = '';
        }

        if (null !== ($layout = $this->getLayout())) {
            $layout->setVariables(array(
                'content' => $body,
            ));

            $body = $this->viewRenderer->render($layout);
        }

        return $body;
    }

    /**
     * @param   \Zend\Mail\Message  $message
     *
     * @return  \Zend\Mail\Message
     */
    protected function applyMessageOptions(Message $message)
    {
        if (empty($this->config['message']['options']) || !is_array($this->config['message']['options'])) {
            return $message;
        }

        foreach ($this->config['message']['options'] as $key => $value) {
            $method = 'set'.ucfirst($key);

            if (is_callable(array($message, $method))) {
                $message->$method((string) $value);
            }
        }

        return $message;
    }

    /**
     * Compose a new message.
     *
     * @param   mixed   $body   Accepts instance of ViewModel, string and null.
     *
     * @return  \Zend\Mail\Message
     * @throws  \SxMail\Exception\InvalidArgumentException
     */
    public function compose($body = null)
    {
        // Supported types are null, ViewModel and string.
        if (null !== $body && !is_string($body) && !($body instanceof ViewModel)) {
            throw new InvalidArgumentException(
                'Invalid value supplied. Expected null, string or instance of Zend\View\Model\ViewModel.'
            );
        }

        $body    = $this->manipulateBody($body);
        $message = new Message;

        $message->setBody($body);

        $this->applyMessageOptions($message);

        return $message;
    }

    /**
     * Send out the email.
     *
     * @param   \Zend\Mail\Message  $message
     */
    public function send(Message $message)
    {
        if (empty($this->config['transport'])) {
            $this->config['transport'] = array(
                'type' => 'sendmail',
            );
        }

        $transportType  = ucfirst($this->config['transport']['type']);
        $transportClass = 'Zend\Mail\Transport\\' . $transportType;

        if (!class_exists($transportClass)) {
            throw new RuntimeException(
                "Transport type '$transportType' not found."
            );
        }

        $transport = new $transportClass;

        if (!empty($this->config['transport']['options'])) {
            $transportOptionsClass = $transportClass . 'Options';

            if (class_exists($transportOptionsClass)) {
                $transport->setOptions(new $transportOptionsClass($this->config['transport']['options']));
            }
        }

        $transport->send($message);
    }
}
