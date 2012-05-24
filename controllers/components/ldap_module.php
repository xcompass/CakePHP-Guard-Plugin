<?php

/**
 * LdapModule The LDAP authentication module. It authenticate against a LDAP
 * server.
 *
 * @uses      AuthModule
 * @package   Plugins.Guard
 * @author    Compass <pan.luo@ubc.ca>
 * @copyright 2012 CTLT
 * @license   PHP Version 3.0 {@link http://www.php.net/license/3_0.txt}
 */
class LdapModule extends AuthModule
{
    /**
     * name the name of the authentication module
     *
     * @var string
     * @access public
     */
    public $name = 'Ldap';

    /**
     * hasLoginForm this module uses internal login page
     *
     * @var mixed
     * @access public
     */
    public $hasLoginForm = true;

    /**
     * authenticate authenticate the user and generate the user session
     *
     * @param mixed $username
     *
     * @access public
     * @return void
     */
    function authenticate($username = null)
    {
        $loggedIn = false;

        $ds = ldap_connect($this->host, $this->port);

        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        if (!(@ldap_bind($ds, $this->serviceUsername, $this->servicePassword))) {
            $this->guard->error(sprintf('Could not connect to LDAP server: %s with port %d.', $this->host, $this->port));
            return false;
        }

        if (!($result = ldap_search($ds, $this->baseDn, $this->usernameField.'='.$this->data[$this->guard->fields['username']]))) {
            $this->guard->error(sprintf('Unable to perform LDAP seach with base DN %s and search %s.',
                $this->baseDn, $this->usernameField.'='.$this->data[$this->guard->fields['username']]));
            return false;
        }

        $info = ldap_get_entries($ds, $result);

        if (0 != $info['count']) {
            if (@ldap_bind($ds, $info[0]['dn'], $this->data[$this->guard->fields['password']])) {
                // we need to get attributes
                if (!empty($this->attributeMap)) {
                    // construct filter
                    $filters = array();
                    $entry = ldap_first_entry($ds, $result);
                    foreach ($this->attributeSearchFilters as $filter) {
                        $values = ldap_get_values($ds, $entry, $filter);
                        $filters[] = $filter.'='.$values[0];
                    }

                    // do the search
                    if (!($result = ldap_search($ds, $info[0]['dn'], implode(',', $filters), array_values($this->attributeMap)))) {
                        $this->guard->error(sprintf('Unable to perform LDAP seach with base DN %s and filter %s.',
                            $info[0]['dn'], implode(',', $filters)));
                        return false;
                    }
                    $entry = ldap_first_entry($ds, $result);
                    foreach ($this->attributeMap as $key => $attribute) {
                        $values = ldap_get_values($ds, $entry, $attribute);
                        $this->data[$key] = $values[0];
                    }
                }

                // ldap success, identify the user from local table
                if ($user = $this->identify($this->data[$this->guard->fields['username']])) {
                    $this->Session->write($this->guard->sessionKey, $user);
                    $loggedIn = true;
                }
            }
        }

        ldap_close($ds);
        return $loggedIn;
    }

    /**
     * identify find the user from database
     *
     * @param bool $username   username
     * @param bool $conditions search condition
     *
     * @access public
     * @return array user array
     */
    function identify($username = null, $conditions = null)
    {
        // get the model AuthComponent is configured to use
        $model =& $this->guard->getModel(); // default is User
        // do a query that will find a User record when given successful login data
        $user = $model->find('first', array('conditions' => array(
            $model->escapeField($this->guard->fields['username']) => $username)
        ));

        // return null if user invalid
        if (!$user) {
            return null; // this is what AuthComponent::identify would return on failure
        }

        // call original AuthComponent::identify with string for $user and false for $conditions
        return $this->guard->identify($user[$this->guard->userModel][$model->primaryKey], null);
    }

}
