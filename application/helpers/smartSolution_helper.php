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

function setting_data()
{
    $ci = &get_instance();
    $ci->load->database();
    return $ci->db->select('*')
        ->from('web_setting')
        ->get()
        ->result_array();
}

function retrieve_company()
{
    $ci = &get_instance();
    $ci->load->database();
    $ci->db->select('*');
    $ci->db->from('company_information');
    $ci->db->limit('1');
    $query = $ci->db->get();
    if ($query->num_rows() > 0) {
        return $query->result_array();
    }
    return false;
}

function pmethod_dropdown()
{
    $ci = &get_instance();
    $ci->load->database();
    $data = $ci->db->select('*')
        ->from('acc_coa')
        ->where('PHeadName', 'Cash')
        ->or_where('PHeadName', 'Cash at Bank')
        ->get()
        ->result();

    $list[''] = 'Select Method';
    if (!empty($data)) {
        $list[0] = 'Credit Sale';
        foreach ($data as $value)
            $list[$value->HeadCode] = $value->HeadName;
        return $list;
    } else {
        return false;
    }
}

function pmethod_dropdown_new()
{
    $ci = &get_instance();
    $ci->load->database();
    $data = $ci->db->select('*')
        ->from('acc_coa')
        ->where('PHeadName', 'Cash')
        ->or_where('PHeadName', 'Cash at Bank')
        ->get()
        ->result();

    $list[''] = 'Select Method';
    if (!empty($data)) {

        foreach ($data as $value)
            $list[$value->HeadCode] = $value->HeadName;
        return $list;
    } else {
        return false;
    }
}

function get_allcustomer()
{
    $ci = &get_instance();
    $ci->load->database();
    return $ci->db->select('*')
        ->from('customer_information')
        ->order_by('customer_name', 'asc')
        ->get()
        ->result_array();
}
