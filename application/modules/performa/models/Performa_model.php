<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Performa_model extends CI_Model
{
    public function performa_quot_number_generator()
    {
        $this->db->select_max('id', 'id');
        $query   = $this->db->get('performa');
        $result  = $query->result_array();
        $quot_no = $result[0]['id'];
        if ($quot_no != '') {
            $quot_no = $quot_no + 1;
        } else {
            $quot_no = 1;
        }
        return 'PI-' . $quot_no;
    }

    public function list($offset, $limit)
    {
        $this->db->select('a.*, b.customer_name');
        $this->db->from('performa a');
        $this->db->join('customer_information b', 'b.customer_id = a.customer_id');
        $this->db->order_by('a.id', 'desc');
        $this->db->limit($offset, $limit);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        }
        return false;
    }

    public function performa_entry($data)
    {

        $this->db->select('*');
        $this->db->from('performa');
        $this->db->where('quot_no', $data['quot_no']);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return FALSE;
        } else {
            $this->db->insert('performa', $data);
            return TRUE;
        }
    }

    public function performa_main_edit($quot_id)
    {
        return $this->db->select('*')
            ->from('performa')
            ->where('quotation_id', $quot_id)
            ->get()
            ->result_array();
    }

    public function performa_product_detail($quot_id)
    {

        return $this->db->select('a.*,b.*')
            ->from('performa_products_used a')
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

    public function performa_service_detail($quot_id)
    {
        $result = $this->db->select('a.*,b.*')
            ->from('performa_service_used a')
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

    public function itemTaxDetails($quot_no)
    {
        $taxdetector = 'item' . $quot_no;
        return $this->db->select('*')
            ->from('performa_taxinfo')
            ->where('relation_id', $taxdetector)
            ->get()
            ->result_array();
    }

    public function serviceTaxDetails($quot_no)
    {
        $taxdetector = 'serv' . $quot_no;
        return $this->db->select('*')
            ->from('performa_taxinfo')
            ->where('relation_id', $taxdetector)
            ->get()
            ->result_array();
    }

    public function update($data)
    {
        $this->db->select('*');
        $this->db->from('performa');
        $this->db->where('quotation_id', $data['quotation_id']);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $this->db->where('quotation_id', $data['quotation_id']);
            $this->db->update('performa', $data);
            return TRUE;
        } else {
            return FALSE;
        }
    }
}
