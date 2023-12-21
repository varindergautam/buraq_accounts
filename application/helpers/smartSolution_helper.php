<?php
defined('BASEPATH') or exit('No direct script access allowed');

function vatTaxSetting()
{
    $ci = &get_instance();
    $ci->load->database();
    $ci->db->select('*');
    $ci->db->from('vat_tax_setting');
    $query   = $ci->db->get();
    return $query->row();
}

function getAllProducts()
{
    $ci = &get_instance();
    $ci->load->database();
    return $ci->db->select('*')->from('product_information')->get()->result();
}

function taxFields()
{
    $ci = &get_instance();
    $ci->load->database();
    return $ci->db->select('tax_name,default_value')
        ->from('tax_settings')
        ->get()
        ->result_array();
}
