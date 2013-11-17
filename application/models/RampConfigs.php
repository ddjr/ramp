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
 * @package    Ramp_Model
 * @copyright  Copyright (c) 2013 Alyce Brady (http://www.cs.kzoo.edu/~abrady)
 * @license    http://www.cs.kzoo.edu/ramp/LICENSE.txt   Simplified BSD License
 *
 */

class Application_Model_RampConfigs
{
    const CONFIG_SETTINGS = "rampConfigSettings";

    const AUTH_TYPE       = "rampAuthenticationType";
    const INTERNAL_AUTH   = "internal";

    const SESSION_TIMEOUT = "sessionTimeout";

    const BASE_MENU_DIR   = "menuDirectory";
    const MENU_LIST       = "roleBasedMenus";
    const DEFAULT_MENU    = "menuFilename";

    const ACTIVITIES_DIRECTORY = "rampActivitiesDirectory";
    const SETTINGS_DIRECTORY   = "rampSettingsDirectory";

    protected static $_singleton = null;

    protected $_configs;   // configuration properties read in

    protected $_activitiesDirectory;
    protected $_settingsDirectory;


    // STATIC FUNCTIONS

    /**
     * Gets the singleton instance of the RampConfigs class.
     */
    public static function getInstance()
    {
        self::$_singleton = self::$_singleton ? :
                            new Application_Model_RampConfigs();
        return self::$_singleton;
    }

    /**
     * Sets the session timeout.
     */
    public static function setSessionTimeout()
    {
        $configs = self::getInstance();
        $timeout = $configs->getSessionTimeout();
        if ( $timeout > 0 )
        {
            $sessionInfo = Zend_Auth::getInstance()->getStorage();
            $ns = new Zend_Session_Namespace($sessionInfo->getNamespace());
            $ns->setExpirationSeconds($timeout);
        }
    }


    // CONSTRUCTOR AND INSTANCE FUNCTIONS

    /**
     * Class constructor
     *
     * Creates a singleton object representing the RAMP configuration 
     * properties.
     */
    protected function __construct()
    {
        $this->_configs =
            Zend_Registry::isRegistered(self::CONFIG_SETTINGS) ?
                Zend_Registry::get(self::CONFIG_SETTINGS) :
                array();
        $this->_activitiesDirectory =
            Zend_Registry::isRegistered(self::ACTIVITIES_DIRECTORY) ?
                Zend_Registry::get(self::ACTIVITIES_DIRECTORY) :
                null;
        $this->_settingsDirectory =
            Zend_Registry::isRegistered(self::SETTINGS_DIRECTORY) ?
                Zend_Registry::get(self::SETTINGS_DIRECTORY) :
                null;
    }

    /**
     * Checks whether the Ramp application is using internal authentication.
     *
     * UNDER CONSTRUCTION
    public function usingInternalAuthentication()
    {
    }
     */

    /**
     * Checks whether sessions should timeout.
    public function getSessionTimeout()
    {
        return $this->_configs[self::SESSION_TIMEOUT] ? : 0;
    }
     */

    /**
     * Gets the session timeout value.
     */
    public function getSessionTimeout()
    {
        return $this->_configs[self::SESSION_TIMEOUT] ? : 0;
    }

    /**
     * Gets the base directory for menus.
     */
    public function getMenuDirectory()
    {
        // Get the settings directory from Zend_Registry.
        return $this->_configs[self::BASE_MENU_DIR] ? : null;
    }

    /**
     * Gets the default menu.
     */
    public function getDefaultMenu()
    {
        if ( isset($this->_configs[self::DEFAULT_MENU]) )
        {
            $defaultMenu = $this->_configs[self::DEFAULT_MENU];
            return $this->_buildMenuFilename($defaultMenu);
        }
        return null;
    }

    /**
     * Returns the given menu name if that file exists, an extended 
     * version of the menu name (built up from the base menu directory 
     * and the given file name) if that file exists, or null.
     *
     * @param $menuFilename
     */
    protected function _buildMenuFilename($menuFilename)
    {
        if ( file_exists($menuFilename) )
        {
            return $menuFilename;
        }
        $menuDir = $this->getMenuDirectory();
        if ( $menuDir != null )
        {
            $extendedFilename = $this->getMenuDirectory() .
                                DIRECTORY_SEPARATOR .  $menuFilename;
            if ( file_exists($extendedFilename) )
            {
                return $extendedFilename;
            }
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
        if ( isset($this->_configs[self::MENU_LIST]) &&
             isset($this->_configs[self::MENU_LIST][$role]) )
        {
            $menu = $this->_configs[self::MENU_LIST][$role];
            $menu = $this->_buildMenuFilename($menu);
            if ( $menu != null )
            {
                return $menu;
            }
        }
        return $this->getDefaultMenu();
    }

    /**
     * Gets the base directory being used for activity specification
     * files.  If there isn't one defined, use the settings directory.
     *
     * @return string   directory path
     */
    public function getActivitiesDirectory()
    {
        return $this->_activitiesDirectory ? : self::getSettingsDirectory();
    }

    /**
     * Gets the base directory being used for table sequence/setting files.
     *
     * @return string   directory path
     */
    public function getSettingsDirectory()
    {
        // Get the settings directory from Zend_Registry.
        return $this->_settingsDirectory;
    }

}

