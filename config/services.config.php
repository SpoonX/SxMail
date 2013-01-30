<?php

namespace SxMail;

return array(
    'factories' => array(
        'SxMail\Service\SxMail' => function($sm) {
            $config         = $sm->get('config');
            $sxmailConfig   = !empty($config['sxmail']) ? $config['sxmail'] : array();

            return new Service\SxMailService($sm->get('view_manager')->getRenderer(), $sxmailConfig);
        },
    ),
);
