<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Sale_order_model extends CI_Model
{
    public function sale_order_quot_number_generator()
    {
        $this->db->select_max('quot_no', 'quot_no');
        $query   = $this->db->get('sale_orders');
        $result  = $query->result_array();
        $quot_no = $result[0]['quot_no'];
        if ($quot_no != '') {
            $quot_no = $quot_no + 1;
        } else {
            $quot_no = 2000;
        }
        return $quot_no;
    }

    public function sale_order_entry($data)
    {

        $this->db->select('*');
        $this->db->from('sale_orders');
        $this->db->where('quot_no', $data['quot_no']);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return FALSE;
        } else {
            $this->db->insert('sale_orders', $data);
            return TRUE;
        }
    }

    public function sale_order_main_edit($quot_id)
    {
        return $this->db->select('*')
            ->from('sale_orders')
            ->where('quotation_id', $quot_id)
            ->get()
            ->result_array();
    }

    public function sale_order_product_detail($quot_id)
    {

        return $this->db->select('a.*,b.*')
            ->from('sale_order_products_used a')
            ->join('product_information b', 'a.product_id=b.product_id', 'left')
            ->where('a.quot_id', $quot_id)
            ->order_by('a.id', 'asc')
            ->get()
            ->result_array();
    }

    public function customerinfo($customer_id)
    {
        return $this->db->select('*')
            ->from('customer_information')
            ->where('customer_id', $customer_id)
            ->get()
            ->result_array();
    }

    public function sale_order_service_detail($quot_id)
    {
        $result = $this->db->select('a.*,b.*')
            ->from('sale_order_service_used a')
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
}
