<?php

//$config['Guard.AuthModule.Name'] = 'Ldap';    // Using LDAP module
//$config['Guard.AuthModule.Name'] = 'Shibboleth';    // Using Shibboleth module
$config['Guard.AuthModule.Name'] = 'Default';     // Using default (build-in) module

$config['Guard.AuthModule.Shibboleth'] = array(
    'sessionInitiatorURL' => 'https://%HOST%/Shibboleth.sso/Login',
    'logoutURL'           => 'https://%HOST%/Shibboleth.sso/Logout',
    'fieldMapping'        => array(
        'eppn'        => 'username',
        'affiliation' => 'role',
    ),
    'mappingRules'        => array(
        'eppn'        => array('/@ubc.ca/' => ''),
        'affiliation' => array('/staff@ubc.ca/' => 'admin'),
    ),
    'loginError'          => 'You have successfully logged through Shibboleth. But you do not have access this appliction.',
    'loginImageButton'    => '',
    'loginTextButton'     => 'Login',
);

$config['Guard.AuthModule.Ldap'] = array(
    'host' => 'ldaps://ldapcons.stg.id.ubc.ca/',
    'port' => 636,
    'serviceUsername' => 'uid=ipeer, ou=Special Users, o=ubc.ca', // username to connect to LDAP
    'servicePassword' => '', // password to connect to LDAP
    'baseDn' => 'ou=Campus Login, o=ubc.ca',
    'usernameField' => 'cwlLoginName',
    'passwordField' => 'password',
);
