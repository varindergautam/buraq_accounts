<?php
defined('BASEPATH') or exit('No direct script access allowed');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

ini_set('error_reporting', E_ALL & ~E_DEPRECATED);

class Sale_order extends MX_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'service/service_model', 'account/Accounts_model', 'quotation/quotation_model', 'sale_order_model', 'delivery/delivery_model'
        ));
        if (!$this->session->userdata('isLogIn'))
            redirect('login');
    }

    public function sale_order_details($quot_id = null)
    {
        $currency_details     = $this->quotation_model->setting_data();
        $data['currency_details'] = $currency_details;
        $data['title']        = 'Sale Order Details';
        $data['quot_main']    = $this->sale_order_model->sale_order_main_edit($quot_id);
        $data['quot_product'] = $this->sale_order_model->sale_order_product_detail($quot_id);
        $data['quot_service'] = $this->sale_order_model->sale_order_service_detail($quot_id);

        $data['customer_info'] = $this->sale_order_model->customerinfo($data['quot_main'][0]['customer_id']);
        $data['discount_type'] = $currency_details[0]['discount_type'];
        $data['company_info'] = $this->quotation_model->retrieve_company();
        $data['module']        = "sale_order";
        $data['page']          = "sale_order_details";
        echo modules::run('template/layout', $data);
    }

    public function manage_sale_order()
    {
        $data['title']         = 'Sale Orders';
        $config["base_url"]    = base_url('manage_sale_order');
        $config["total_rows"]  = $this->db->count_all('sale_orders');
        $config["per_page"]    = 20;
        $config["uri_segment"] = 2;
        $config["last_link"]   = "Last";
        $config["first_link"]  = "First";
        $config['next_link']   = 'Next';
        $config['prev_link']   = 'Prev';
        $config['full_tag_open'] = "<ul class='pagination col-xs pull-right'>";
        $config['full_tag_close'] = "</ul>";
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = "<li class='disabled'><li class='active'><a href='#'>";
        $config['cur_tag_close'] = "<span class='sr-only'></span></a></li>";
        $config['next_tag_open'] = "<li>";
        $config['next_tag_close'] = "</li>";
        $config['prev_tag_open'] = "<li>";
        $config['prev_tagl_close'] = "</li>";
        $config['first_tag_open'] = "<li>";
        $config['first_tagl_close'] = "</li>";
        $config['last_tag_open'] = "<li>";
        $config['last_tagl_close'] = "</li>";
        $this->pagination->initialize($config);
        $page = ($this->uri->segment(2)) ? $this->uri->segment(2) : 0;
        $data["links"]  = $this->pagination->create_links();
        $data['module'] = "sale_order";
        $data['quotation_list'] = $this->sale_order_model->list($config["per_page"], $page);
        $data['page']   = "sale_order_list";
        echo Modules::run('template/layout', $data);
    }

    public function to_delivery_note($quot_id = null)
    {
        $vat_tax_info   = $this->quotation_model->vat_tax_setting();

        $data['quot_main']       = $this->sale_order_model->sale_order_main_edit($quot_id);

        if ($data['quot_main'][0]['is_dynamic'] == 1) {
            if ($data['quot_main'][0]['is_dynamic'] != $vat_tax_info->dynamic_tax) {

                $this->session->set_flashdata('exception', 'VAT and TAX are set globally, which is not the same as VAT and TAX on this invoice. (which was configured when the invoice was created). It is not editable.');
                redirect("manage_quotation");
            }
        } elseif ($data['quot_main'][0]['is_fixed'] == 1) {
            if ($data['quot_main'][0]['is_fixed'] != $vat_tax_info->fixed_tax) {

                $this->session->set_flashdata('exception', 'VAT and TAX are set globally, which is not the same as VAT and TAX on this invoice. (which was configured when the invoice was created). It is not editable.');
                redirect("manage_quotation");
            }
        }
        $taxfield = $this->db->select('tax_name,default_value')
            ->from('tax_settings')
            ->get()
            ->result_array();

        $tablecolumn = $this->db->list_fields('tax_collection');
        $num_column = count($tablecolumn) - 4;
        $currency_details        = $this->quotation_model->setting_data();
        $data['currency_details'] = $currency_details;
        $data['title']           = display('quotation_to_delivery');
        $data['quot_product']    = $this->sale_order_model->sale_order_product_detail($quot_id);
        $data['quot_service']    = $this->sale_order_model->sale_order_service_detail($quot_id);
        $data['customer_info']   = $this->sale_order_model->customerinfo($data['quot_main'][0]['customer_id']);
        $data['itemtaxin']       = $this->quotation_model->itemtaxdetails($data['quot_main'][0]['quot_no']);
        $data['servicetaxin']    = $this->quotation_model->servicetaxdetails($data['quot_main'][0]['quot_no']);
        $data['taxes']           = $taxfield;
        $data['taxnumber']       = $num_column;
        $data['customers']       = $this->quotation_model->get_allcustomer();
        $data['get_productlist'] = $this->quotation_model->get_allproduct();
        $data['all_pmethod']     = $this->quotation_model->pmethod_dropdown();
        $data['module']          = "quotation";
        $vatortax              = $this->quotation_model->vat_tax_setting();
        if ($vatortax->fixed_tax == 1) {

            $data['page']            = "quotation_to_delivery";
        }
        if ($vatortax->dynamic_tax == 1) {
            $data['page']          = "quotation_to_delivery_dynamic";
        }
        echo modules::run('template/layout', $data);
    }

    public function save_to_delivery() 
    {
        $this->form_validation->set_rules('customer_id', display('customer_name'), 'required|max_length[50]');
        $this->form_validation->set_rules('qdate', display('quotation_date'), 'required|max_length[50]');
        $this->form_validation->set_rules('expiry_date', display('expiry_date'), 'required|max_length[50]');
        if ($this->form_validation->run()) {

            $quot_id     = $this->delivery_model->delivery_quot_number_generator();
            $quotation_id = $this->input->post('quotation_id', TRUE);

            $tablecolumn = $this->db->list_fields('delivery_taxinfo');
            $num_column  = count($tablecolumn) - 4;
            $fixordyn    = $this->db->select('*')->from('vat_tax_setting')->get()->row();
            $is_fixed    = '';
            $is_dynamic  = '';

            if ($fixordyn->fixed_tax == 1) {
                $is_fixed   = 1;
                $is_dynamic = 0;
            } elseif ($fixordyn->dynamic_tax == 1) {
                $is_fixed   = 0;
                $is_dynamic = 1;
            }
            $customershow = 0;
            // $status = 1;
            // $deliver_status = 1;
            // $sale_order_status = 1;
            $data = array(
                'quotation_id'        => $quot_id,
                'customer_id'         => $this->input->post('customer_id', TRUE),
                'quotdate'            => $this->input->post('qdate', TRUE),
                'expire_date'         => $this->input->post('expiry_date', TRUE),
                'item_total_amount'   => $this->input->post('grand_total_price', TRUE),
                'item_total_dicount'  => $this->input->post('total_discount', TRUE),
                'item_total_vat'      => $this->input->post('total_vat_amnt', TRUE),
                'item_total_tax'      => $this->input->post('total_tax', TRUE),
                'service_total_amount' => $this->input->post('grand_total_service_amount', TRUE),
                'service_total_discount' => $this->input->post('totalServiceDicount', TRUE),
                'service_total_vat'   => $this->input->post('service_total_vat_amnt', TRUE),
                'service_total_tax'   => $this->input->post('total_service_tax', TRUE),
                'quot_dis_item'       => $this->input->post('invoice_discount', TRUE),
                'quot_dis_service'    => $this->input->post('service_discount', TRUE),
                'quot_no'             => $quot_id,
                'create_by'           => $this->session->userdata('id'),
                'quot_description'    => $this->input->post('details', TRUE),
                // 'status'              => $status,
                // 'deliver_status'              => $deliver_status,
                // 'sale_order_status'              => $sale_order_status,
                'is_fixed'            =>  $is_fixed,
                'is_dynamic'          =>  $is_dynamic,
                'quotation_main_id'     => $quotation_id,
            );


            $result = $this->quotation_model->delivery_entry($data);

            $quotdata = array('delivery_status'  => 2);
            $this->db->where('quotation_id', $quotation_id);
            $this->db->update('quotation', $quotdata);

            if ($result == TRUE) {
                // Used Item Details Part
                $item         = $this->input->post('product_id', TRUE);
                $serial       = $this->input->post('serial_no', TRUE);
                $descrp       = $this->input->post('desc', TRUE);
                $item_rate    = $this->input->post('product_rate', TRUE);
                $item_supp_rate = $this->input->post('supplier_price', TRUE);
                $item_qty     = $this->input->post('product_quantity', TRUE);
                $item_dis_per = $this->input->post('discount', TRUE);
                $item_total_discount = $this->input->post('discountvalue', TRUE);
                $vat_per      = $this->input->post('vatpercent', TRUE);
                $vat_value    = $this->input->post('vatvalue', TRUE);
                $item_tax     = $this->input->post('tax', TRUE);
                $totalp       =  $this->input->post('total_price', TRUE);
                for ($j = 0, $n = count($item); $j < $n; $j++) {
                    $product_id    = $item[$j];
                    $rate          = $item_rate[$j];
                    $qty           = $item_qty[$j];
                    $supplier_rate = $item_supp_rate[$j];
                    $discount      = $item_dis_per[$j];
                    $discountval   = $item_total_discount[$j];
                    $vatper        = $vat_per[$j];
                    $vatvalue      = $vat_value[$j];
                    $tax           = $item_tax[$j];
                    $srl           = $serial[$j];
                    $dcript        = $descrp[$j];
                    $total_price   = $totalp[$j];
                    $quotitem = array(
                        'quot_id'       => $quot_id,
                        'product_id'    => $product_id,
                        'batch_id'      => $srl,
                        'description'   => $dcript,
                        'rate'          => $rate,
                        'supplier_rate' => $supplier_rate,
                        'total_price'   => $total_price,
                        'discount_per'  => $discount,
                        'discount'      => $discountval,
                        'vat_amnt'      => $vatvalue,
                        'vat_per'       => $vatper,
                        'tax'           => $tax,
                        'used_qty'      => $qty,
                    );
                    $this->db->insert('deli_products_used', $quotitem);
                }

                //item tax info
                for ($l = 0; $l < $num_column; $l++) {
                    $taxfield = 'tax' . $l;
                    $taxvalue = 'total_tax' . $l;
                    $taxdata[$taxfield] = $this->input->post($taxvalue);
                }
                $taxdata['customer_id'] = $this->input->post('customer_id', TRUE);
                $taxdata['date']        = (!empty($this->input->post('qdate', TRUE)) ? $this->input->post('qdate', TRUE) : date('Y-m-d'));
                $taxdata['relation_id'] = 'item' . $this->input->post('quotation_no', TRUE);
                $this->db->insert('delivery_taxinfo', $taxdata);

                // Used Service Details Part
                $service                = $this->input->post('service_id', TRUE);
                $service_rate           = $this->input->post('service_rate', TRUE);
                $service_qty            = $this->input->post('service_quantity', TRUE);
                $service_dis_per        = $this->input->post('sdiscount', TRUE);
                $service_discountvalue  = $this->input->post('service_discountvalue', TRUE);
                $service_vatpercent     = $this->input->post('service_vatpercent', TRUE);
                $service_vatvalue       = $this->input->post('service_vatvalue', TRUE);
                $totalservicep          = $this->input->post('total_service_amount', TRUE);
                $service_tax            = $this->input->post('stax', TRUE);
                for ($k = 0, $n = count($service); $k < $n; $k++) {
                    $service_id     = $service[$k];
                    $charge         = $service_rate[$k];
                    $sqty           = $service_qty[$k];
                    $sdiscount      = $service_dis_per[$k];
                    $stotaldiscount = $service_discountvalue[$k];
                    $service_vatper = $service_vatpercent[$k];
                    $servicevatval  = $service_vatvalue[$k];
                    $stax           = $service_tax[$k];
                    $total_serviceprice = $totalservicep[$k];
                    $quotservice = array(
                        'quot_id'        => $quot_id,
                        'service_id'     => $service_id,
                        'charge'         => $charge,
                        'total'          => $total_serviceprice,
                        'discount'       => $sdiscount,
                        'discount_amount' => $stotaldiscount,
                        'vat_per'        => $service_vatper,
                        'vat_amnt'       => $servicevatval,
                        'tax'            => $stax,
                        'qty'            => $sqty,
                    );
                    $this->db->insert('delivery_service_used', $quotservice);
                }
                //service taxinfo

                for ($m = 0; $m < $num_column; $m++) {
                    $taxfield = 'tax' . $m;
                    $taxvalue = 'total_service_tax' . $m;
                    $servicetaxinfo[$taxfield] = $this->input->post($taxvalue);
                }
                $servicetaxinfo['customer_id'] = $this->input->post('customer_id', TRUE);
                $servicetaxinfo['date']        = (!empty($this->input->post('qdate', TRUE)) ? $this->input->post('qdate', TRUE) : date('Y-m-d'));
                $servicetaxinfo['relation_id'] = 'serv' . $this->input->post('quotation_no', TRUE);
                $this->db->insert('delivery_taxinfo', $servicetaxinfo);

                $mailsetting = $this->db->select('*')->from('email_config')->get()->result_array();
                if ($mailsetting[0]['isquotation'] == 1) {
                    $mail = $this->quotation_pdf_generate($quot_id);
                    if ($mail == 0) {
                        $this->session->set_flashdata(array('exception' => display('please_config_your_mail_setting')));
                    }
                }
                $this->session->set_flashdata(array('message' => display('successfully_added')));
                redirect(base_url('manage_quotation'));
            } else {
                $this->session->set_flashdata(array('exception' => display('already_inserted')));
                redirect(base_url('manage_quotation'));
            }
        } else {
            $this->session->set_flashdata(array('exception' => validation_errors()));
            redirect(base_url('manage_quotation'));
        }
    }
}
