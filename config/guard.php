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
    'loginError'          => 'You have successfully logged in through Shibboleth. But you do not have access this appliction.',
    'loginImageButton'    => '',
    'loginTextButton'     => 'Login',
);

$config['Guard.AuthModule.Ldap'] = array(
    'host' => 'ldaps://ldap.school.ca/',
    'port' => 636,
    'serviceUsername' => 'uid=USERNAME, ou=Special Users, o=school.ca', // username to connect to LDAP
    'servicePassword' => 'PASSWORD', // password to connect to LDAP
    'baseDn' => 'ou=Campus Login, o=school.ca',
    'usernameField' => 'uid',
    'attributeSearchFilters' => array(
//        'uid',
    ),
    'attributeMap' => array(
//        'username' => 'uid',
    ),
    'fallbackInternal' => true,
);

$config['Guard.AuthModule.Cwl'] = array(
    'sessionInitiatorURL' => 'https://www.auth.cwl.ubc.ca/auth/login',
    'serviceName'         => 'ServiceName',
    'fieldMapping'        => array(
        'eppn'        => 'username',
        'affiliation' => 'role',
    ),
    'mappingRules'        => array(
        'eppn'        => array('/@ubc.ca/' => ''),
        'affiliation' => array('/staff@ubc.ca/' => 'admin'),
    ),
    'loginError'          => 'You have successfully logged in. But you do not have access this appliction.',
    'loginImageButton'    => '',
    'loginTextButton'     => 'Login',
    // CWL XML-RPC interface URLs: https://www.auth.verf.cwl.ubc.ca/auth/rpc (for verification)
    //                             https://www.auth.cwl.ubc.ca/auth/rpc
    'RPCURL'              => "https://www.auth.cwl.ubc.ca",
    'RPCPath'             => "/auth/rpc",

    /**
     * the name of the function being called through XML-RPC. this is
     * prepended with 'session.' later
     */
    //$CWLFunctionName    => 'getLoginName',
    'functionName'        => 'getIdentities',

    /**
     * the application's ID/name and password as given by the CWL team
     */
    'applicationID'       => '',
    'applicationPassword' => '',
);
