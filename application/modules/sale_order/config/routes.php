<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['sale_order_details/(:any)']= "sale_order/sale_order/sale_order_details/$1";
$route['add_sale_order']            = "sale_order/sale_order/sale_order_form";
$route['manage_sale_order']         = "sale_order/sale_order/manage_sale_order";
$route['to_delivery_note/(:any)']= "sale_order/sale_order/to_delivery_note/$1";
$route['save_to_delivery']            = "sale_order/sale_order/save_to_delivery";
$route['to_sales/(:any)']= "sale_order/sale_order/to_sales/$1";
$route['save_to_sales']            = "sale_order/sale_order/save_to_sales";
$route['quotation_download/(:any)']= "sale_order/sale_order/quotation_download/$1";