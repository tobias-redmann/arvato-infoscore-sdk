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
    'LastName'  => 'Redmann',
    'FirstName' => 'Tobias',
    'Street'    => 'Hocksteinweg',
    'City'      => 'Berlin',
    'ZIP'       => '14165',
    'Country'   => 'de'
);

$customer = new Customer($customer_attrs);

var_dump($infoscore->checkES15($customer));

?>