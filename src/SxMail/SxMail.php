<?php

namespace SxMail;

use Zend\Mail\Message;
use Zend\View\Model\ViewModel;
use Zend\View\View;
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
     * @var Zend\View\View
     */
    protected $view;

    /**
     * Construct SxMail.
     *
     * @param   array   $config
     */
    public function __construct(View $view, $config)
    {
        $this->view   = $view;
        $this->config = $config;
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
            $body = $this->view->render($body);
        } elseif (null === $body) {
            $body = '';
        }

        if (!empty($this->config['message']['layout']) && is_string($this->config['message']['layout'])) {
            $layout = new ViewModel;

            $layout->setTemplate($this->config['message']['layout'])->setVariables(array(
                'content' => $body,
            ));

            unset($this->config['message']['layout']);

            $body = $this->view->render($layout);
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
     * @throws  InvalidArgumentException
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
