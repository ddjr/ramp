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
 * @package    Ramp_Controller
 * @copyright  Copyright (c) 2012-2014 Alyce Brady
 *             (http://www.cs.kzoo.edu/~abrady)
 * @license    http://www.cs.kzoo.edu/ramp/LICENSE.txt   Simplified BSD License
 *
 */

/* TODO: might re-organize action functions to handle callbacks first, 
 * especially callbacks that just mean going elsewhere (eliminating the 
 * need to read the table from the database first).
 */

class TableController extends Zend_Controller_Action
{
    /* Table and field that requires special processing */
    const USERS_TABLE       = Ramp_Acl::USERS_TABLE;
    const PASSWORD          = 'password';

    /* values used as keyword parameters (keyword or value) */
    const SUBMIT_BUTTON         = 'submit';
    const SETTING_NAME          = '_setting';
    const SEARCH_TYPE           = '_search';
    const BLOCK_ENTRY_CHOICE    = '_blockEntry';
    const ANY                   = Application_Model_SetTable::ANY;
    const ALL                   = Application_Model_SetTable::ALL;

    /* labels for forms and buttons */
    const VIEW                  = 'View';       // used by Form
    const SEARCH                = "Search";
    const MATCH_ALL             = "Search On All Fields";
    const MATCH_ANY             = "Match Against Any Field";
    const DISPLAY_ALL           = "Display All Entries";
    const TABLE                 = "Tabular Display";
    const SPLIT_VIEW            = "Split View Display";
    const ADD                   = "Add New Entry";
    const CLONE_BUTTON          = "Clone This Entry";
    // const BLOCK_ENTRY_PREFIX    = "Add ";
    // const BLOCK_ENTRY_SUFFIX    = " in a Block";
    const EDIT                  = "Edit Entry";
    const DEL_BUTTON            = "Delete Entry";
    const RESET_BUTTON          = "Reset Fields";
    const CANCEL                = "Cancel";
    const CONFIRM               = "Confirm";
    const SAVE                  = "Save Changes";

    /* values for processing search requests */
    const SEARCH_VALS       = Application_Form_TableRecordEntry::FIELD_VALUES;

    // Constant representing an unspecified enum value for a search
    const ANY_VAL               = Application_Model_SetTable::ANY_VAL;

    // Constants to index the "same" and "different" fields for split views.
    const SAME                  = "same";
    const DIFFERENT             = "different";

    protected $_debugging = false;

    protected $_displayAllView;

    protected $_controllerName;

    protected $_actionName;

    protected $_encodedSeqName;

    protected $_tblViewingSeq;

    protected $_submittedButton;

    protected $_searchType;

    protected $_fieldsToMatch;

    protected $_matchComparators;

    protected $_baseParams = null;

    protected $_matchAbbrevs = array(self::MATCH_ALL => self::ALL,
                                     self::MATCH_ANY => self::ANY);

    /**
     * Initializes the attributes for this object as well as some
     * values commonly used by the associated view scripts.
     */
    public function init()
    {
        // Set the default view for "Display All" actions.
        $this->_displayAllView = "list-view";

        // Get the sequence parameter.
        $seqName =
            Ramp_Controller_KeyParameters::getKeyParam($this->getRequest());
        $this->_encodedSeqName = urlencode($seqName);

        // Set the basic parameters to build on when going to other actions.
        $this->_controllerName = $this->getRequest()->getControllerName();
        $this->_actionName = $this->getRequest()->getActionName();
        $this->_baseParams = array('controller' => $this->_controllerName,
                       self::SETTING_NAME => $this->_encodedSeqName);

        // Get and store other parameters for possible future use.
        $this->_submittedButton = $this->_getParam(self::SUBMIT_BUTTON);
        $this->_searchType = $this->_getParam(self::SEARCH_TYPE);
        $this->_getFieldsToMatch();

        // Initialize values that are passed to the view scripts.
        $this->view->seqSetting = $seqName;
        $this->view->baseParams = $this->_baseParams;
        $this->view->msgs = array();
        $this->view->errMsgs = array();

        // Get the sequence information (types of table settings to use).
        if ( $this->_actionName != "check-syntax" )
        {
            $this->_tblViewingSeq = 
                Application_Model_TVSFactory::getSequenceOrSetting($seqName);
        }

// $this->_debugging = true;

    }

    /**
     * Provides a gateway to this set of actions.  Chooses the actual 
     * action to take based on the initial action defined for this table 
     * setting.
     */
    public function indexAction()
    {
        $this->_forward($this->_tblViewingSeq->getInitialAction());
    }

    /**
     * Controls the search action, presenting a new page in which
     * to add values to match against.
     */
    public function searchAction()
    {
        $setTable = $this->_tblViewingSeq->getSetTableForSearching();

        // Let the view renderer know the table, buttons, and data form to use.
        $this->_initViewTableInfo($setTable);
        $this->view->buttonList = array(self::MATCH_ALL, self::MATCH_ANY,
                                        self::RESET_BUTTON, self::DISPLAY_ALL);
        $this->view->dataEntryForm = $form =
            new Application_Form_TableRecordEntry($setTable, self::SEARCH);

        // Is this the initial display or a callback from a button action?
        if ( $this->_thisIsInitialDisplay() )
        {
            // Have fields been provided?
            if ( ! empty($this->_fieldsToMatch) )
                $this->_executeSearch($setTable, $this->_fieldsToMatch,
                    $this->_matchComparators, self::MATCH_ALL);

            // Otherwise, nothing to do except render view (done automatically).
        }
        else
        {
            $this->_processSearchCallback($setTable, $form);
        }
    }

    /**
     * Executes the search and goes to the appropriate display page.  
     * Only returns if the search failed.
     *
     * @param $setTable    table setting for the table in which to search
     * @param $data        column/value pairs on which to search
     * @param $comparators column/comparator pairs of search comparators
     * @param $buttonLabel button specifying whether to match any specified
     *                     field (OR) or all specified fields (AND)
     */
    protected function _executeSearch($setTable, $data, $comparators,
                                      $buttonLabel)
    {
        // Execute the search and decide how to display the results.
        $matchAbbrev = $this->_matchAbbrevs[$buttonLabel];
        $rows = $setTable->getTableEntries($data, $comparators, $matchAbbrev);
        $numResults = count($rows);
        if ( $numResults == 1 )
        {
            // One match found.
            $keyInfo = $setTable->getKeyInfo($rows[0]);
            $this->_goTo('record-view', $keyInfo);
        }
        elseif ( $numResults > 1 )
        {
            // Multiple matches found.
            $this->_goTo($this->_displayAllView, $data, $comparators,
                         $matchAbbrev);
        }
        else
        {
            // Search failed.
            $this->view->errMsgs[] = "No matching results were found.";
            $this->view->buttonList[] = self::ADD;
            $this->view->dataEntryForm->populate($data);
        }
    }

    /**
     * Processes search call-back after a button has been pressed.
     *
     * @param $setTable   table setting for the table in which to search
     * @param $form       the form used for the search
     */
    protected function _processSearchCallBack($setTable, $form)
    {
        if ( $this->_submittedButton == self::DISPLAY_ALL )
        {
            $this->_goTo($this->_displayAllView);
        }
        else
        {
            $formData = $this->getRequest()->getPost();
            if ( $form->isValid($formData) )
            {
                $fieldVals = $form->getFieldValues();
                $comparators = $form->getComparators();
                $meaningfulData = $this->_getFilledFields($fieldVals,
                                                          $comparators);

                // Adding new entry based on failed search?
                if ( $this->_submittedButton == self::ADD )
                {
                    if ( $this->_illegalCallback($setTable) )
                        { return; }

                    $this->_goTo('add', $meaningfulData);
                }

                // Searching for any or all matches. Display based on 
                // number of results.
                $this->_executeSearch($setTable, $meaningfulData,
                                      $comparators, $this->_submittedButton);

                // Will only get here if search failed.
            }
            else
            {
                // Invalid entries: show them for editing.
                $this->view->errMsgs[] =
                        "Invalid data values.  Please correct.";
                $form->populate($formData);
            }
        }
    }

    /**
     * Provides a list view of table records (usually search results).
     */
    public function listViewAction()
    {
        $setTable = $this->_tblViewingSeq->getSetTableForSearchResults();

        // Is this the initial display or a callback from a button action?
        //    (Or an illegal callback that could allow password corruption?)
        if ( $this->_thisIsInitialDisplay() ||
             $this->_illegalCallback($setTable) )
        {
            // Let view renderer know the table and data set to use.
            $this->view->buttonList = array(self::ADD, self::SPLIT_VIEW,
                                            self::TABLE, self::SEARCH);
            $this->_multiRecordInitDisplay($setTable);
        }
        elseif ( $this->_submittedButton == self::SPLIT_VIEW ||
                 $this->_submittedButton == self::TABLE  ||
                 $this->_submittedButton == self::ADD )
        {
            // Go to a different view with the same data set.
            $action = $this->_getButtonAction($this->_submittedButton);
            $this->_goTo($action, $this->_fieldsToMatch,
                         $this->_matchComparators, $this->_searchType);
        }
        else    // SEARCH or DISPLAY-ALL
            { $this->_goTo($this->_getButtonAction($this->_submittedButton)); }


    }

    /**
     * Provides a tabular view of table records.
     */
    public function tableViewAction()
    {
        $setTable = $this->_tblViewingSeq->getSetTableForTabularView();

        // Is this the initial display or a callback from a button action?
        //    (Or an illegal callback that could allow password corruption?)
        if ( $this->_thisIsInitialDisplay() ||
             $this->_illegalCallback($setTable) )
        {
            // Let view renderer know the table and data set to use.
            $this->view->buttonList = array(self::ADD, self::SPLIT_VIEW,
                                            self::SEARCH);
            $this->_multiRecordInitDisplay($setTable);
        }
        elseif ( $this->_submittedButton == self::SPLIT_VIEW ||
                 $this->_submittedButton == self::ADD )
        {
            // Go to a different view with the same data set.
            $action = $this->_getButtonAction($this->_submittedButton);
            $this->_goTo($action, $this->_fieldsToMatch,
                         $this->_matchComparators, $this->_searchType);
        }
        else    // SEARCH or DISPLAY-ALL
            { $this->_goTo($this->_getButtonAction($this->_submittedButton)); }

    }

    /**
     * Provides a split view of table records.
     */
    public function splitViewAction()
    {
        $setTable = $this->_tblViewingSeq->getSetTableForSplitView();

        // Is this the initial display or a callback from a button action?
        //    (Or an illegal callback that could allow password corruption?)
        if ( $this->_thisIsInitialDisplay() ||
             $this->_illegalCallback($setTable) )
        {
            // Let view renderer know the table and data sets to use.
            $this->view->buttonList = array(self::ADD,
                                            self::TABLE, self::SEARCH);
            $this->_multiRecordInitDisplayWithoutData($setTable);

            // Get full data set and split into shared/different fields.
            $fullDataSet = $setTable->getTableEntries($this->_fieldsToMatch,
                                                      $this->_matchComparators,
                                                      $this->_searchType);
            $dataSplit = $this->_getSplitData($setTable, $fullDataSet);
            $shared = $dataSplit[self::SAME];
            $different = $dataSplit[self::DIFFERENT];

            // Create settings and data sets for split views.
            $sharedViewSetting = $this->view->sharedViewSetting =
                $setTable->createSubsetWithout(array_keys($different));
            $this->view->sharedDataEntryForm =
                new Application_Form_TableRecordEntry($sharedViewSetting,
                                                      self::VIEW, true);
            $this->view->sharedDataEntryForm->populate($shared);
            $differentViewSetting = $this->view->differentViewSetting =
                $setTable->createSubsetWithout(array_keys($shared));
            $this->view->differentDataToDisplay = $fullDataSet;

    // $this->view->sharedFields = $dataSplit[self::SAME];
    // $this->view->differentFields = $dataSplit[self::DIFFERENT];
    // $this->view->sharedFields = $sharedViewSetting->getVisibleFields();
    // $this->view->differentFields = $setTable->getVisibleFields();
    // $fieldsToMatch = $this->_fieldsToMatch;
    // $this->view->sharedDataToDisplay =
    //    $sharedViewSetting->getTableEntries($fieldsToMatch,
    //                                        $this->_matchComparators,
    //                                        $this->_searchType);

        }
        elseif ( $this->_submittedButton == self::TABLE  ||
                 $this->_submittedButton == self::ADD )
        {
            // Go to a different view with the same data set.
            $action = $this->_getButtonAction($this->_submittedButton);
            $this->_goTo($action, $this->_fieldsToMatch,
                         $this->_matchComparators, $this->_searchType);
        }
        else    // SEARCH or DISPLAY-ALL
            { $this->_goTo($this->_getButtonAction($this->_submittedButton)); }

    }

    /**
     * Presents a single record on a page for viewing.
     *
     * Precondition: this action should only be invoked when the 
     * provided parameters uniquely identify a single record.
     */
    public function recordViewAction()
    {
        $setTable = $this->_tblViewingSeq->getSetTableForViewing();

        // Let the view renderer know the table, buttons, and data form to use.
        $this->_initViewTableInfo($setTable);
        $buttonList = array(self::EDIT, self::ADD, self::CLONE_BUTTON);
        /*
        if ( $this->_tblViewingSeq->allowsBlockEntry() )
        {
            $buttonList[] = self::BLOCK_ENTRY_PREFIX .
                            $this->_tblViewingSeq->getBlockEntryFields() .
                            self::BLOCK_ENTRY_SUFFIX;
        }
         */
        $this->view->buttonList = array_merge($buttonList, 
                    array(self::SEARCH, self::DEL_BUTTON, self::DISPLAY_ALL));
        $this->view->dataEntryForm = $form =
                    new Application_Form_TableRecordEntry($setTable);

        // Is this the initial display or a callback from a button action?
        //    (Or an illegal callback that could allow password corruption?)
        if ( $this->_thisIsInitialDisplay() ||
             $this->_illegalCallback($setTable) )
        {
            // Retrieve record based on provided fields / primary keys.
            $form->populate($setTable->getTableEntry($this->_fieldsToMatch));
        }
        elseif ( $this->_submittedButton == self::CLONE_BUTTON )
        {
            $this->_goTo('add',
                         $setTable->getCloneableFields($this->_fieldsToMatch));
        }
        elseif ( $this->_submittedButton == self::EDIT )
            { $this->_goTo('record-edit', $this->_fieldsToMatch); }
        elseif ( $this->_submittedButton == self::DEL_BUTTON )
            { $this->_goTo('delete', $this->_fieldsToMatch); }
        else
            { $this->_goTo($this->_getButtonAction($this->_submittedButton)); }

    }

    /**
     * Controls the editing action for a single, editable record on a page.
     *
     * Precondition: this action should only be invoked when the 
     * parameters provided uniquely identify a single record.
     */
    public function recordEditAction()
    {
        $setTable = $this->_tblViewingSeq->getSetTableForModifying();

        // Let the view renderer know the table, buttons, and data form to use.
        $this->_initViewTableInfo($setTable);
        $this->view->buttonList = array(self::SAVE, self::RESET_BUTTON,
                                        self::CANCEL, self::DEL_BUTTON);
        $this->view->dataEntryForm = $form =
              new Application_Form_TableRecordEntry($setTable, self::EDIT);

        // Is this the initial display or the post-edit callback?
        if ( $this->_thisIsInitialDisplay() )
        {
            // Retrieve record based on provided fields / primary keys.
            $this->_acquireLock($setTable, $this->_fieldsToMatch);
            $form->populate($setTable->getTableEntry($this->_fieldsToMatch));
        }
        elseif ( $this->_submittedButton == self::SAVE )
        {
            // Process the filled-out form that has been posted:
            // if the changes are valid, update the database.
            $formData = $this->getRequest()->getPost();
            if ($form->isValid($formData))
            {
                // Update the database and redisplay the record.
                $setTable->updateTableEntry($form->getFieldValues());
                $this->_releaseLock($setTable, $this->_fieldsToMatch);
                $this->_goTo('record-view', $this->_fieldsToMatch);
            }
            else
            {
                // Invalid entries: show them for editing.
                $this->view->errMsgs[] =
                        "Invalid data values.  Please correct.";
                $form->populate($formData);
            }
        }
        elseif ( $this->_submittedButton == self::DEL_BUTTON )
        {
            $this->_releaseLock($setTable, $this->_fieldsToMatch);
            $this->_goTo('delete', $this->_fieldsToMatch);
        }
        else  // Cancel
        {
            $this->_releaseLock($setTable, $this->_fieldsToMatch);
            $this->_goTo('record-view', $this->_fieldsToMatch);
        }

    }

    /**
     * Controls the Table add action, presenting a new page in which
     * to add a new entry to the table.
     */
    public function addAction()
    {
        $setTable = $this->_tblViewingSeq->getSetTableForAdding();

        // Let the view renderer know the table, buttons, and data form to use.
        $this->_initViewTableInfo($setTable);
        $this->view->buttonList = array(self::SAVE, self::RESET_BUTTON,
                                        self::CANCEL, self::SEARCH);
        $this->view->dataEntryForm = $form =
                new Application_Form_TableRecordEntry($setTable, self::ADD);

        // Is this the initial display or the callback with fields provided?
        if ( $this->_thisIsInitialDisplay() )
        {
            // If "starter" fields have been provided, fill them in.
            if ( ! empty($this->_fieldsToMatch) )
            {
                $form->populate($this->_fieldsToMatch);
            }
        }
        elseif ( $this->_submittedButton == self::SAVE )
        {
            // Process the filled-out form that has been posted:
            // if the changes are valid, update the database.
            $formData = $this->getRequest()->getPost();
            if ($form->isValid($formData))
            {
                // Determine fields being added.  Remove null fields.
                $addValues = $this->_fillInitValues($setTable,
                                                    $form->getFieldValues());
                $meaningfulData = $this->_getFilledFields($addValues);

                // Update the database and redisplay the record.
                $setTable->addTableEntry($meaningfulData);
                $this->_executeSearch($setTable, $meaningfulData, null,
                                      self::DISPLAY_ALL);
            }
            else
            {
                // Invalid entries: show them for editing.
                $this->view->errMsgs[] =
                        "Invalid data values.  Please correct.";
                $form->populate($formData);
            }
        }
        elseif ( $this->_submittedButton == self::CANCEL )
            { $this->_goTo('index'); }
        else
            { $this->_goTo($this->_getButtonAction($this->_submittedButton)); }
        
    }

    /**
     * Controls the Table delete action, presenting a confirmation page
     * before deleting an existing entry from the table.
     * TODO: Confirmation could be handled on the client side, in which
     * case only confirmed delete requests would get to the server.  In
     * that  case, processing of deletion requests would probably move
     * to the  appropriate display or edit actions.
     */
    public function deleteAction()
    {
        $setTable = $this->_tblViewingSeq->getSetTableForDeleting();

        // Let the view renderer know the table, buttons, and data form to use.
        $this->_initViewTableInfo($setTable);
        $this->view->buttonList = array(self::CONFIRM, self::CANCEL);
        $this->view->dataEntryForm = $form =
                    new Application_Form_TableRecordEntry($setTable,
                                                          self::DEL_BUTTON);

        // Is this the initial display or the callback with confirmation?
        if ( $this->_thisIsInitialDisplay() )
        {
            // Retrieve the table entry to display based on the key(s);
            // assign to view for confirmation.
            $this->_acquireLock($setTable, $this->_fieldsToMatch);
            $form->populate($setTable->getTableEntry($this->_fieldsToMatch));
        }
        elseif ( $this->_submittedButton == self::CANCEL )
        {
            $this->_releaseLock($setTable, $this->_fieldsToMatch);
            $this->_goTo('record-view', $this->_fieldsToMatch);
        }
        else        // Delete has been confirmed.
        {
            // Process the posted delete confirmation information. 
            $formData = $this->getRequest()->getPost();
            if ( $form->isValid($formData) )
            {
                // Get the lock information, including the key on which 
                // the record was locked BEFORE deleting record!
                $lockInfo = $this->_getLockInfo($setTable,
                                                $this->_fieldsToMatch);
                $rows = $setTable->deleteTableEntry($form->getFieldValues());
                $this->_releaseLock(null, null, $lockInfo);
                // TODO: report to user that entry was deleted!
            }

            // After deletion, return to initial page.
            $this->_goTo('index');
        }

    }

    /**
     * Controls the Table block entry action, presenting a new page in which
     * to add multiple entries at once to a table.
     */
    public function blockEntryAction()
    {
        $seq = $this->_tblViewingSeq;
        if ( ! $seq->allowsBlockEntry() )
        {
            throw new Exception("Table sequence " . $seq->getSeqName() .
                        " does not have a valid blockEntry specification.");
        }
        $blockEntryChoice = $this->_getParam(self::BLOCK_ENTRY_CHOICE);
        $blockEntrySeq = $seq->getBlockEntrySeq($blockEntryChoice);

        $setTable = $blockEntrySeq->getSetTableForSummary();

        /*
        // Let the view renderer know the table, buttons, and data form to use.
        $this->_initViewTableInfo($setTable);
        $this->view->buttonList = array(self::SAVE, self::RESET_BUTTON,
                                        self::CANCEL, self::SEARCH);
        $this->view->dataEntryForm = $form =
                new Application_Form_TableRecordEntry($setTable, self::ADD);

        // Is this the initial display or the callback with fields provided?
        if ( $this->_thisIsInitialDisplay() )
        {
            // If "starter" fields have been provided, fill them in.
            if ( ! empty($this->_fieldsToMatch) )
            {
                $form->populate($this->_fieldsToMatch);
            }
        }
        elseif ( $this->_submittedButton == self::SAVE )
        {
            // Process the filled-out form that has been posted:
            // if the changes are valid, update the database.
            $formData = $this->getRequest()->getPost();
            if ($form->isValid($formData))
            {
                // Determine fields being added.  Remove null fields.
                $addValues = $this->_fillInitValues($setTable,
                                                    $form->getFieldValues());
                $meaningfulData = $this->_getFilledFields($addValues);

                // Update the database and redisplay the record.
                $setTable->addTableEntry($meaningfulData);
                $this->_executeSearch($setTable, $meaningfulData, null,
                                      self::DISPLAY_ALL);
            }
            else
            {
                // Invalid entries: show them for editing.
                $this->view->errMsgs[] =
                        "Invalid data values.  Please correct.";
                $form->populate($formData);
            }
        }
        elseif ( $this->_submittedButton == self::CANCEL )
        {
            $this->_goTo('index');
        }
        else
        {
            $this->_goTo($this->_getButtonAction($this->_submittedButton));
        }
         */
    }

    /**
     * Initializes basic view renderer information from the set table.
     *
     * @param Application_Model_SetTable  table: setting & db info
     */
    protected function _initViewTableInfo($setTable)
    {
        $this->view->tableInfo = $setTable;
        $this->view->undefinedFieldsErrorMsg =
            $this->_getExtraneousFieldsErrorMsg($setTable);

        if ( count($setTable->getVisibleFields()) == 0 )
        {
            $this->view->errMsgs[] =
                    "This table setting has no visible fields.";
        }

        if ( $this->_debugging )
        {
            $this->view->errMsgs[] = "DEBUGGING INFO: ";
            $this->view->errMsgs[] = " Base params are: "
                        . print_r($this->_baseParams, true);
            $this->view->errMsgs[] = " Request params are: "
                        . print_r($this->getRequest()->getParams(), true);
            $this->view->errMsgs[] = " Fields to match are: "
                        . print_r($this->_fieldsToMatch, true);
        }

    }

    /**
     * Builds an error message if there are fields specified in the 
     * table setting that do not exist in the database.
     *
     * @param Application_Model_SetTable  table: setting & db info
     * @return string       error message (or empty string if no error)
     */
    protected function _getExtraneousFieldsErrorMsg($setTable)
    {
        $msg = "";
        $undefinedFields = $setTable->getUndefinedFieldNames();
        if ( count($undefinedFields) > 0 )
        {
            $msg = "Note: the following fields in the table setting are
                    undefined in the database: ";
            $delim = "";
            foreach ($undefinedFields as $field)
            {
                $msg .= $delim . $field;
                $delim = ", ";
            }
        }
        return $msg;
    }

    /**
     * Returns true if the current request represents the initial 
     * display for the current action.  A return of false, therefore, 
     * indicates that the current request represents the callback with 
     * fields to add, modify, or search filled in.
     */
    protected function _thisIsInitialDisplay()
    {
        return !  $this->getRequest()->isPost();
    }

    /**
     * Checks whether this is a callback that would lead to the ability 
     * to set or modify encrypted passwords in the RAMP Users table.
     * Generates an error message if this is such an illegal callback.
     * (Should only be an issue when doing internal authentication, but
     * the consequences are so severe that we should always check.)
     *
     * Pre-condition: should only be called on button callbacks, i.e., 
     *    when $this->_thisIsInitialDisplay() is false.
     *
     * @return true if button action would go to an ADD or EDIT form and
     *              the setting potentially allows corruption of encrypted 
     *              passwords in the Users table; false otherwise
     */
    protected function _illegalCallback($setTable)
    {
        // Get the appropriate table setting based on the button action.
        if ( $this->_submittedButton == self::ADD ||
             $this->_submittedButton == self::CLONE_BUTTON )
        {
            $setTable = $this->_tblViewingSeq->getSetTableForAdding();
        }
        elseif ( $this->_submittedButton == self::EDIT )
        {
            $setTable = $this->_tblViewingSeq->getSetTableForModifying();
        }
        else
        {
            // This is not a modifying callback.
            return false;
        }

        // Does this table setting allow modification of password field 
        // in RAMP Users table?  (Is password field visible in setting?)
        if ( $setTable->getDbTableName() == self::USERS_TABLE &&
             $setTable->getFieldObject(self::PASSWORD)->isVisible() )
        {
            $this->view->errMsgs[] =
                "Table Setting Error: May not Add, Edit, or Clone records " .
                "using forms that allow manipulation of encrypted passwords.";
            return true;
        }

        return false;
    }

    /**
     * Set up initial display (except for buttonList) for actions
     * involving multiple records.
     */
    protected function _multiRecordInitDisplay($setTable)
    {
        $this->_multiRecordInitDisplayWithoutData($setTable);

        // Let view renderer know the table and data set to use.
        $this->view->dataToDisplay =
            $setTable->getTableEntries($this->_fieldsToMatch,
                                       $this->_matchComparators,
                                       $this->_searchType);

    }

    /**
     * Defines basic display parameters, not including the buttonList 
     * nor the data to display.
     */
    protected function _multiRecordInitDisplayWithoutData($setTable)
    {
        $this->_initViewTableInfo($setTable);

        // Let view renderer know whether this is a subset of the table.
        $this->view->displayingSubset = ! empty($this->_fieldsToMatch);
        if ( $this->view->displayingSubset )
        {
            $this->view->buttonList[] = self::DISPLAY_ALL;
        }

        // List items will get filled-in status based on ADD setting.
        $this->view->addSetting =
            $this->_tblViewingSeq->getSetTableForAdding();
    }

    /**
     * Get two views of the data, one of which has only the fields that 
     * have the same value for all rows (the other fields are there, but 
     * hidden), and one of which has only the fields whose values are 
     * different across rows (the shared fields are there, but hidden).
     */
    protected function _getSplitData($setTable, $fullDataSet)
    {
        $firstRow = $fullDataSet[0];

        // Determine which fields go in "same" array, and which go in 
        // "different".  (Empty fields are not considered "same".)
        $data = array(self::SAME => array(), self::DIFFERENT => array());
        $allVisibleFields = $setTable->getVisibleFields();
        foreach ( $allVisibleFields as $fieldName => $field )
        {
            $whichArray = $this->_allRowsMatch($fullDataSet, $fieldName)
                            ? self::SAME : self::DIFFERENT;
            $data[$whichArray][$fieldName] = $field;
        }

        // For "same" array, replace Field objects with shared data.
        foreach ( $data[self::SAME] as $fieldName => $field )
        {
            $data[self::SAME][$fieldName] = $firstRow[$fieldName];
        }
// throw new Exception("data['same']: " . print_r($data['same'], true) . "<br />data['diff']: " . print_r(array_keys($data['different']), true) .  "<br />firstRow: " . print_r($firstRow, true));

        return $data;
    }

    /**
     * Determines whether all rows have matching data for the given 
     * field.
     */
    protected function _allRowsMatch($allData, $fieldName)
    {
        $firstRow = $allData[0];
        array_shift($allData);
        $matchVal = $firstRow[$fieldName];
        if ( empty($matchVal) )
            { return false; }

        foreach ( $allData as $row )
        {
            if ( $row[$fieldName] != $matchVal )
                { return false; }
        }
        return true;
    }

    /**
     * Gets the action usually associated with the given button.
     */
    protected function _getButtonAction($buttonLabel)
    {
        $commonMapping = array(
            self::SEARCH => 'search',
            self::DISPLAY_ALL => $this->_displayAllView,
            self::ADD => 'add', self::EDIT => 'record-edit',
            self::DEL_BUTTON => 'delete', self::TABLE => 'table-view',
            self::SPLIT_VIEW => 'split-view');

        return isset($commonMapping[$buttonLabel]) ?
                $commonMapping[$buttonLabel] : null;
    }

    /**
     * Gets the user data from request parameters as name=>value pairs
     * to use in database queries and stores it in 
     * $this->_fieldsToMatch.
     */
    protected function _getFieldsToMatch()
    {
        $request = $this->getRequest();

        // Remove Zend and Ramp keyword parameters.
        $paramsToRemove = array( 
                                'controller' => null,
                                'action' => null,
                                'module' => null,
                                self::SETTING_NAME => null,
                                self::SUBMIT_BUTTON => null,
                                self::SEARCH_TYPE => null,
                                self::BLOCK_ENTRY_CHOICE => null,
                            );

        // Return an array that does not include those parameters.
        $this->_fieldsToMatch = array();
        $userParams = array_diff_key($request->getUserParams(),
                                     $paramsToRemove);
        foreach ( $userParams as $encodedKey => $encodedVal )
        {
            $key = urldecode($encodedKey);
            $parts = explode('?', $encodedVal);
            $this->_fieldsToMatch[$key] = urldecode($parts[0]);
            if ( count($parts) > 1 )
            {
                $this->_matchComparators[$key] = urldecode($parts[1]);
            }
        }
    }

    /**
     * Redirects the action, passing whatever parameters are 
     * appropriate.
     *
     * @param string $nextAction     the name of the next action
     * @param array $matchingFields  fields and values to search/select for
     * @param array $comparators     search comparators to pass (if any)
     * @param bool $searchType       search type to pass (if any)
     */
    protected function _goTo($nextAction, $matchingFields = null,
                             $comparators = null, $searchType = null)
    {
        // Build up parameters to pass to next action.
        $params = array(self::SETTING_NAME => $this->_encodedSeqName);
        if ( ! empty($searchType) )
        {
            $params[self::SEARCH_TYPE] = $searchType;
        }
        if ( $matchingFields != null )
        {
            if ( empty($comparators) )
            {
                foreach ( $matchingFields as $key => $val )
                {
                    $params[urlencode($key)] = urlencode($val);
                }
            }
            else
            {
                foreach ( $matchingFields as $key => $val )
                {
                    $params[urlencode($key)] =
                        urlencode($val) . '?' . urlencode($comparators[$key]);
                }
            }
        }

        $this->_helper->redirector($nextAction, $this->_controllerName,
                                   null, $params);
    }

    /**
     * Filters out fields for which no values were provided (unless the 
     * field has a unary comparator and doesn't need a value).
     *
     * @param array $data   Column-value pairs
     * @param $comparators  Column/comparator pairs of search comparators
     * @return array        Column-value pairs, with no empty values 
     *                      (unless they are significant for a search)
     */
    protected function _getFilledFields(array $data, $comparators = array())
    {
        // Remove column-value pairs with null values.
        $meaningfulData = array();
        foreach ( $data as $field => $value )
        {
            if ( $this->_isUnaryComparator($field, $comparators) )
            {
                $meaningfulData[$field] = $value;
            }
            else if ( $value !== null && $value != "" &&
                      $value != self::ANY_VAL )
            {
                $meaningfulData[$field] = $value;
            }
        }
        return $meaningfulData;
    }

    /**
     * Determines whether a field is significant, despite not having a 
     * value,  because it is tied to a unary comparator which does not 
     * need a value.
     */
    protected function _isUnaryComparator($field, $comparators)
    {
        return isset($comparators[$field]) &&
               in_array($comparators[$field],
                       Application_Form_TableRecordEntry::unaryComparators());
    }

    /**
     * Fill in initial values based on information in setting.
     * TODO: Is there any need to consider cases where the 
     * "external table" is this table, so one field is being
     * initialized  from another that is not in the database yet?
     *
     * @param Application_Model_SetTable  table: setting & db info
     * @param array $data   Column-value pairs representing provided data
     */
    protected function _fillInitValues($setTable, array $data)
    {
        $storedSourceRecs = array();
        $searchData = $data;
        $initializedData = $data;

        // Loop through fields in this table to see if any should be 
        // initialized from values in another table.
        $inputFieldNames = array_keys($data);
        $relevantFields = $this->view->tableInfo->getExternallyInitFields();
        // Why wasn't this written as the simpler:   ?
        // $relevantFields = $setTable->getExternallyInitFields();
        foreach ( $relevantFields as $newFieldName => $newField )
        {
            // Initialize from another table if data not already provided.
            if ( $data[$newFieldName] == null )
            {
                // Determine initializing table; see if source record 
                // has already been retrieved.
                $sourceTblName = $newField->getInitTableName();
                if ( ! isset($storedSourceRecs[$sourceTblName]) )
                {
                    $sourceRecord =
                            $this->_getSourceRecord($setTable, $sourceTblName,
                                                    $data);
                    if ( $sourceRecord == null )
                    {
                        continue;
                    }
                    else
                    {
                        $storedSourceRecs[$sourceTblName] = $sourceRecord;
                    }
                }
                $sourceRecord = $storedSourceRecs[$sourceTblName];
                $initializedData[$newFieldName] = $sourceRecord[$newFieldName];
            }
        }

        return $initializedData;
    }

    /**
     * Gets the record with the initializing information.
     *
     * @param Application_Model_SetTable $setTable    setting & db info
     * @param string $sourceTblName  name of table with initializing info
     * @param array $userData        fields with user-supplied data
     */
    protected function _getSourceRecord($setTable, $sourceTblName, $userData)
    {
        // Need to retrieve initializing record; check that
        // user provided enough information to find it.
        $initRef = $setTable->getInitRefInfo($sourceTblName);
        $sourceTbl = $initRef->getViewingSeq()->getReferenceSetTable();
        $searchKeys = $initRef->xlFieldValuePairs($userData);
        $matches = $sourceTbl->getTableEntries($searchKeys);
        if ( count($matches) != 1 )
        {
            // Cannot initialize with info provided; proceed 
            // directly to next field.
            $this->view->errMsgs[] =
                "Cannot initialize $newFieldName from " .
                "$sourceTblName -- insufficient primary " .
                "key information to find source record.";
            return null;
        }
        else
        {
            return $matches[0];
        }
    }

    /**
     * Acquires the lock for the given record in the specified table.
     *
     * @param Application_Model_SetTable $setTable    setting & db info
     * @param array $matchingFields  fields and values to search/select for
     */
    protected function _acquireLock($setTable, $matchingFields)
    {
        // Get the information needed to acquire a lock.
        $lockInfo = $this->_getLockInfo($setTable, $matchingFields);

        // Get the lock (if possible).
        $locksTable = new Application_Model_DbTable_Locks();
        if ( ! $locksTable->acquireLock($lockInfo) )
        {
            // Notify user that lock is unavailable.
            $params = array(Application_Model_DbTable_Locks::USER =>
                            urlencode($user));
            $this->_helper->redirector('unavailable-lock', 'lock', null,
                                       $params);
        }
    }

    /**
     * Releases the lock for the given record in the specified table.
     * Uses the $setTable and $matchingFields parameters to determine 
     * the lock information unless the optional $lockInfo parameter is
     * provided.
     *
     * @param Application_Model_SetTable $setTable    setting & db info
     * @param array $matchingFields  fields and values to search/select for
     * @param $lockInfo  the information to use to lock (if already known)
     */
    protected function _releaseLock($setTable, $matchingFields,
                                    $lockInfo = null)
    {
        // Get the information needed to release a lock.
        if ( $lockInfo == null )
        {
            $lockInfo = $this->_getLockInfo($setTable, $matchingFields);
        }

        // Release the lock.
        $locksTable = new Application_Model_DbTable_Locks();
        $locksTable->releaseLock($lockInfo);
    }

    /**
     * Gets the lock information for the lock to acquire or release 
     * based on the given set table, the matching fields, and the Lock 
     * Relations table.
     *
     * @param Application_Model_SetTable $setTable    setting & db info
     * @param array $matchingFields  fields and values to search/select for
     */
    protected function _getLockInfo($setTable, $matchingFields)
    {
        // Get the locking table and key field name.
        $lockRelationsTable = new Application_Model_DbTable_LockRelations();
        $lookupInfo =
                $lockRelationsTable->getLockInfo($setTable->getDbTableName());
        $keyToLookup =
            $lookupInfo[Application_Model_DbTable_LockRelations::LOCKING_KEY_NAME];

        // Get the key used for locking
        $recordToLock = $setTable->getTableEntry($matchingFields);
        $lockingKey = $recordToLock[$keyToLookup];

        // Get the user information.
        $auth = Zend_Auth::getInstance();
        if ( $auth->hasIdentity() )
        {
            $user = $auth->getIdentity()->username;
        }
        else
        {
            // Use DEFAULT_ROLE if user is not logged in.
            $user = Ramp_Acl::DEFAULT_ROLE;
        }

        // Construct $lockInfo object.
        $lockInfo = array();
        $lockInfo[Application_Model_DbTable_Locks::LOCK_TABLE] =
            $lookupInfo[Application_Model_DbTable_LockRelations::LOCK_TABLE];
        $lockInfo[Application_Model_DbTable_Locks::LOCKING_KEY] = $lockingKey;
        $lockInfo[Application_Model_DbTable_Locks::USER] = $user;

        return $lockInfo;

    }

}

