<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['purchase_order_details/(:any)']= "purchase_order/purchase_order/purchase_order_details/$1";
$route['add_purchase_order']            = "purchase_order/purchase_order/purchase_order_form";
$route['manage_purchase_order']         = "purchase_order/purchase_order/manage_purchase_order";
$route['quotation_download/(:any)']= "purchase_order/purchase_order/quotation_download/$1";
$route['edit_purchase_order/(:any)'] = 'purchase_order/purchase_order/edit_purchase_order/$1';