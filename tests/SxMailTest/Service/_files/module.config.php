<?php

return array(
    'sxmail' => array(
        'configs' => array(
            'default' => array(
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
            'otherConfig' => array(
                'transport' => array(
                    'type'      => 'sendmail',
                    'options'   => null,
                ),
            ),
            'mergedConfig' => array(
                'transport' => array(
                    'type'      => 'sendmail',
                    'options'   => null,
                    'foo'       => 'bar',
                ),
            ),
        ),
    ),
);
