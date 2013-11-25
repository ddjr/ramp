<?php

/**
 * RAMP: Records and Activity Management Program
 *
 * LICENSE
 *
 * This source file is subject to the BSD-2-Clause license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.cs.kzoo.edu/ramp/LICENSE.txt
 *
 * @category   Ramp
 * @package    Ramp
 * @copyright  Copyright (c) 2013 Alyce Brady (http://www.cs.kzoo.edu/~abrady)
 * @license    http://www.cs.kzoo.edu/ramp/LICENSE.txt   Simplified BSD License
 *
 */

class Ramp_RegistryFacade
{
    const CONFIG_SETTINGS   = "rampConfigSettings";

    const AUTH_TYPE         = "rampAuthenticationType";
    const INTERNAL_AUTH     = "internal";

    const DEFAULT_PW        = "defaultPassword";

    const SESSION_TIMEOUT   = "sessionTimeout";

    const ACL_ROLES         = "aclNonGuestRole";
    const ACL_RESOURCES     = "aclResources";
    const ACL_RULES         = "aclRules";

    const BASE_MENU_DIR     = "menuDirectory";
    const MENU_LIST         = "roleBasedMenus";
    const DEFAULT_MENU      = "menuFilename";

    const ACTIVITIES_ROOT   = "activitiesDirectory";
    const SETTINGS_ROOT     = "settingsDirectory";
    const SETTINGS_SUFFIX   = "settingsSuffix";
    const DOCUMENT_ROOT     = "documentRoot";
    const INIT_ACT_LIST     = "roleBasedInitActivities";
    const DEF_INIT_ACT      = "initialActivity";

    const TITLE             = "title";
    const SUBTITLE          = "subtitle";
    const SHORT_NAME        = "applicationShortName";
    const ICON              = "icon";
    const CSS               = "css";

    protected static $_singleton = null;

    protected $_configs;   // configuration properties read in

    protected $_activitiesDirectory;
    protected $_settingsDirectory;


    // STATIC FUNCTIONS

    /**
     * Gets the singleton instance of the RegistryFacade class.
     */
    public static function getInstance()
    {
        self::$_singleton = self::$_singleton ? :
                            new Ramp_RegistryFacade();
        return self::$_singleton;
    }


    // CONSTRUCTOR AND INSTANCE FUNCTIONS

    /**
     * Class constructor
     *
     * Creates a singleton object representing the RAMP configuration 
     * properties in the Zend_Registry.
     */
    protected function __construct()
    {
        $this->_configs =
            Zend_Registry::isRegistered(self::CONFIG_SETTINGS) ?
                Zend_Registry::get(self::CONFIG_SETTINGS) :
                array();
        /*
        $this->_activitiesDirectory =
            Zend_Registry::isRegistered(self::ACTIVITIES_DIRECTORY) ?
                Zend_Registry::get(self::ACTIVITIES_DIRECTORY) :
                null;
        $this->_settingsDirectory =
            Zend_Registry::isRegistered(self::SETTINGS_DIRECTORY) ?
                Zend_Registry::get(self::SETTINGS_DIRECTORY) :
                null;
         */
    }

    /**
     * Gets the authentication type.  (If no authentication type has 
     * been set in the application.ini file, the default is to use 
     * internal authentication.)
     */
    public function getAuthenticationType()
    {
        return $this->_configs[self::AUTH_TYPE] ? : self::INTERNAL_AUTH;
    }

    /**
     * Gets the default password (used when a user has not yet set their 
     * password).
     */
    public function getDefaultPassword()
    {
        return $this->_configs[self::DEFAULT_PW] ? : null;
    }

    /**
     * Checks whether the Ramp application is using internal authentication.
     */
    public function usingInternalAuthentication()
    {
        return $this->getAuthenticationType() == self::INTERNAL_AUTH;
    }

    /**
     * Gets the session timeout value.
     */
    public function getSessionTimeout()
    {
        return $this->_configs[self::SESSION_TIMEOUT] ? : 0;
    }

    /**
     * Gets the authorization roles used in Access Control Lists (ACLs).
     */
    public function getAclRoles()
    {
        return $this->_configs[self::ACL_ROLES] ? : null;
    }

    /**
     * Gets the resources whose use is authorized with Access Control
     * Lists (ACLs).
     */
    public function getAclResources()
    {
        return $this->_configs[self::ACL_RESOURCES] ? : null;
    }

    /**
     * Gets the rules that implement the RAMP Access Control Lists (ACLs).
     */
    public function getAclRules()
    {
        return $this->_configs[self::ACL_RULES] ? : array();
    }

    /**
     * Gets an array containing settings that affect the look and feel 
     * of the application.  The array keys are:
     *      'title'
     *      'subtitle'
     *      'shortName'
     *      'icon'
     *      'rampStyleSheet'
     */
    public function getLookAndFeel()
    {
        $lookAndFeel = array();
        $lookAndFeel['title'] = $this->_configs[self::TITLE] ? : null;
        $lookAndFeel['subtitle'] = $this->_configs[self::SUBTITLE] ? : null;
        $lookAndFeel['shortName'] = $this->_configs[self::SHORT_NAME] ? : null;
        $lookAndFeel['icon'] = $this->_configs[self::ICON] ? : null;
        $lookAndFeel['rampStyleSheet'] = $this->_configs[self::CSS] ? : null;
        return $lookAndFeel;
    }

    /**
     * Gets the base directory for menus.
     * If there isn't one defined, uses the settings directory.
     */
    public function getMenuDirectory()
    {
        $path = $this->_configs[self::BASE_MENU_DIR];
        return empty($path) ? self::getSettingsDirectory() : $path;
    }

    /**
     * Gets the default menu.
     */
    public function getDefaultMenu()
    {
        if ( ! empty($this->_configs[self::DEFAULT_MENU]) )
        {
            $defaultMenu = $this->_configs[self::DEFAULT_MENU];
            return $this->_buildFilename($defaultMenu,
                                         $this->getMenuDirectory());
        }
        return null;
    }

    /**
     * Gets the appropriate menu for the given role (or the default menu 
     * if no role-specific menu has been defined for the given role).
     *
     * @param $role  the user's role
     */
    public function getMenu($role)
    {
        if ( ! empty($this->_configs[self::MENU_LIST]) &&
             ! empty($this->_configs[self::MENU_LIST][$role]) )
        {
            $menu = $this->_configs[self::MENU_LIST][$role];
            $menu = $this->_buildFilename($menu, $this->getMenuDirectory());
            if ( $menu != null )
            {
                return $menu;
            }
        }
        return $this->getDefaultMenu();
    }

    /**
     * Gets the base directory being used for activity specification
     * files.  If there isn't one defined, uses the settings directory.
     *
     * @return string   directory path
     */
    public function getActivitiesDirectory()
    {
        $path = $this->_configs[self::ACTIVITIES_ROOT];
        return empty($path) ? self::getSettingsDirectory() : $path;
    }

    /**
     * Gets the base directory being used for table sequence/setting files.
     *
     * @return string   directory path
     */
    public function getSettingsDirectory()
    {
        // Get the settings directory from Zend_Registry.
        $path = $this->_configs[self::SETTINGS_ROOT];

        // If no directory specified, come up with a default instead.
        if ( empty($path) )
        {
            $baseDir = self::_getBaseDirectory();
            $path = $baseDir . DIRECTORY_SEPARATOR . 'settings';
        }

        return $path;
    }

    /**
     * Gets the suffix being used for table sequence/setting files.
     */
    public function getSettingsSuffix()
    {
        return $this->_configs[self::SETTINGS_SUFFIX] ? : null;
    }

    /**
     * Gets the base directory being used for document files.
     * If there isn't one defined, uses the settings directory.
     *
     * @return string   directory path
     */
    public function getDocumentRoot()
    {
        $path = $this->_configs[self::DOCUMENT_ROOT];
        return empty($path) ? self::getSettingsDirectory() : $path;
    }

    /**
     * Gets the default initial activity.
     */
    public function getDefaultInitialActivity()
    {
        return $this->_configs[self::DEF_INIT_ACT] ? : null;
    }

    /**
     * Returns the given activity name if that file exists, an extended 
     * version of the activity name (built up from the base activity
     * directory and the given file name) if that file exists, or null.
     *
     * @param $filename      the file name (absolute or relative)
     * @param $directory     the directory in which to look if the 
     *                          filename is relative
     */
    protected function _buildFilename($filename, $directory)
    {
        if ( file_exists($filename) )
        {
            return $filename;
        }
        if ( $directory != null )
        {
            $extendedFilename = $directory .  DIRECTORY_SEPARATOR .  $filename;
            if ( file_exists($extendedFilename) )
            {
                return $extendedFilename;
            }
        }
        return null;
    }

    /**
     * Gets the path where settings should be stored.  Uses code from 
     * Zend_Controller_Action::initView() -- if only the Zend 
     * programmers had broken this out into a protected function!
     *
     * @return the base directory for this application module
     *
     */
    protected static function _getBaseDirectory()
    {
        $front = Zend_Controller_Front::getInstance();
        $module  = $front->getRequest()->getModuleName();
        $dirs    = $front->getControllerDirectory();
        if (empty($module) || !isset($dirs[$module])) {
            $module = $front->getDispatcher()->getDefaultModule();
        }
        $baseDir = dirname($dirs[$module]);

        return $baseDir;
    }

}

