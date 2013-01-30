<?php

namespace SxMailTest;

use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config as ServiceManagerConfig;
use Zend\View\Model\ViewModel;

class SxMailTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test if composing in the most default way works.
     */
    public function testComposeLayoutAndViewModel()
    {
        $viewRenderer = $this->getMock('Zend\View\Renderer\RendererInterface', array('render', 'getEngine', 'setResolver'));
        $viewRenderer
                ->expects($this->exactly(3))
                ->method('render')
                ->will($this->returnValue('aapje'));

        $viewManager = $this->getMock('Zend\Mvc\View\Console\ViewManager', array('getRenderer'));

        $viewManager
                ->expects($this->once())
                ->method('getRenderer')
                ->will($this->returnValue($viewRenderer));

        $serviceManager = new ServiceManager(
            new ServiceManagerConfig(include __DIR__ . '/_files/services.config.php')
        );

        $serviceManager->setService('view_manager', $viewManager);
        $serviceManager->setService('Config', include __DIR__ . '/_files/module.config.php');

        $viewModel  = new ViewModel;
        $viewModel->setTemplate('mock.phtml');

        $mail       = $serviceManager->get('SxMail\Service\SxMail');
        $sxMail     = $mail->prepare('testWithLayout');
        $data       = $sxMail->compose($viewModel);
        $dataNull   = $sxMail->compose(null);
        $this->assertInstanceOf('Zend\Mail\Message', $data);
        $this->assertEquals('aapje', $data->getBody()->getPartContent(0)); // 1x aapje because layout doesn't load view in this test.
        $this->assertEquals('aapje', $dataNull->getBody()->getPartContent(0)); // Aapje because this time there's no layout. (was unset in prev test)
    }

    /**
     * Test if setting headers works.
     */
    public function testSetHeaders()
    {
        $viewRenderer = $this->getMock('Zend\View\Renderer\RendererInterface', array('render', 'getEngine', 'setResolver'));
        $viewRenderer
                ->expects($this->exactly(0))
                ->method('render')
                ->will($this->returnValue('aapje'));

        $viewManager = $this->getMock('Zend\Mvc\View\Console\ViewManager', array('getRenderer'));

        $viewManager
                ->expects($this->once())
                ->method('getRenderer')
                ->will($this->returnValue($viewRenderer));

        $serviceManager = new ServiceManager(
            new ServiceManagerConfig(include __DIR__ . '/_files/services.config.php')
        );

        $serviceManager->setService('view_manager', $viewManager);
        $serviceManager->setService('Config', include __DIR__ . '/_files/module.config.php');

        $mail       = $serviceManager->get('SxMail\Service\SxMail');
        $sxMail     = $mail->prepare('testSetHeaders');
        $data       = $sxMail->compose('Random');
        $headers    = $data->getHeaders();

        $this->assertEquals('X-Cool-Header: cool value!', $headers->get('x-cool-header')->toString());
    }

    /**
     * Test if detecting mime works.
     */
    public function testDetectMime()
    {
        $viewRenderer = $this->getMock('Zend\View\Renderer\RendererInterface', array('render', 'getEngine', 'setResolver'));
        $viewRenderer
                ->expects($this->exactly(1))
                ->method('render')
                ->will($this->returnValue('aapje'));

        $viewManager = $this->getMock('Zend\Mvc\View\Console\ViewManager', array('getRenderer'));

        $viewManager
                ->expects($this->once())
                ->method('getRenderer')
                ->will($this->returnValue($viewRenderer));

        $serviceManager = new ServiceManager(
            new ServiceManagerConfig(include __DIR__ . '/_files/services.config.php')
        );

        $serviceManager->setService('view_manager', $viewManager);
        $serviceManager->setService('Config', include __DIR__ . '/_files/module.config.php');

        $viewModel  = new ViewModel;
        $viewModel->setTemplate('mock.phtml');

        $mail           = $serviceManager->get('SxMail\Service\SxMail');
        $sxMail         = $mail->prepare();
        $string         = $sxMail->compose('Random');
        $htmlForced     = $sxMail->compose('Random', 'text/html');
        $html           = $sxMail->compose('<strong>Random</strong>');
        $htmlModel      = $sxMail->compose($viewModel);
        $mimeString     = $string->getBody()->getPartHeadersArray(0);
        $mimeHtmlForced = $htmlForced->getBody()->getPartHeadersArray(0);
        $mimeHtml       = $html->getBody()->getPartHeadersArray(0);
        $mimeModel      = $htmlModel->getBody()->getPartHeadersArray(0);

        $this->assertEquals('text/plain', $mimeString[0][1]);
        $this->assertEquals('text/html', $mimeHtmlForced[0][1]);
        $this->assertEquals('text/html', $mimeHtml[0][1]);
        $this->assertEquals('text/html', $mimeModel[0][1]);

    }

    /**
     * Test if setLayout fails with invalid values.
     * @expectedException SxMail\Exception\InvalidArgumentException
     */
    public function testSetLayoutFail()
    {
        // Expected zero times because render isn't called until the end.
        $viewRenderer =  $this->getMock('Zend\View\Renderer\RendererInterface', array('render', 'getEngine', 'setResolver'));
        $viewRenderer
                ->expects($this->exactly(0))
                ->method('render')
                ->will($this->returnValue('aapje'));

        $viewManager = $this->getMock('Zend\Mvc\View\Console\ViewManager', array('getRenderer'));

        $viewManager
                ->expects($this->once())
                ->method('getRenderer')
                ->will($this->returnValue($viewRenderer));

        $serviceManager = new ServiceManager(
            new ServiceManagerConfig(include __DIR__ . '/_files/services.config.php')
        );

        $serviceManager->setService('view_manager', $viewManager);
        $serviceManager->setService('Config', include __DIR__ . '/_files/module.config.php');

        $mail       = $serviceManager->get('SxMail\Service\SxMail');
        $sxMail     = $mail->prepare('testWithLayout');

        $sxMail->setLayout(9);

    }

    /**
     * Test if exception gets thrown when supplying invalid body
     *
     * @expectedException SxMail\Exception\InvalidArgumentException
     */
    public function testComposeInvalidBody()
    {
        $viewRenderer   = $this->getMock('Zend\View\Renderer\RendererInterface', array('render', 'getEngine', 'setResolver'));
        $viewManager    = $this->getMock('Zend\Mvc\View\Console\ViewManager', array('getRenderer'));

        $viewManager
                ->expects($this->once())
                ->method('getRenderer')
                ->will($this->returnValue($viewRenderer));

        $serviceManager = new ServiceManager(
            new ServiceManagerConfig(include __DIR__ . '/_files/services.config.php')
        );

        $serviceManager->setService('view_manager', $viewManager);
        $serviceManager->setService('Config', include __DIR__ . '/_files/module.config.php');

        $mail       = $serviceManager->get('SxMail\Service\SxMail');
        $sxMail     = $mail->prepare();

        $sxMail->compose(123);
    }

    /**
     * Test if options get applied to the message, and if non-existing options get skipped.
     */
    public function testComposeApplyOptions()
    {
        $viewRenderer =  $this->getMock('Zend\View\Renderer\RendererInterface', array('render', 'getEngine', 'setResolver'));
        $viewRenderer
                ->expects($this->exactly(2))
                ->method('render')
                ->will($this->returnValue('aapje'));

        $viewManager = $this->getMock('Zend\Mvc\View\Console\ViewManager', array('getRenderer'));

        $viewManager
                ->expects($this->once())
                ->method('getRenderer')
                ->will($this->returnValue($viewRenderer));

        $serviceManager = new ServiceManager(
            new ServiceManagerConfig(include __DIR__ . '/_files/services.config.php')
        );

        $serviceManager->setService('view_manager', $viewManager);
        $serviceManager->setService('Config', include __DIR__ . '/_files/module.config.php');

        $viewModel  = new ViewModel;
        $viewModel->setTemplate('mock.phtml');

        $mail       = $serviceManager->get('SxMail\Service\SxMail');
        $sxMail     = $mail->prepare('testWithMessageOptions');
        $data       = $sxMail->compose($viewModel);

        $this->assertEquals('bacon urrwhere.', $data->getSubject());
    }

    /**
     * Test a simple email.
     */
    public function testSend()
    {
        if (getenv('TRAVIS')) {
            $this->setExpectedException(
                'Zend\Mail\Exception\RuntimeException',
                'Unable to send mail: Unknown error'
            );
        }

        $viewRenderer =  $this->getMock('Zend\View\Renderer\RendererInterface', array('render', 'getEngine', 'setResolver'));

        $viewManager = $this->getMock('Zend\Mvc\View\Console\ViewManager', array('getRenderer'));

        $viewManager
                ->expects($this->once())
                ->method('getRenderer')
                ->will($this->returnValue($viewRenderer));

        $serviceManager = new ServiceManager(
            new ServiceManagerConfig(include __DIR__ . '/_files/services.config.php')
        );

        $serviceManager->setService('view_manager', $viewManager);
        $serviceManager->setService('Config', include __DIR__ . '/_files/module.config.php');

        $body       = 'Ohi! My name is SxMail. I know this might seem spammy, but look at it from the bright side! This means that your unit test ran successfully! Ain\'t this a lovely world :3';
        $mail       = $serviceManager->get('SxMail\Service\SxMail');
        $sxMail     = $mail->prepare('testSimpleSendMail');
        $data       = $sxMail->compose($body);

        $this->assertEquals($body, $data->getBody()->getPartContent(0));

        $sxMail->send($data);
    }

    /**
     * Test email with smtp plain, ssl tls.
     */
    public function testSendSmtp()
    {
        if (getenv('TRAVIS')) {
            $this->setExpectedException(
                'Zend\Mail\Protocol\Exception\RuntimeException',
                'Network is unreachable'
            );
        }

        $viewRenderer   =  $this->getMock('Zend\View\Renderer\RendererInterface', array('render', 'getEngine', 'setResolver'));
        $viewManager    = $this->getMock('Zend\Mvc\View\Console\ViewManager', array('getRenderer'));

        $viewManager
                ->expects($this->once())
                ->method('getRenderer')
                ->will($this->returnValue($viewRenderer));

        $serviceManager = new ServiceManager(
            new ServiceManagerConfig(include __DIR__ . '/_files/services.config.php')
        );

        $serviceManager->setService('view_manager', $viewManager);
        $serviceManager->setService('Config', include __DIR__ . '/_files/module.config.php');

        $body       = 'Ohi! My name is SxMail. I know you like my body, it has to be different. Otherwise you won\'t see the difference broah. Also, sorry for spamz. But this means everything works! hooraaay.';
        $mail       = $serviceManager->get('SxMail\Service\SxMail');
        $sxMail     = $mail->prepare('testSmtp');
        $data       = $sxMail->compose($body);

        $this->assertEquals($body, $data->getBody()->getPartContent(0));

        $sxMail->send($data);
    }

    /**
     * Test if we throw an exception on invalid transport method.
     * @expectedException SxMail\Exception\RuntimeException
     */
    public function testSendSmtpFailInvalidTransportType()
    {
        $viewRenderer =  $this->getMock('Zend\View\Renderer\RendererInterface', array('render', 'getEngine', 'setResolver'));

        $viewManager = $this->getMock('Zend\Mvc\View\Console\ViewManager', array('getRenderer'));

        $viewManager
                ->expects($this->once())
                ->method('getRenderer')
                ->will($this->returnValue($viewRenderer));

        $serviceManager = new ServiceManager(
            new ServiceManagerConfig(include __DIR__ . '/_files/services.config.php')
        );

        $serviceManager->setService('view_manager', $viewManager);
        $serviceManager->setService('Config', include __DIR__ . '/_files/module.config.php');

        $body       = 'Not relevant.';
        $mail       = $serviceManager->get('SxMail\Service\SxMail');
        $sxMail     = $mail->prepare('testSmtpInvalidTransportType');
        $data       = $sxMail->compose($body);

        $this->assertEquals($body, $data->getBody()->getPartContent(0));

        $sxMail->send($data);
    }
}
