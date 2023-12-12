<?php
defined('BASEPATH') or exit('No direct script access allowed');
  

class Sale_order_model extends CI_Model
{
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
}
