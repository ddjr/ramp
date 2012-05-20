<?php

/**
 * FYI: Meta Info contains: 
 *      [SCHEMA_NAME]
 *      [TABLE_NAME]
 *      [COLUMN_NAME]
 *      [COLUMN_POSITION]
 *      [DATA_TYPE]
 *      [DEFAULT]
 *      [NULLABLE]
 *      [LENGTH]
 *      [SCALE]
 *      [PRECISION]
 *      [UNSIGNED]
 *      [PRIMARY]
 *      [PRIMARY_POSITION]
 *      [IDENTITY]
 */

/**
 * RAMP: Records and Activity Management Program
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Ramp
 * @package    Ramp_Model
 * @copyright  Copyright (c) 2012 Alyce Brady (http://www.cs.kzoo.edu/~abrady)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Config.php 23775 2011-03-01 17:25:24Z ralph $
 */

class Application_Model_Field
{

    // Constants representing field setting properties.
    const HIDE          = 'hide';
    const LABEL         = 'label';
    const FOOTNOTE      = 'footnote';
    const READ_ONLY     = 'readOnly';
    const RECOMMENDED   = 'recommended';
    const DISCOURAGED   = 'discouraged';
    const INIT_TBL      = 'initFrom';
    const INIT_FIELD    = 'initFromField';
    const IMPORTED      = 'importedFrom';
    const ALIAS         = 'importedField';
    const LINK_TO_TBL   = 'selectUsing';

    // Constants representing db metadata properties.
    const NULLABLE      = 'NULLABLE';
    const PRIMARY       = 'PRIMARY';
    const DB_DEFAULT    = 'DEFAULT';
    const IDENTITY      = 'IDENTITY';
    const LENGTH        = 'LENGTH';

    /** @var string */
    protected $_name;           // field name

    /** @var string */
    protected $_label;          // field label

    /** @var string */
    protected $_footnote;       // field footnote

    /** @var bool */
    protected $_visible;        // Should the field be visible?

    /** @var bool */
    protected $_readOnly;       // Should the field be read-only?

    /** @var bool */
    protected $_recommended = false;  // suggestion that a value should be
                                      // provided (informational)

    /** @var bool */
    protected $_discouraged = false;  // suggestion that a value should be
                                      // left alone (informational)

    /** @var bool */
    protected $_inDB;           // Is the field in the database table?

    /** @var string */
    protected $_initTable;      // name of setting to use to initialize field

    /** @var string */
    protected $_initField;      // name of field from which to initialize

    /** @var string */
    protected $_importTbl;      // name of table from which imported, or null

    /** @var string */
    protected $_importName;     // alias name for field being imported
                                // (name in the import table)

    /** @var string */
    protected $_connectTbl;     // name of table to which this field is a link

    /** @var array */
    protected $_metaInfo;       // meta-information provided by database

    /**
     * Class constructor
     *
     * Creates an object that represents all the information known about 
     * a field.
     *
     * @param string $fieldName     the name of the field
     * @param array $settingInfo    field information from the table setting
     * @param array $metaInfo       field information from the database
     * @param bool $showColsByDefault true if the table setting 
     *                                specified showing columns by default
     * @return void
     */
    public function __construct($fieldName,
                                array $settingInfo = array(),
                                array $metaInfo = array(),
                                $showColsByDefault = false)
    {
        $this->_name = $fieldName;
        $this->_metaInfo = $metaInfo;

        $this->init($settingInfo, $metaInfo, $showColsByDefault);
    }

    /**
     * Initializes field attributes from information provided by the 
     * table setting.
     *
     * @param array $settingInfo    field information from the table setting
     * @param array $metaInfo       field information from the database
     * @param bool $showColsByDefault true if the table setting 
     *                                specified showing columns by default
     * @return Application_Model_Field
     */
    public function init(array $settingInfo, array $metaInfo,
                         $showColsByDefault)
    {

        // Initialize attributes.  If no label is provided, use the 
        // column name from the database.  A column is assumed to be 
        // visible (not hidden) if a label is provided for it or if the 
        // table setting says to show all columns by default, although
        // even then a column can be explicitly marked as hidden.
        $this->_label = isset($settingInfo[self::LABEL]) ?
                            $settingInfo[self::LABEL] :
                            $this->_name;
        $this->_footnote = isset($settingInfo[self::FOOTNOTE]) ?
                            $settingInfo[self::FOOTNOTE] :
                            "";
        $this->_readOnly = isset($settingInfo[self::READ_ONLY]) &&
                            $settingInfo[self::READ_ONLY] == true;
        $explicitly_visible = isset($settingInfo[self::HIDE]) &&
                            $settingInfo[self::HIDE] == false;
        $assumed_visible = isset($settingInfo[self::LABEL]) ||
                            $showColsByDefault;
        $explicitly_hidden = isset($settingInfo[self::HIDE]) &&
                            $settingInfo[self::HIDE] == true;
        $this->_visible = $explicitly_visible ||
                          ($assumed_visible && ! $explicitly_hidden);
        $this->_recommended = isset($settingInfo[self::RECOMMENDED]) &&
                              $settingInfo[self::RECOMMENDED];
        $this->_discouraged = isset($settingInfo[self::DISCOURAGED]) &&
                              $settingInfo[self::DISCOURAGED];
        $this->_initTable = isset($settingInfo[self::INIT_TBL]) ?
                            $settingInfo[self::INIT_TBL] :
                            null;
        $this->_initField = isset($settingInfo[self::INIT_FIELD]) ?
                            $settingInfo[self::INIT_FIELD] :
                            $this->_name;
        $this->_importTbl = isset($settingInfo[self::IMPORTED]) ?
                            $settingInfo[self::IMPORTED] :
                            null;
        $this->_importName = isset($settingInfo[self::ALIAS]) ?
                            $settingInfo[self::ALIAS] :
                            $this->_name;
        $this->_connectTbl = isset($settingInfo[self::LINK_TO_TBL]) ?
                            $settingInfo[self::LINK_TO_TBL] :
                            null;
        $this->_inTable = ! empty($metaInfo);

        return $this;
    }

    /**
     * Gets the name of the field in the database.
     *
     * return string    field name
     */
    public function getDbFieldName()
    {
        return $this->_name;
    }

    /**
     * Returns the data type for this field, if the field is in the 
     * "local" table.  If the field is imported, returns String as the
     * best default for Text input boxes.
     *
     * @return 
     */
    public function isEnum()
    {
        return $this->_inTable &&
               substr($this->_metaInfo['DATA_TYPE'], 0, 4) == "enum";
    }

    /**
     * Returns the data type for this field, if the field is in the 
     * "local" table.  If the field is imported, returns String as the
     * best default for Text input boxes.
     *
     * Precondition: isEnum() is true
     *
     * @return 
     */
    public function getEnumValues()
    {
        $options = array();

        // Trim away "enum(" and the trailing ")".
        $valueString = $this->_metaInfo['DATA_TYPE'];
        $valueString = substr($valueString, 5, strlen($valueString) - 6);
        $optionNames = explode(",", $valueString);

        // Trim quotes off the values and add to options array.
        foreach ( $optionNames as $optionName )
        {
            $optionName = substr($optionName, 1, strlen($optionName) - 2);
            $options[$optionName] = $optionName;
        }
        return $options;
    }

    /**
     * Returns the data type for this field, if the field is in the 
     * "local" table.  If the field is imported, returns String as the
     * best default for Text input boxes.
     *
     * @return 
     */
    public function getDataType()
    {
        return ( $this->_inTable ) ?  $this->_metaInfo['DATA_TYPE'] : null;
    }

    /**
     * Is this field present in the database table?
     *
     * @return bool
     */
    public function isInTable()
    {
        return $this->_inTable;
    }

    /**
     * Should this field be initialized from another database table?
     *
     * @return bool
     */
    public function initFromAnotherTable()
    {
        return $this->_inTable && $this->_initTable !== null;
    }

    /**
     * Get the name of the database table from which this field
     * should be initialized.
     *
     * @return string  name of database table
     */
    public function getInitTableName()
    {
        return $this->_initTable;
    }

    /**
     * Get the database field from which this field should be initialized.
     *
     * Precondition: $this->initFromAnotherTable()
     *
     * @return string  name of field in other table
     */
    public function getInitField()
    {
        return $this->_initField;
    }

    /**
     * Is this field imported from another database table?
     *
     * @return bool
     */
    public function isImported()
    {
        return $this->_importTbl !== null;
    }

    /**
     * Get the database table from which this field is imported.
     *
     * @return string  name of database table
     */
    public function getImportTable()
    {
        return $this->_importTbl;
    }

    /**
     * Gets the name to use when importing this field from another 
     * table.
     *
     * Precondition: $this->isImported()
     *
     * @return string  name of field in other table
     */
    public function resolveAlias()
    {
        return $this->_importName;
    }

    /**
     * Is this field in the database, whether in the current table or
     * imported from another table?
     *
     * @return bool
     */
    public function isInDB()
    {
        return $this->_inTable || $this->_importTbl;
    }

    /**
     * Does this field link to information in another database table?
     *
     * @return bool
     */
    public function isExternalTableLink()
    {
        return $this->_connectTbl !== null;
    }

    /**
     * Get the database table to which this field is a link.
     *
     * @return string  name of database table
     */
    public function getLinkedTable()
    {
        $extTable = Application_Model_TVSFactory::getSequenceOrSetting(
                                    $this->_connectTbl);
        $setting = Application_Model_TableViewSequence::SEARCH_RES_SETTING;
        return $extTable->getSetTable($setting)->getTitle();
    }

    /**
     * Get the database table setting to which this field is a link.
     *
     * @return string  name of database table
     */
    public function getLinkedTableSetting()
    {
        return $this->_connectTbl;
    }

    /**
     * Should this field always be read-only, according to the table
     * setting?
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->_readOnly;
    }

    /**
     * Should this field be visible, according to the table setting?
     * Fields are visible by default when a label is provided in the 
     * table setting (unless explicitly marked as hidden), and also when 
     * marked as visible (hidden is false) in the setting, even if no 
     * label is provided.
     *
     * @return bool
     */
    public function isVisible()
    {
        return $this->_visible;
    }

    /**
     * Gets the label or column heading for this field from the table 
     * setting.  If no label was provided, returns the name from the database.
     *
     * return string    field label
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * Gets the field footnote (if any) from the table setting.  If no 
     * footnote was provided, returns an empty string.
     *
     * return string    table footnote
     */
    public function getFieldFootnote()
    {
        return $this->_footnote;
    }

    /**
     * Returns the meta information for this field.
     *
     * @return 
     */
    public function getMetaInfo()
    {
        return $this->_metaInfo;
    }

    /**
     * Returns the length of this field (only meaningful for some data 
     * types, such as varchar).
     *
     * @return int
     */
    public function getLength()
    {
        return ( $this->_inTable && $this->_metaInfo[self::LENGTH] ) ?
                     $this->_metaInfo[self::LENGTH] : 0;
    }

    /**
     * Is this field required by the database?
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->_inTable && ! $this->_metaInfo[self::NULLABLE];
    }

    /**
     * Is providing data for this field recommended (even if the field 
     * is not required by the database)?
     *
     * @return bool
     */
    public function isRecommended()
    {
        return $this->_inTable && $this->_recommended;
    }

    /**
     * Is providing or modifying data for this field discouraged (usually
     * because it is best set automatically by defaults, triggers, etc.)?
     *
     * @return bool
     */
    public function isDiscouraged()
    {
        return $this->_inTable && $this->_discouraged;
    }

    /**
     * Is this field a primary key?
     *
     * @return bool
     */
    public function isPrimaryKey()
    {
        return $this->_inTable && $this->_metaInfo[self::PRIMARY];
    }

    /**
     * Is this field auto-incrementable?
     *
     * @return bool
     */
    public function isAutoIncremented()
    {
        return $this->_inTable && $this->_metaInfo[self::IDENTITY];
    }

    /**
     * Gets the default provided in the database for this field (if 
     * any).
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->_inTable ? $this->_metaInfo[self::DB_DEFAULT] : null;
    }

    /**
     * Can entries be added to the table even if this field is not
     * provided?
     *
     * @return bool
     */
    public function valueNecessaryForAdd()
    {
        return ( $this->isPrimaryKey() || $this->isRequired() )
               && $this->getDefault() == null
               && ! $this->isAutoIncremented();
    }

}
