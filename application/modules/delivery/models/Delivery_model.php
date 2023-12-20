<?php
defined('BASEPATH') or exit('No direct script access allowed');
#------------------------------------    
# Author: Bdtask Ltd
# Author link: https://www.bdtask.com/
# Dynamic style php file
# Developed by :Isahaq
#------------------------------------    

class Delivery_model extends CI_Model
{
    public function delivery($quot_id)
    {
        return $this->db->select('*')
            ->from('delivery')
            ->where('quotation_id', $quot_id)
            ->get()
            ->result_array();
    }

    public function delivery_quot_number_generator()
    {
        $this->db->select_max('id', 'id');
        $query   = $this->db->get('delivery');
        $result  = $query->result_array();
        $quot_no = $result[0]['id'];
        if ($quot_no != '') {
            $quot_no = $quot_no + 1;
        } else {
            $quot_no = 1;
        }
        return 'DN-' . $quot_no;
    }

    public function quot_main_edit($quot_id)
    {
        return $this->db->select('*')
            ->from('delivery')
            ->where('quotation_id', $quot_id)
            ->get()
            ->result_array();
    }

    public function quot_product_detail($quot_id)
    {

        return $this->db->select('a.*,b.*')
            ->from('deli_products_used a')
            ->join('product_information b', 'a.product_id=b.product_id', 'left')
            ->where('a.quot_id', $quot_id)
            ->order_by('a.id', 'asc')
            ->get()
            ->result_array();
    }

    public function quot_service_detail($quot_id)
    {
        $result = $this->db->select('a.*,b.*')
            ->from('delivery_service_used a')
            ->join('product_service b', 'a.service_id=b.service_id')
            ->where('a.quot_id', $quot_id)
            ->order_by('a.id', 'asc')
            ->get()
            ->result_array();
        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    public function customerinfo($customer_id)
    {
        return $this->db->select('*')
            ->from('customer_information')
            ->where('customer_id', $customer_id)
            ->get()
            ->result_array();
    }

    public function itemtaxdetails($quot_no)
    {
        $taxdetector = 'item' . $quot_no;
        return $this->db->select('*')
            ->from('delivery_taxinfo')
            ->where('relation_id', $taxdetector)
            ->get()
            ->result_array();
    }

    public function servicetaxdetails($quot_no)
    {
        $taxdetector = 'serv' . $quot_no;
        return $this->db->select('*')
            ->from('delivery_taxinfo')
            ->where('relation_id', $taxdetector)
            ->get()
            ->result_array();
    }
}
