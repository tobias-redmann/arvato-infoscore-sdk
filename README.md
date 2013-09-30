arvato-infoscore-sdk
====================

PHP SDK for Arvato Infoscore Service

## Use the SDK

### Create an Infoscore Object

	$credentials = array(
					'PartnerNo' => '69999', 
					'PartnerCode' => '69990600'
					);

	$infoscore = new Infoscore($credentials);
	

### Validate a customer

	$cust_attrs = 	array(
    					'LastName'  => 'Lotter',
    					'FirstName' => 'Ingrid',
    					'Street'    => 'Kemptener Str.',
    					'City'      => 'Lindau',
    					'ZIP'       => '88131',
    					'House'     => '70A',
    					'Country'   => 'de'
					);
					
	$customer = new Customer($cust_attrs);
	
	$response = $infoscore->checkES15($customer);
	
	if ($response === false) {
	
		var_dump($infoscore->errors);
	
	} else {
	
		if ($response->isValid()) {
		
			
		
		}
	
	}