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
  
  private function buildFields($serviceName, $customer, $serviceAttributes) {
    
    $allowed_customer_params = array();
    $allowed_service_params = array();
    
    switch($serviceName) {
    
      case 'ES0015':
      
        $allowed_customer_params = array('CustomerNo', 'Title', 'LastName', 'FirstName', 'DateOfBirth', 'Street', 'House', 'City', 'Country', 'ZIP');

        $allowed_service_params  = array('PartnerNo', 'PartnerCode', 'UserName', 'UserCode');
        
        break;
    
    }
    
    $url = '';
    
    $url .= 'Service='. $serviceName;
    
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
  
  
  private function requestService($fields) {
    
    if ($this->isSandbox()) {
      
      $url .= $this->sandbox_url;
      
    } else {
      
      $url .= $this->production_url;
      
    }
    
    $ch = curl_init();
    
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    
    // cert is not valid
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    
    if ($result !== false) {
      
      $raw_data = array();
      
      parse_str($result, $raw_data);
      
      curl_close($ch);
      
      return $raw_data;
      
    } else {
      
      $this->errors[] = 'Curl Error:  ' . curl_error($ch);
      
      curl_close($ch);
      
      return false;
      
    }
    
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
      
      $fields = $this->buildFields('ES0015', $customer, $service_attributes);
      
      $raw_data_response = $this->requestService($fields);
      
      if ($raw_data_response === false) {
        
        return false;
        
      }
      
      return new Response('ES0015', $customer, $raw_data_response);
      
    } else {
      
      return false;
      
    }
    
  }
  
  
}

class Response {
  
  private $serviceName = null;
  private $raw_data = null;
  
  function __construct($serviceName, $customer ,$raw_data) {
    
    $this->serviceName = $serviceName;
    $this->raw_data = $raw_data;
    
  }
  
  /**
   * Is the response a valid response?
   * 
   * @return boolean
   */
  function isValid() {
    
    if (isset($this->raw_data['RC']) && $this->raw_data['RC'] == '0') {
      
      return true;
      
    } else {
      
      return false;
      
    }
    
  }
  
  
  function isGreen() {
    
    if (isset($this->raw_data['eScoreValue'])) {
      
      if ( $this->raw_data['eScoreValue'] == 'G' ) {
        
        return true;
        
      } else {
        
        return false;
        
      }
      
      
    } else {
      
      return null;
      
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
  
  public function getRawData() {
    
    return get_object_vars($this);
    
  }
  
  
}




?>