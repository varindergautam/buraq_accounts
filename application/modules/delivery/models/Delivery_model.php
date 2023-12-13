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
}
