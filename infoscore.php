<?php


class Infoscore{
  
  
  private $use_sandbox    = true;
  
  private $PartnerNo      = null;
  private $PartnerCode    = null;
  
  private $UserName       = null;
  private $UserCode       = null;
  
  
  private $sandbox_url    = 'https://193.101.24.30:2443';
  private $production_url = 'https://193.101.24.33:2443';
  
  private $errors         = array();
  

  function __construct($attrs) {
    
    foreach($attrs as $attr => $att_value) {
      
      $this->{$attr} = $att_value;
      
    }
    
  }
  
  function useSandbox($use_sandbox) {
    
    $this->use_sandbox = $use_sandbox;
    
  }
  
  function isSandbox() {
    
    return $this->use_sandbox;
    
  }
  
  
  private function checkGeneralServiceParameters($serviceName) {
    
    $required_parameters = array();
    
    switch($serviceName) {
      
      case 'ES0015';
      
        $required_parameters = array('PartnerNo', 'PartnerCode');
        
      break;
      
    }
    
    $missing_fields = array();
    
    foreach($required_parameters as $required_parameter) {
      
      if ($this->{$required_parameter} == null) {
        
        $missing_fields[] = $required_parameter;
        
      }
      
    }
    
    if (count($missing_fields) > 0) {
      
      $this->errors[] = 'ServiceParameters missing: '. implode(', ', $missing_fields);
      
      return false;
      
    } else {
      
      return true;
      
    }
    
    
  }
  
  private function checkCustomerParameters($serviceName, $customer) {
    
    $required_parameters = array();
    
    switch($serviceName) {
      
      case 'ES0015':
        
        $required_parameters = array('LastName', 'FirstName', 'Street', 'Country', 'ZIP');
        
        break;
      
    }
    
    $missing_fields = array();
    
    foreach($required_parameters as $required_parameter) {
      
      $function_name = 'get'. $required_parameter;
      
      $current_value = $customer->{$function_name}();
      
      if ($current_value == null) {
        
        $missing_fields[] = $required_parameter;
        
      }
      
    }
    
    if(count($missing_fields) > 0) {
    
      $this->errors[] = 'CustomerParameters missing: ' . implode(', ',$missing_fields);
      
      return false;
      
    } else {
      
      return true;
      
    }
    
    
  }
  
  private function checkServiceParameters($serviceName, $params) {
    
    $required_params = array();
    
    switch($serviceName) {
      
      case 'ES0015':
        
        $required_params = array('RequestReason','RequestInformaScore','RequestEScoreValue');
        
        break;
      
    }
    
    $missing_fields = array();
    
    foreach($required_params as $required_param) {
      
      if (!isset($params[$required_param]) || $params[$required_param] == null) {
        
        $missing_fields[] = $required_param;
        
      }
      
    }
    
    if (count($missing_fields) > 0) {
      
      $this->errors[] = 'ServiceParameters missing: ' . implode(', ', $missing_fields );
      
      return false;
      
    } else {
      
      return true;
      
    }
    
  }
  
  private function getRequestUrl($serviceName, $customer, $serviceAttributes) {
    
    $allowed_customer_params = array();
    $allowed_service_params = array();
    
    switch($serviceName) {
    
      case 'ES0015':
      
        $allowed_customer_params = array('CustomerNo', 'Title', 'LastName', 'FirstName', 'DateOfBirth', 'Street', 'House', 'City', 'Country', 'ZIP');

        $allowed_service_params  = array('PartnerNo', 'PartnerCode', 'UserName', 'UserCode');
        
        break;
    
    }
    
    $url = '';
    
    if ($this->isSandbox()) {
      
      $url .= $this->sandbox_url;
      
    } else {
      
      $url .= $this->production_url;
      
    }
    
    $url .= '?Service='. $serviceName;
    
    foreach($allowed_service_params as $asp) {
      
      $service_value = $this->{$asp};
      
      if ($service_value != null && $service_value != '') {
        
        $url .= '&'. $asp . '=' . urlencode($service_value);
        
      }
      
    }
    
    
    foreach($allowed_customer_params as $acp) {
      
      $function_name = 'get'. $acp;
      
      $current_value = $customer->{$function_name}();
      
      if ($current_value != null && $current_value != '') {
        
        $url .= '&'. $acp . '='. urlencode($current_value);
        
      }
      
    }
    
    foreach($serviceAttributes as $san => $sav) {
      
      $url .= '&'. $san . '=' . urlencode($sav);
      
    }
    
    
    return $url;
    
    
    
    
  }
  
  
  public function checkES15($customer, $attrs = array()) {
    
    $service_attributes = array();
    
    $service_attributes['RequestReason']        = 'ABK';
    $service_attributes['RequestInformaScore']  = 'Y';
    $service_attributes['RequestEScoreValue']   = 'Y';
    
    $service_attributes_ok = $this->checkServiceParameters('ES0015',$service_attributes);    
    
    // do a service pre check
    $service_params_ok = $this->checkGeneralServiceParameters('ES0015');
    
    // do customer pre check
    $customer_params_ok = $this->checkCustomerParameters('ES0015', $customer);
    
    if ($service_params_ok === true && $customer_params_ok === true && $service_attributes_ok) {
      
      $url = $this->getRequestUrl('ES0015', $customer, $service_attributes);
      
      $response = file_get_contents($url);
      
      var_dump($response);
      
      
    } else {
      
      var_dump($this->errors);
      
    }
    
  }
  
  
}


class Customer {
  
  private $CustomerNo   = null;
  
  private $Title        = null; 
  private $LastName     = null;
  private $FirstName    = null;
  private $DateOfBirth  = null;
  
  private $Street       = null;
  private $House        = null;
  private $City         = null;
  private $Country      = null;
  private $ZIP          = null;
  
  function __construct($attrs) {
    
    foreach($attrs as $attr => $att_value) {
      
      $this->{$attr} = $att_value;
      
    }
    
  }
  
  public function __call($functionName, $attributes) {
    
    if(substr($functionName, 0, 3) == 'get') {
      
        $varname = substr($functionName, 3);
    
    } else {
    
      throw new Exception('Bad method.', 500);
    
      
    }
    
    if(property_exists('Customer', $varname)) {
    
      return $this->$varname;
    
    } else {
      
        throw new Exception('Property does not exist: '.$varname, 500);
    
    }
  
    
  }
  
  
  
}




?>