<?php

include('infoscore.php');

/*
 * Creating the Service Object 
 */
$attrs = array(
    'PartnerNo'   => '69999',
    'PartnerCode' => '69990600'
);

$infoscore = new Infoscore($attrs);

var_dump($infoscore);

$customer_attrs = array(
    'LastName'  => 'Lotter',
    'FirstName' => 'Ingrid',
    'Street'    => 'Kemptener Str.',
    'City'      => 'Lindau',
    'ZIP'       => '88131',
    'House'     => '70A',
    'Country'   => 'de'
);

$customer = new Customer($customer_attrs);

$response = $infoscore->checkES15($customer);

if ($response !== false && $response->isValid()) {
  
  var_dump($response->isGreen());
  
}


var_dump($customer->getRawData());

?>