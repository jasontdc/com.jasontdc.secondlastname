<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Secondlastname_Upgrader extends CRM_Secondlastname_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed.
   * $this->executeSqlFile('sql/myinstall.sql');
   */
  public function install() {

    try{

      //see if the custom group already exists
      $result = civicrm_api3('CustomGroup', 'get', array(
        'title' => "Second Last Name",
        'name' => "com_jasontdc_secondlastname_group",
      ));

      //if it doesn't exist, create it...
      if(count($result['values']) <= 0) {
        $apicall = array(
          'sequential' => 1,
          'title' => "Second Last Name",
          'extends' => array( "0" => "Individual"),
          'name' => "com_jasontdc_secondlastname_group",
          'style' => "Inline",
          'collapse_display' => 1,
          'is_active' => 1,
          'is_multiple' => 0,
          'collapse_adv_display' => 1,
          'is_reserved' => 0,
        );
        
        $result = civicrm_api3('CustomGroup', 'create', $apicall);
      }

      //see if the custom field already exists
      $result = civicrm_api3('CustomField', 'get', array(
        'sequential' => 1,
        'custom_group_id' => 'com_jasontdc_secondlastname_group',
        'name' => "com_jasontdc_secondlastname_field",
      ));

      //if it doesn't exist, create it...
      if(count($result['values']) <= 0) {
        $apicall = array(
          'sequential' => 1, 
          'option_type' => 0, 
          'label' => "Second Last Name", 
          'data_type' => "String", 
          'html_type' => "Text", 
          'name' => "com_jasontdc_secondlastname_field", 
          'custom_group_id' => 'com_jasontdc_secondlastname_group',
          'is_required' => 0, 
          'is_searchable' => 1, 
          'is_search_range' => 0, 
          'is_active' => 1, 
          'is_view' => 0, 
          'text_length' => 255
        );

        $result = civicrm_api3('CustomField', 'create', $apicall);
      }
    }
    catch (CiviCRM_API3_Exception $e) {
      // Handle error here.
      $errorMessage = $e->getMessage();
      $errorCode = $e->getErrorCode();
      $errorData = $e->getExtraParams();
      return array(
        'error' => $errorMessage,
        'error_code' => $errorCode,
        'error_data' => $errorData,
      );
    }
    catch (Exception $e) {
      // Handle error here.
      $errorMessage = $e->getMessage();
      $errorCode = $e->getCode();
      $errorData = $e->getTrace();
      return array(
        'error' => $errorMessage,
        'error_code' => $errorCode,
        'error_data' => $errorData,
      );
    }
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   *
  public function uninstall() {
   $this->executeSqlFile('sql/myuninstall.sql');
  }

  /**
   * Example: Run a simple query when a module is enabled.
   *
  public function enable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a simple query when a module is disabled.
   *
  public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a couple simple queries.
   *
   * @return TRUE on success
   * @throws Exception
   *
  public function upgrade_4200() {
    $this->ctx->log->info('Applying update 4200');
    CRM_Core_DAO::executeQuery('UPDATE foo SET bar = "whiz"');
    CRM_Core_DAO::executeQuery('DELETE FROM bang WHERE willy = wonka(2)');
    return TRUE;
  } // */


  /**
   * Example: Run an external SQL script.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4201() {
    $this->ctx->log->info('Applying update 4201');
    // this path is relative to the extension base dir
    $this->executeSqlFile('sql/upgrade_4201.sql');
    return TRUE;
  } // */


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4202() {
    $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

    $this->addTask(ts('Process first step'), 'processPart1', $arg1, $arg2);
    $this->addTask(ts('Process second step'), 'processPart2', $arg3, $arg4);
    $this->addTask(ts('Process second step'), 'processPart3', $arg5);
    return TRUE;
  }
  public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  public function processPart3($arg5) { sleep(10); return TRUE; }
  // */


  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4203() {
    $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

    $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
    for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
      $endId = $startId + self::BATCH_SIZE - 1;
      $title = ts('Upgrade Batch (%1 => %2)', array(
        1 => $startId,
        2 => $endId,
      ));
      $sql = '
        UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
        WHERE id BETWEEN %1 and %2
      ';
      $params = array(
        1 => array($startId, 'Integer'),
        2 => array($endId, 'Integer'),
      );
      $this->addTask($title, 'executeSql', $sql, $params);
    }
    return TRUE;
  } // */

}
