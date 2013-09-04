<?php

namespace SxMail;

use Zend\Mail\Message;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
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
     * @var \Zend\Mail\Transport\TransportInterface
     */
    protected $transport;

    /**
     * Construct SxMail.
     *
     * @param   \Zend\View\Renderer\RendererInterface   $viewRenderer
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
                'Invalid value supplied for setLayout.'.
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
     * @param   string  $mimeType
     *
     * @return  string
     */
    protected function manipulateBody($body, $mimeType = null)
    {
        // Make sure we have a string.
        if ($body instanceof ViewModel) {
            $body               = $this->viewRenderer->render($body);
            $detectedMimeType   = 'text/html';
        } elseif (null === $body) {
            $detectedMimeType   = 'text/plain';
            $body               = '';
        }

        if (null !== ($layout = $this->getLayout())) {
            $layout->setVariables(array(
                'content' => $body,
            ));

            $detectedMimeType   = 'text/html';
            $body               = $this->viewRenderer->render($layout);
        }


        if (null === $mimeType && !isset($detectedMimeType)) {
            $mimeType = preg_match("/<[^<]+>/", $body) ? 'text/html' : 'text/plain';
        } elseif (null === $mimeType) {
            $mimeType = $detectedMimeType;
        }

        $mimePart       = new MimePart($body);
        $mimePart->type = $mimeType;

        if (!empty($this->config['charset'])) {
            $mimePart->charset = $this->config['charset'];
        }

        $message        = new MimeMessage();

        if (!isset($this->config['message']['generate_alternative_body'])) {
            $this->config['message']['generate_alternative_body'] = true;
        }

        if ($this->config['message']['generate_alternative_body'] && $mimeType === 'text/html') {
            $generatedBody  = $this->renderTextBody($body);
            $altPart        = new MimePart($generatedBody);
            $altPart->type  = 'text/plain';

            if (!empty($this->config['charset'])) {
                $altPart->charset = $this->config['charset'];
            }

            $message->addPart($altPart);
        }

        $message->addPart($mimePart);

        return $message;
    }

    /**
     * Strip html tags and render a text-only version.
     *
     * @param   string  $body
     *
     * @return  string
     */
    protected function renderTextBody($body)
    {
        $body = html_entity_decode(
            trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/s', '', $body))), ENT_QUOTES
        );

        if (empty($body)) {
          $body = 'To view this email, open it an email client that supports HTML.';
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
     * @param   \Zend\Mail\Message  $message
     */
    public function applyMessageHeaders($message)
    {
        if (empty($this->config['message']['headers']) || !is_array($this->config['message']['headers'])) {
            return $message;
        }

        $headers        = $this->config['message']['headers'];
        $messageHeaders = $message->getHeaders();

        foreach ($headers as $field => $value) {
            $messageHeaders->addHeaderLine((string) $field, (string) $value);
        }
    }

    /**
     * Compose a new message.
     *
     * @param   mixed   $body   Accepts instance of ViewModel, string and null.
     * @param   string  $mimeType
     *
     * @return  \Zend\Mail\Message
     * @throws  \SxMail\Exception\InvalidArgumentException
     */
    public function compose($body = null, $mimeType = null)
    {
        // Supported types are null, ViewModel and string.
        if (null !== $body && !is_string($body) && !($body instanceof ViewModel)) {
            throw new InvalidArgumentException(
                'Invalid value supplied. Expected null, string or instance of Zend\View\Model\ViewModel.'
            );
        }

        $body    = $this->manipulateBody($body, $mimeType);
        $message = new Message;

        $message->setBody($body);

        if ($this->config['message']['generate_alternative_body'] && count($body->getParts()) > 1) {
            $message->getHeaders()->get('content-type')->setType('multipart/alternative');
        }

        $this->applyMessageHeaders($message);
        $this->applyMessageOptions($message);

        return $message;
    }

    /**
     * Set the transport instance.
     *
     * @param \Zend\Mail\Transport\TransportInterface $transport
     */
    public function setTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @return  \Zend\Mail\Transport\TransportInterface
     * @throws  RuntimeException
     */
    public function getTransport()
    {
        if (null !== $this->transport) {
            return $this->transport;
        }

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

        $this->setTransport($transport);

        return $this->transport;
    }

    /**
     * Send out the email.
     *
     * @param   \Zend\Mail\Message  $message
     */
    public function send(Message $message)
    {
        $this->getTransport()->send($message);
    }
}
