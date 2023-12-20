<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 #------------------------------------    
    # Author: Bdtask Ltd
    # Author link: https://www.bdtask.com/
    # Dynamic style php file
    # Developed by :Isahaq
    #------------------------------------    

class Purchase_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
  
        $this->load->model(array('account/Accounts_model')); 
        
    }

 public function supplier_list(){
    $maxid = $this->Accounts_model->getMaxFieldNumber('id','acc_vaucher','Vtype','DV','VNo');
     $query = $this->db->select('*')
                ->from('supplier_information')
                ->where('status', '1')
                ->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
 }

 public function pmethod_dropdown(){
    $data = $this->db->select('*')
            ->from('acc_coa')
            ->where('PHeadName','Cash')
            ->or_where('PHeadName','Cash at Bank')
            ->get()
            ->result(); 

   $list[''] = 'Select Method';
   if (!empty($data)) {
    $list[11111] = 'Credit Purchase';
       foreach($data as $value)
           $list[$value->HeadCode] = $value->HeadName;
       return $list;
   } else {
       return false; 
   }
}
public function pmethod_dropdown_new(){
    $data = $this->db->select('*')
            ->from('acc_coa')
            ->where('PHeadName','Cash')
            ->or_where('PHeadName','Cash at Bank')
            ->get()
            ->result(); 

   $list[''] = 'Select Method';
   if (!empty($data)) {
    
       foreach($data as $value)
           $list[$value->HeadCode] = $value->HeadName;
       return $list;
   } else {
       return false; 
   }
}
public function getCustomerList_purchase($supplier_id){

    $query =$this->db->from('supplier_information a')
        ->where('a.supplier_id', $supplier_id);
    $query = $this->db->get();
    if ($query->num_rows() > 0) {

       
        return $query->result_array();
    }
    $result = $query->result();
    $no_of_credit_days = $result[0]->no_of_credit_days;


    return false;
}

     public function product_search_item($supplier_id, $product_name) {
      $query=$this->db->select('*')
                ->from('supplier_product a')
                ->join('product_information b','a.product_id = b.product_id')
//                ->where('a.supplier_id',$supplier_id)
                ->like('b.product_model', $product_name, 'both')
                ->or_where('a.supplier_id',$supplier_id)
                ->like('b.product_name', $product_name, 'both')
                ->group_by('a.product_id')
                ->order_by('b.product_name','asc')
                ->limit(15)
                ->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

        public function retrieve_purchase_editdata($purchase_id) {
        $this->db->select('a.*,
                        b.*,
                        a.id as dbpurs_id,
                        c.product_id,
                        c.product_name,
                        c.product_model,
                        d.supplier_id,
                        d.supplier_name'
        );
        $this->db->from('product_purchase a');
        $this->db->join('product_purchase_details b', 'b.purchase_id =a.id');
        $this->db->join('product_information c', 'c.product_id =b.product_id');
        $this->db->join('supplier_information d', 'd.supplier_id = a.supplier_id');
        $this->db->where('a.purchase_id', $purchase_id);
        $this->db->order_by('a.purchase_details', 'asc');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

        public function get_total_product($product_id, $supplier_id) {
        $this->db->select('SUM(a.quantity) as total_purchase,b.*');
        $this->db->from('product_purchase_details a');
        $this->db->join('supplier_product b', 'a.product_id=b.product_id');
        $this->db->where('a.product_id', $product_id);
        $this->db->where('b.supplier_id', $supplier_id);
        $total_purchase = $this->db->get()->row();

        $this->db->select('SUM(b.quantity) as total_sale');
        $this->db->from('invoice_details b');
        $this->db->where('b.product_id', $product_id);
        $total_sale = $this->db->get()->row();

        $this->db->select('a.*,b.*');
        $this->db->from('product_information a');
        $this->db->join('supplier_product b', 'a.product_id=b.product_id');
        $this->db->where(array('a.product_id' => $product_id, 'a.status' => 1));
//        $this->db->where('b.supplier_id', $supplier_id);

        $product_information = $this->db->get()->row();

        $available_quantity = ($total_purchase->total_purchase - $total_sale->total_sale);


        $data2 = array(
            'total_product'  => $available_quantity,
            'supplier_price' => $product_information->supplier_price,
//            'supplier_price' => $product_information->min_sale_price, denis new code
            'price'          => $product_information->price,
//            'price'          => $product_information->min_sale_price,
            'supplier_id'    => $product_information->supplier_id,
            'unit'           => $product_information->unit,
            'product_vat'    => $product_information->product_vat,
        );

        return $data2;
    }

     public function count_purchase() {
        $this->db->select('a.*,b.supplier_name');
        $this->db->from('product_purchase a');
        $this->db->join('supplier_information b', 'b.supplier_id = a.supplier_id');
        $this->db->order_by('a.purchase_date', 'desc');
        $query = $this->db->get();

        $last_query = $this->db->last_query();
        if ($query->num_rows() > 0) {
            return $query->num_rows();
        }
        return false;
    }

      public function getPurchaseList($postData=null){

         $response = array();
         $fromdate = $this->input->post('fromdate');
         $todate   = $this->input->post('todate');
         if(!empty($fromdate)){
            $datbetween = "(a.purchase_date BETWEEN '$fromdate' AND '$todate')";
         }else{
            $datbetween = "";
         }
         ## Read value
         $draw = $postData['draw'];
         $start = $postData['start'];
         $rowperpage = $postData['length']; // Rows display per page
         $columnIndex = $postData['order'][0]['column']; // Column index
         $columnName = $postData['columns'][$columnIndex]['data']; // Column name
         $columnSortOrder = $postData['order'][0]['dir']; // asc or desc
         $searchValue = $postData['search']['value']; // Search value

         ## Search 
         $searchQuery = "";
         if($searchValue != ''){
            $searchQuery = " (b.supplier_name like '%".$searchValue."%' or a.chalan_no like '%".$searchValue."%' or a.purchase_id like'%".$searchValue."%' or a.purchase_id like'%".$searchValue."%')";
         }

         ## Total number of records without filtering
        $this->db->select('count(*) as allcount');
        $this->db->from('product_purchase a');
        $this->db->join('supplier_information b', 'b.supplier_id = a.supplier_id','left');
          if(!empty($fromdate) && !empty($todate)){
             $this->db->where($datbetween);
         }
          if($searchValue != '')
          $this->db->where($searchQuery);
          
         $records = $this->db->get()->result();
         $totalRecords = $records[0]->allcount;

         ## Total number of record with filtering
         $this->db->select('count(*) as allcount');
        $this->db->from('product_purchase a');
        $this->db->join('supplier_information b', 'b.supplier_id = a.supplier_id','left');
         if(!empty($fromdate) && !empty($todate)){
             $this->db->where($datbetween);
         }
         if($searchValue != '')
            $this->db->where($searchQuery);
          
         $records = $this->db->get()->result();
         $totalRecordwithFilter = $records[0]->allcount;

         ## Fetch records
        $this->db->select('a.*,b.supplier_name,b.no_of_credit_days , DATEDIFF(CURDATE(), a.purchase_date) AS rem_time');
        $this->db->from('product_purchase a');
        $this->db->join('supplier_information b', 'b.supplier_id = a.supplier_id','left');
          if(!empty($fromdate) && !empty($todate)){
             $this->db->where($datbetween);
         }
         if($searchValue != '')
         $this->db->where($searchQuery);
       
         $this->db->order_by($columnName, $columnSortOrder);
         $this->db->limit($rowperpage, $start);

         $records = $this->db->get()->result();

         $data = array();
         $sl =1;
         foreach($records as $record ){

          $button = '';
          $base_url = base_url();
          $jsaction = "return confirm('Are You Sure ?')";

           $button .='  <a href="'.$base_url.'purchase_details/'.$record->purchase_id.'" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="left" title="'.display('purchase_details').'"><i class="fa fa-window-restore" aria-hidden="true"></i></a>';
              if($this->permission1->method('manage_purchase','update')->access()){
                $approve = $this->db->select('status,referenceNo')->from('acc_vaucher')->where('referenceNo', $record->purchase_id)->where('status', 1)->get()->num_rows();
                if ($approve == 0) {

                    $button .=' <a href="'.$base_url.'purchase_edit/'.$record->purchase_id.'" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="left" title="'. display('update').'"><i class="fa fa-pencil" aria-hidden="true"></i></a> ';
                }
             }

     

         $purchase_ids ='<a href="'.$base_url.'purchase_details/'.$record->purchase_id.'">'.$record->purchase_id.'</a>';
             $no_of_credit_days = $record->no_of_credit_days;
             $due_amount = $record->due_amount;

             if ($due_amount > 0) {

                 if ($no_of_credit_days == '') {
                     $rem_time = '';
                 } else {
                     $rem_time = $record->rem_time;
                 }
                 $days_overdue = $rem_time - $no_of_credit_days;
                 $rem_time_display = $rem_time > $no_of_credit_days ? '<span style="color: red;">' .  $days_overdue . '</span>' : $rem_time;
             }else{
                 $rem_time_display = '';
             }


             $data[] = array(
                'sl'               =>$sl,
                'chalan_no'        =>$record->chalan_no,
                'purchase_id'      =>$purchase_ids,
                'supplier_name'    =>$record->supplier_name,
                'no_of_credit_days'=>$record->no_of_credit_days,
                 'rem_time'               =>$rem_time_display,
                'purchase_date'    =>$record->purchase_date,
                'total_amount'     =>$record->grand_total_amount,
                'button'           =>$button,
                
            ); 
            $sl++;
         }

         ## Response
         $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecordwithFilter,
            "iTotalDisplayRecords" => $totalRecords,
            "aaData" => $data
         );

         return $response; 
    }

    public function purchase_details_data($purchase_id) {
        $this->db->select('a.*,b.*,c.*,e.purchase_details,d.product_id,d.product_name,d.product_model');
        $this->db->from('product_purchase a');
        $this->db->join('supplier_information b', 'b.supplier_id = a.supplier_id');
        $this->db->join('product_purchase_details c', 'c.purchase_id = a.id');
        $this->db->join('product_information d', 'd.product_id = c.product_id');
        $this->db->join('product_purchase e', 'e.purchase_id = c.purchase_id');
        $this->db->where('a.purchase_id', $purchase_id);
        $this->db->group_by('d.product_id');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return false;
    }

    /*invoice no generator*/
    public function number_generator() {
        $this->db->select_max('purchase_id', 'invoice_no');
        $query      = $this->db->get('product_purchase');
        $result     = $query->result_array();
        $invoice_no = $result[0]['invoice_no'];
        if ($invoice_no != '') {
            $invoice_no = $invoice_no + 1;
        } else {
            $invoice_no = 1;
        }
        return $invoice_no;
    }

    public function insert_purchase(){
         
        $purchase_id = $this->number_generator();
        $p_id        = $this->input->post('product_id',TRUE);
        $supplier_id = $this->input->post('supplier_id',TRUE);
        $supinfo     = $this->db->select('*')->from('supplier_information')->where('supplier_id',$supplier_id)->get()->row();
        $sup_head    = $supinfo->supplier_id.'-'.$supinfo->supplier_name;
        $sup_coa     = $this->db->select('*')->from('acc_coa')->where('HeadName',$sup_head)->get()->row();
        $receive_by = $this->session->userdata('id');
        $receive_date= date('Y-m-d');
        $createdate  = date('Y-m-d H:i:s');
        $paid_amount = $this->input->post('paid_amount',TRUE);
        $due_amount  = $this->input->post('due_amount',TRUE);
        $discount    = $this->input->post('discount',TRUE);
        $bank_id     = $this->input->post('bank_id',TRUE);

        $multipayamount = $this->input->post('pamount_by_method',TRUE);
        $multipaytype = $this->input->post('multipaytype',TRUE);
        
        $multiamnt = array_sum($multipayamount);

        if ($multiamnt == $paid_amount) {



            if(!empty($bank_id)){
                $bankname = $this->db->select('bank_name')->from('bank_add')->where('bank_id',$bank_id)->get()->row()->bank_name;

                $bankcoaid = $this->db->select('HeadCode')->from('acc_coa')->where('HeadName',$bankname)->get()->row()->HeadCode;
            }else{
                $bankcoaid = '';
            }


        //supplier & product id relation ship checker.
           /* for ($i = 0, $n = count($p_id); $i < $n; $i++) {
                $product_id = $p_id[$i];
                $value = $this->product_supplier_check($product_id, $supplier_id);
                if ($value == 0) {
                    $this->session->set_flashdata('error_message', display('product_and_supplier_did_not_match'));
                    redirect(base_url('add_purchase'));
                    exit();
                }
            }*/
        if ($multipaytype[0] == 0) {
            $is_credit = 1;
        }
        else {
            $is_credit = '';
        }

        $data = array(
            'purchase_id'        => $purchase_id,
            'chalan_no'          => $this->input->post('chalan_no',TRUE),
            'supplier_id'        => $this->input->post('supplier_id',TRUE),
            'grand_total_amount' => $this->input->post('grand_total_price',TRUE),
            'total_discount'     => $this->input->post('discount',TRUE),
            'invoice_discount'   => $this->input->post('total_discount',TRUE),
            'total_vat_amnt'     => $this->input->post('total_vat_amnt',TRUE),
            'purchase_date'      => $this->input->post('purchase_date',TRUE),
            'purchase_details'   => $this->input->post('purchase_details',TRUE),
            'paid_amount'        => $paid_amount,
            'due_amount'         => $due_amount,
            'status'             => 1,
            'bank_id'            => $this->input->post('bank_id',TRUE),
            'payment_type'       => 1,
            'is_credit'          => $is_credit,
        );

        $this->db->insert('product_purchase', $data);
        $purs_insert_id =  $this->db->insert_id();  
    
        $predefine_account  = $this->db->select('*')->from('acc_predefine_account')->get()->row();
        $Narration          = "Purchase Voucher";
        $Comment            = "Purchase Voucher for supplier";
        $COAID              = $predefine_account->purchaseCode;
        

        if($multipaytype && $multipayamount){

            if ($multipaytype[0] == 0) { 

                $amount_pay = $data['grand_total_amount'];
                $amnt_type = 'Credit';
                $reVID     = $predefine_account->supplierCode;
                $subcode   = $this->db->select('*')->from('acc_subcode')->where('referenceNo', $supplier_id)->where('subTypeId', 4)->get()->row()->id;
                $insrt_pay_amnt_vcher = $this->insert_purchase_debitvoucher($is_credit,$purchase_id,$COAID,$amnt_type,$amount_pay,$Narration,$Comment,$reVID,$subcode);

            }else {
                $amnt_type = 'Debit';
                for ($i=0; $i < count($multipaytype); $i++) {

                    $reVID = $multipaytype[$i];
                    $amount_pay = $multipayamount[$i];

                    $insrt_pay_amnt_vcher = $this->insert_purchase_debitvoucher($is_credit,$purchase_id,$COAID,$amnt_type,$amount_pay,$Narration,$Comment,$reVID);
                    
                }

                if ($data['due_amount'] > 0) {

                    $amount_pay2 = $data['due_amount'];
                    $amnt_type2 = 'Credit';
                    $reVID2     = $predefine_account->supplierCode;
                    $subcode2   = $this->db->select('*')->from('acc_subcode')->where('referenceNo', $supplier_id)->where('subTypeId', 4)->get()->row()->id;
                    $this->insert_purchase_debitvoucher(1,$purchase_id,$COAID,$amnt_type2,$amount_pay2,$Narration,$Comment,$reVID2,$subcode2);

                }
            }

        }
        
        $rate         = $this->input->post('product_rate',TRUE);
        $quantity     = $this->input->post('product_quantity',TRUE);
        $expiry_date  = $this->input->post('expiry_date',TRUE);
        $batch_no     = $this->input->post('batch_no',TRUE);
        $t_price      = $this->input->post('total_price',TRUE);
        $discountvalue= $this->input->post('discountvalue',TRUE);
        $vatpercent   = $this->input->post('vatpercent',TRUE);
        $vatvalue     = $this->input->post('vatvalue',TRUE);
        $discount_per = $this->input->post('discount_per',TRUE);
        
        for ($i = 0, $n = count($p_id); $i < $n; $i++) {
            $product_quantity = $quantity[$i];
            $product_rate     = $rate[$i];
            $product_id       = $p_id[$i];
            $total_price      = $t_price[$i];
            $ba_no            = $batch_no[$i];
            $exp_date         = $expiry_date[$i];
            $dis_per          = $discount_per[$i];
            $disval           = $discountvalue[$i];
            $vatper           = $vatpercent[$i];
            $vatval           = $vatvalue[$i];

            $data1 = array(
                'purchase_detail_id' => $this->generator(15),
                'purchase_id'        => $purs_insert_id,
                'product_id'         => $product_id,
                'quantity'           => $product_quantity,
                'rate'               => $product_rate,
                'batch_id'           => $ba_no,
                'expiry_date'        => $exp_date,
                'total_amount'       => $total_price,
                'discount'           => $dis_per,
                'discount_amnt'      => $disval,
                'vat_amnt_per'       => $vatper,
                'vat_amnt'           => $vatval,
                'status'             => 1
            );

            $product_price = array(

                'supplier_price' => $product_rate
            );
            
            if (!empty($quantity)) {
                $this->db->insert('product_purchase_details', $data1);
                $this->db->where('product_id', $product_id)->update('supplier_product', $product_price);
            }
            
        }

        $setting_data = $this->db->select('is_autoapprove_v')->from('web_setting')->where('setting_id', 1)->get()->result_array();
        if ($setting_data[0]['is_autoapprove_v'] == 1) {	
            
            
            $new = $this->autoapprove($purchase_id);
        }

        return 1;

        
        }else {

            return 2;
            
        }
    }
    

    public function product_supplier_check($product_id, $supplier_id) {
        $this->db->select('*');
        $this->db->from('supplier_product');
        $this->db->where('product_id', $product_id);
        $this->db->where('supplier_id', $supplier_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return true;
        }
        return 0;
    }

    // insert purchase debitvoucher
    public function insert_purchase_debitvoucher($is_credit = null,$purchase_id = null,$dbtid = null,$amnt_type = null,$amnt = null,$Narration = null,$Comment = null,$reVID = null,$subcode = null){  

        
        $fyear = financial_year();
        $VDate = date('Y-m-d');
        $CreateBy=$this->session->userdata('id');
        $createdate=date('Y-m-d H:i:s');
        if ($is_credit == 1) {
            $maxid = $this->Accounts_model->getMaxFieldNumber('id','acc_vaucher','Vtype','JV','VNo');             
            $vaucherNo = "JV-". ($maxid +1);

            $debitinsert = array(
                'fyear'          =>  $fyear,
                'VNo'            =>  $vaucherNo,
                'Vtype'          =>  'JV',
                'referenceNo'    =>  $purchase_id,
                'VDate'          =>  $VDate,
                'COAID'          =>  $reVID,    
                'Narration'      =>  $Narration,     
                'ledgerComment'  =>  $Comment,   
                'RevCodde'       =>  $dbtid,    
                'subType'        =>  4,    
                'subCode'        =>  $subcode,    
                'isApproved'     =>  0,                      
                'CreateBy'       =>  $CreateBy,
                'CreateDate'     =>  $createdate,      
                'status'         =>  0,      
            );

            
        }else {
            
            $maxid = $this->Accounts_model->getMaxFieldNumber('id','acc_vaucher','Vtype','DV','VNo');             
            $vaucherNo = "DV-". ($maxid +1);

            $debitinsert = array(
                'fyear'          =>  $fyear,
                'VNo'            =>  $vaucherNo,
                'Vtype'          =>  'DV',
                'referenceNo'    =>  $purchase_id,
                'VDate'          =>  $VDate,
                'COAID'          =>  $dbtid,     
                'Narration'      =>  $Narration,     
                'ledgerComment'  =>  $Comment,   
                'RevCodde'       =>  $reVID,    
                'isApproved'     =>  0,                      
                'CreateBy'       => $CreateBy,
                'CreateDate'     => $createdate,      
                'status'         => 0,      
            );
        }
        if($amnt_type == 'Debit'){
            
            $debitinsert['Debit']  = $amnt;
            $debitinsert['Credit'] =  0.00;    
        }else{

            $debitinsert['Debit']  = 0.00;
            $debitinsert['Credit'] =  $amnt; 
        }
        

        $this->db->insert('acc_vaucher',$debitinsert);
       
	    return true;
	}

    public function autoapprove($purchase_id){

        $vouchers = $this->db->select('referenceNo, VNo')->from('acc_vaucher')->where('referenceNo',$purchase_id)->where('status',0)->get()->result();
        foreach ($vouchers as $value) {
            # code...
            $this->Accounts_model->approved_vaucher($value->VNo, 'active');
        }
        return true;
        
    }

    public function generator($lenth)
    {
        $number=array("A","B","C","D","E","F","G","H","I","J","K","L","N","M","O","P","Q","R","S","U","V","T","W","X","Y","Z","1","2","3","4","5","6","7","8","9","0");
    
        for($i=0; $i<$lenth; $i++)
        {
            $rand_value=rand(0,34);
            $rand_number=$number["$rand_value"];
        
            if(empty($con))
            { 
            $con=$rand_number;
            }
            else
            {
            $con="$con"."$rand_number";}
        }
        return $con;
    }

}

