<?php

require_once 'omnipay.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function omnipay_civicrm_config(&$config) {
  _omnipay_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function omnipay_civicrm_xmlMenu(&$files) {
  _omnipay_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function omnipay_civicrm_install() {
  return _omnipay_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function omnipay_civicrm_uninstall() {
  return _omnipay_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function omnipay_civicrm_enable() {
  return _omnipay_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function omnipay_civicrm_disable() {
  return _omnipay_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function omnipay_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _omnipay_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function omnipay_civicrm_managed(&$entities) {
  return _omnipay_civix_civicrm_managed($entities);
}
class nz_co_fuzion_omnipay extends CRM_Core_Payment {
  const CHARSET = 'iso-8859-1';
  protected $_mode = null;
  protected static $_params = array();
  /**
   * We only need one instance of this object. So we use the singleton
   * pattern and cache the instance in this variable
   *
   * @var object
   * @static
  */
  private static $_singleton = null;

  /**
   * Constructor
   *
   * @param string $mode the mode of operation: live or test
   *
   * @return void
   */
  function __construct($mode, &$paymentProcessor) {
    $this->_mode = $mode;
    $this->_paymentProcessor = $paymentProcessor;
    $this->_processorName = ts('Omnipay');
  }
  /**
   * singleton function used to manage this object
   *
   * @param string $mode the mode of operation: live or test
   * @param $paymentProcessor
   * @param null $paymentForm
   * @param bool $force
   *
   * @return object
   */
  static function &singleton($mode, &$paymentProcessor, &$paymentForm = NULL, $force = false) {
    $processorName = $paymentProcessor['name'];
    if (self::$_singleton[$processorName] === null) {
      self::$_singleton[$processorName] = new nz_co_fuzion_omnipay($mode, $paymentProcessor);
    }
    return self::$_singleton[$processorName];
  }
  function checkConfig() {
  }
  function setExpressCheckOut(&$params) {
    CRM_Core_Error::fatal(ts('This function is not implemented'));
  }
  function getExpressCheckoutDetails($token) {
    CRM_Core_Error::fatal(ts('This function is not implemented'));
  }
  function doExpressCheckout(&$params) {
    CRM_Core_Error::fatal(ts('This function is not implemented'));
  }
  function doDirectPayment(&$params) {
    CRM_Core_Error::fatal(ts('This function is not implemented'));
  }

  /**
   * Main transaction function
   *
   * @param array $params  name value pair of contribution data
   *
   * @return void
   * @access public
   *
   */
  function doTransferCheckout(&$params, $component) {
    //doesn't look like these can actually be passed in....
    $config = CRM_Core_Config::singleton();
    //  $cancelURL = $this->getCancelURL($component);
    $url = $config->userFrameworkResourceURL . "extern/ipn.php?processor_name=nz.co.fuzion.omnipay";
    $component = strtolower($component);
    $paymentProcessorParams = $this->mapParamstoPaymentProcessorFields($params, $component);

    // Allow further manipulation of params via custom hooks
    CRM_Utils_Hook::alterPaymentProcessorParams($this, $params, $paymentProcessorParams);
    $processorURL = $this->_paymentProcessor['url_site'] . $this->buildPaymentProcessorString($paymentProcessorParams) . '/civi_url_for_api_update';
    CRM_Utils_System::redirect($processorURL);
  }

  /**
  * Get URL which the browser should be returned to if they cancel or are unsuccessful
  * @component string $omponent function is called from
  * @return string $cancelURL Fully qualified return URL
  * @todo Ideally this would be in the parent payment class
  */
  function getCancelURL($component) {
    $component = strtolower($component);
    if ($component != 'contribute' && $component != 'event') {
      CRM_Core_Error::fatal(ts('Component is invalid'));
    }
    if ($component == 'event') {
      $cancelURL = CRM_Utils_System::url('civicrm/event/register', "_qf_Confirm_display=true&qfKey={$params['qfKey']}", false, null, false);
    }
    else if ($component == 'contribute') {
      $cancelURL = CRM_Utils_System::url('civicrm/contribute/transact', "_qf_Confirm_display=true&qfKey={$params['qfKey']}", false, null, false);
    }
    return $cancelURL;
  }

  /**
  * map the name / value set required by the payment processor
  * @param array $params
  * @return array $processorParams array reflecting parameters required for payment processor
  */
  function mapParamstoPaymentProcessorFields($params, $component) {
    $processorParams = array(
      'contact_id' => $params['contactID'],
      'contribution_id' => $params['contributionID'],
      'amount' => $params['amount']
    );
    return $processorParams;
  }

  /*
  * Build string of name value pairs for submission to payment processor
  *
  * @params array $paymentProcessorParams
  * @return string $paymentProcessorString
  */
  function buildPaymentProcessorString($paymentProcessorParams) {
    $paymentProcessorString = implode('/', $paymentProcessorParams);
    return $paymentProcessorString;
  }
}