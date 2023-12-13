<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['sale_order_details/(:any)']= "sale_order/sale_order/sale_order_details/$1";
$route['add_sale_order']            = "sale_order/sale_order/sale_order_form";
$route['manage_sale_order']         = "sale_order/sale_order/manage_sale_order";
$route['to_delivery_note/(:any)']= "sale_order/sale_order/to_delivery_note/$1";

