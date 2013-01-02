<?php
require_once('XML/RPC.php');

/**
 * UBC Campus Wide Login authentication module.
 *
 * @uses AuthModule
 * @package Plugins.Guard
 * @version $id$
 * @copyright Copyright (C) 2010 CTLT
 * @author Compass
 * @license PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
class CwlModule extends AuthModule {

    /**
     * name the name of the authentication module
     *
     * @var string
     * @access public
     */
    var $name = 'Cwl';

    /**
     * hasLoginForm this module uses external login page
     *
     * @var mixed
     * @access public
     */
    var $hasLoginForm = false;

    /**
     * sessionHeaders the headers to check against to see if there is an active
     * shibboleth session
     *
     * @var string
     * @access public
     */
    var $sessionHeaders = array('Shib-Session-ID', 'HTTP_SHIB_IDENTITY_PROVIDER');

    /**
     * hasLoginData test if it got login data from SP (if there is a active
     * session)
     *
     * @access public
     * @return boolean true, if it got login data. false, if not
     */
    function hasLoginData() {
        return isset($_GET['ticket']);
    }

    /**
     * Check if a Shibboleth session is active.
     *
     * @access public
     * @return boolean if session is active
     */
    function isSessionActive() {
        $active = false;

        foreach ($this->sessionHeaders as $header) {
            if ( array_key_exists($header, $_SERVER) && !empty($_SERVER[$header]) ) {
                $active = true;
                break;
            }
        }
        return $active;
    }

    /**
     * Generate the URL to initiate CWL login.
     *
     * @param string $redirect the final URL to redirect the user to after all login is complete
     * @return the URL to direct the user to in order to initiate Shibboleth login
     */
    function sessionInitiatorUrl($redirect = null) {
        $initiator_url = self::urlNormalize($this->sessionInitiatorURL) .
            '?serviceName=' . $this->applicationID.
            '&serviceURL=' . Router::url(array('plugin' => 'guard', 'controller' => 'guard', 'action' => 'login'), true);

        return $initiator_url;
    }

    /**
     * authenticate authenticate the user and generate the user session
     *
     * @param mixed $username
     * @access public
     * @return void
     */
    function authenticate($username = null) {
        $loggedIn = false;

        $this->_mapFields();

        $ticket = $_GET['ticket'];

        // now get some info about the session

        // the parameters passed to the RPC interface.  the ticket is the
        // first argument for all functions
        $params = array(new XML_RPC_Value($ticket, 'string'));

        // note that the function name is prepended with the string 'session.'
        $msg = new XML_RPC_Message("session.".$this->functionName, $params);

        $cli = new XML_RPC_Client($this->RPCPath, $this->RPCURL);
        $cli->setCredentials($this->applicationID, $this->applicationPassword);
        //print_r ($cli);
        //$cli->setDebug(1);

        $resp = $cli->send($msg);
        if (!$resp) {
            CakeLog::write('error', 'Communication error: ' . $cli->errstr);
            return false;
        }

        // print the raw response data

        //echo "<b>Raw Response:</b><br /><pre>";
        //print_r($resp);
        //echo "</pre>";

        if ($resp->faultCode()) {
            // error
            CakeLog::write('error', 'Fault Code: ' . $resp->faultCode() . "," . 'Fault Reason: ' . $resp->faultString());
            return false;
        }

        // an encoded response value
        $val = $resp->value();

        // the actual data we requested
        $data = XML_RPC_decode($val);

        //echo "<b>Response Data:</b><br /><pre>";
        //print_r($data);
        //echo "</pre>";
        if (empty($data['student_number']) && empty($data['guest_id'])) {
            CakeLog::write('error', 'No student number or guest id found.');
            return false;
        }

        $studentNumber = empty($data['student_number']) ? $data['guest_id'] : $data['student_number'];

        if ($user = $this->identify($studentNumber)) {
            $this->Session->write($this->guard->sessionKey, $user);
            $loggedIn = true;
        }

        return $loggedIn;
    }

    /**
     * getLoginUrl return the shibboleth login URL
     *
     * @access public
     * @return string the shibboleth login URL
     */
    function getLoginUrl() {
        return $this->sessionInitiatorUrl();
    }

    /**
     * logout logout shibboleth session. User will be redirected to shibboleth
     * logout URL after the internal logout. Then redirected to the final logout
     * page.
     *
     * @access public
     * @return void
     */
    /*function logout() {
        if ( $this->isSessionActive() ) {
            $this->guard->logoutRedirect = self::urlNormalize($this->logoutURL) .
                '?return=' . Router::url($this->guard->logoutRedirect, true);
        }
    }*/
}
