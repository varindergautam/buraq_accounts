<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['performa_details/(:any)']= "performa/performa/performa_details/$1";
$route['add_performa']            = "performa/performa/performa_form";
$route['manage_performa']         = "performa/performa/manage_performa";
$route['quotation_download/(:any)']= "performa/performa/quotation_download/$1";