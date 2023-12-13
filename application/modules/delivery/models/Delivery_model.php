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
        $this->db->select_max('quot_no', 'quot_no');
        $query   = $this->db->get('delivery');
        $result  = $query->result_array();
        $quot_no = $result[0]['quot_no'];
        if ($quot_no != '') {
            $quot_no = $quot_no + 1;
        } else {
            $quot_no = 5000;
        }
        return $quot_no;
    }
}
