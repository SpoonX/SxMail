<?php

return array(
    'sxmail' => array(
        'configs' => array(
            'default' => array(
                'charset' => 'UTF-8',
                'transport' => array(
                    'type'      => 'smtp',
                    'foo'       => 'bar',
                    'options'   => array(
                        'name'              => 'localhost.localdomain',
                        'host'              => '127.0.0.1',
                        'connection_class'  => 'login',
                        'connection_config' => array(
                            'username' => 'user',
                            'password' => 'pass',
                        ),
                    ),
                ),
            ),
            'testWithLayout' => array(
                'message' => array(
                    'layout' => 'mockLayout.phtml',
                ),
                'transport' => array(
                    'type'      => 'sendmail',
                    'options'   => null,
                ),
            ),
            'testWithMessageOptions' => array(
                'message' => array(
                    'layout'  => 'mockLayout.phtml',
                    'options' => array(
                        'subject' => 'bacon urrwhere.',
                        'skipMe!' => 'Okay...',
                    ),
                ),
            ),
            'testSimpleSendMail' => array(
                'transport' => null,
                'message' => array(
                    'options' => array(
                        'to' => 'r.w.overdijk@gmail.com',
                    ),
                ),
            ),
            'testNoAlternativeBody' => array(
                'message' => array(
                    'generate_alternative_body' => false,
                ),
            ),
            'testSetHeaders' => array(
                'transport' => null,
                'message' => array(
                    'headers' => array(
                        'x-cool-header' => 'cool value!',
                    ),
                ),
            ),
            'testSmtp' => array(
                'transport' => array(
                    'options'   => array(
                        'name'              => 'Gmail',
                        'host'              => 'smtp.gmail.com',
                        'connection_class'  => 'plain',
                        'connection_config' => array(
                            'username' => 'ratustestmail@gmail.com',
                            'password' => 'keeshond',
                            'ssl'      => 'tls',
                        ),
                    ),
                ),
                'message' => array(
                    'options' => array(
                        'to'    => 'r.w.overdijk@gmail.com',
                        'from'  => 'ratustestmail@gmail.com',
                    ),
                ),
            ),
            'testSmtpInvalidTransportType' => array(
                'transport' => array(
                    'type' => 'poodle',
                ),
            ),
        ),
    ),
);
