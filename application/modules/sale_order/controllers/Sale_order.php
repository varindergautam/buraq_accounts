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
            'service/service_model',
            'account/Accounts_model',
            'quotation/quotation_model',
            'sale_order_model',
            'delivery/delivery_model',
            'invoice/invoice_model'
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

    public function sale_order_download($quot_id = null)
    {
        $currency_details         = $this->quotation_model->setting_data();
        $data['currency_details'] = $currency_details;
        $data['title']            = "Sale Order Details";
        $data['quot_main']    = $this->sale_order_model->sale_order_main_edit($quot_id);
        $data['quot_product'] = $this->sale_order_model->sale_order_product_detail($quot_id);
        $data['quot_service'] = $this->sale_order_model->sale_order_service_detail($quot_id);
        $data['customer_info']    = $this->sale_order_model->customerinfo($data['quot_main'][0]['customer_id']);
        $data['discount_type']   = $currency_details[0]['discount_type'];
        $data['company_info'] = $this->quotation_model->retrieve_company();
        $data['currency_details'] = $currency_details;

        $this->load->library('pdfgenerator');
        $dompdf = new Dompdf\Dompdf();
        $page = $this->load->view('sale_order/sale_order_download', $data, true);
        $file_name = time();
        $dompdf->load_html($page);
        $dompdf->render();
        $output = $dompdf->output();
        file_put_contents("assets/data/pdf/quotation/$file_name.pdf", $output);
        $filename = $file_name . '.pdf';
        $file_path = base_url() . 'assets/data/pdf/quotation/' . $filename;

        $this->load->helper('download');
        force_download('./assets/data/pdf/quotation/' . $filename, NULL);
        redirect("manage_sale_order");
    }

    //    ============ its for invoice pdf generate =======
    public function quotation_pdf_generate($quot_id = null)
    {
        $id = $quot_id;
        $currency_details         = $this->quotation_model->setting_data();
        $data['currency_details'] = $currency_details;
        $data['discount_type']    = $currency_details[0]['discount_type'];
        $data['title']            = display('quotation_details');
        $data['quot_service']     = $this->quotation_model->quot_service_detail($quot_id);
        // $data['quot_main']        = $this->quotation_model->quot_main_edit($quot_id);
        $data['quot_main']        = $this->delivery_model->delivery($quot_id);

        $data['quot_product']     = $this->quotation_model->quot_product_detail($quot_id);
        $data['customer_info']    = $this->quotation_model->customerinfo($data['quot_main'][0]['customer_id']);
        $data['company_info'] = $this->quotation_model->retrieve_company();
        $name    = $data['customer_info'][0]['customer_name'];
        $email   = $data['customer_info'][0]['customer_email'];
        $this->load->library('pdfgenerator');
        $html   = $this->load->view('quotation/quotation_download', $data, true);
        $dompdf = new Dompdf\Dompdf();
        $dompdf->load_html($html);
        $dompdf->render();
        $output = $dompdf->output();
        file_put_contents('assets/data/pdf/delivery/' . $id . '.pdf', $output);
        $file_path = getcwd() . '/assets/data/pdf/delivery/' . $id . '.pdf';
        $send_email = '';
        if (!empty($email)) {
            $send_email = $this->setmail($email, $file_path, $id, $name);

            if ($send_email) {
                return 1;
            } else {
                return 0;
            }
        }
        return 0;
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
        $data['module']          = "sale_order";
        $vatortax              = $this->quotation_model->vat_tax_setting();
        if ($vatortax->fixed_tax == 1) {

            $data['page']            = "to_delivery_note";
        }
        if ($vatortax->dynamic_tax == 1) {
            $data['page']          = "to_delivery_note_dynamic";
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
            $status = 1;
            $deliver_status = 1;
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
                'status'              => $status,
                // 'delivery_status'              => $deliver_status,
                // 'sale_order_status'              => $sale_order_status,
                'is_fixed'            =>  $is_fixed,
                'is_dynamic'          =>  $is_dynamic,
                'quotation_main_id'     => $quotation_id,
            );


            $result = $this->quotation_model->delivery_entry($data);

            $quotdata = array('delivery_note_status'  => 2);
            $this->db->where('quotation_id', $quotation_id);
            $this->db->update('sale_orders', $quotdata);

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
                redirect(base_url('manage_sale_order'));
            } else {
                $this->session->set_flashdata(array('exception' => display('already_inserted')));
                redirect(base_url('manage_sale_order'));
            }
        } else {
            $this->session->set_flashdata(array('exception' => validation_errors()));
            redirect(base_url('manage_sale_order'));
        }
    }

    public function to_sales($quot_id = null)
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
        $data['title']           = "Delivery to invoice";
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
        $data['module']          = "sale_order";
        $vatortax              = $this->quotation_model->vat_tax_setting();
        if ($vatortax->fixed_tax == 1) {

            $data['page']            = "to_sales";
        }
        if ($vatortax->dynamic_tax == 1) {
            $data['page']          = "to_sales_dynamic";
        }
        echo modules::run('template/layout', $data);
    }

    public function save_to_sales()
    {
        $this->form_validation->set_rules('customer_id', display('customer_name'), 'required|max_length[15]');

        $quotation_id = $this->input->post('quotation_id', TRUE);

        $finyear = $this->input->post('finyear', true);
        if ($finyear <= 0) {
            $this->session->set_flashdata('exception', 'Please Create Financial Year First From Accounts > Financial Year.');
            // redirect("add_purchase");
            redirect("delivery_to_sales/" . $quotation_id);
        } else {
            if ($this->form_validation->run() === true) {

                $mailsetting  = $this->db->select('*')->from('email_config')->get()->result_array();
                $product_id   = $this->input->post('product_id', TRUE);
                $customer_id  = $this->input->post('customer_id', TRUE);
                $invoice_id   = $this->invoice_model->number_generator();
                $createby     = $this->session->userdata('id');
                $createdate   = date('Y-m-d H:i:s');
                $quantity     = $this->input->post('product_quantity', TRUE);
                $squantity    = $this->input->post('service_quantity', TRUE);
                $tablecolumn  = $this->db->list_fields('tax_collection');
                $num_column   = count($tablecolumn) - 4;
                $cusifo       = $this->db->select('*')->from('customer_information')->where('customer_id', $customer_id)->get()->row();
                $no_of_credit_day = $cusifo->no_of_credit_days;
                // $headn        = $customer_id . '-' . $cusifo->customer_name;
                // $coainfo      = $this->db->select('*')->from('acc_coa')->where('HeadName', $headn)->get()->row();
                // $customer_headcode = $coainfo->HeadCode;
                $bank_id      = $this->input->post('bank_id', TRUE);
                if (!empty($bank_id)) {
                    $bankname = $this->db->select('bank_name')->from('bank_add')->where('bank_id', $bank_id)->get()->row()->bank_name;
                    $bankcoaid = $this->db->select('HeadCode')->from('acc_coa')->where('HeadName', $bankname)->get()->row()->HeadCode;
                } else {
                    $bankcoaid = '';
                }

                $multipaytype   = $this->input->post('multipaytype', TRUE);

                if ($multipaytype[0] == '0' && ($no_of_credit_day === null || $no_of_credit_day <= 0)) {
                    echo '<script>alert("Credit is not available");</script>';
                    echo '<script>setTimeout(function(){ window.history.back(); }, 1000);</script>';
                    exit();
                }

                if ($no_of_credit_day !== null && $no_of_credit_day > 0 && $multipaytype[0] == '0') {
                    $grand_total_price = $this->input->post('grand_total_price', TRUE);
                    $paid_amount = $this->input->post('paid_amount', TRUE);
                    $due_amount = $grand_total_price - $paid_amount;
                } else {
                    $due_amount = '';
                }


                $quotdata = array('status'  => 2,);
                $this->db->where('quotation_id', $quotation_id);
                $this->db->update('sale_orders', $quotdata);

                $transection_id = $this->occational->generator(15);
                $fixordyn   = $this->db->select('*')->from('vat_tax_setting')->get()->row();
                $is_fixed   = '';
                $is_dynamic = '';

                if ($fixordyn->fixed_tax == 1) {
                    $is_fixed   = 1;
                    $is_dynamic = 0;
                    $paid_tax = $this->input->post('total_vat_amnt', TRUE);
                } elseif ($fixordyn->dynamic_tax == 1) {
                    $is_fixed   = 0;
                    $is_dynamic = 1;
                    $paid_tax = $this->input->post('total_tax', TRUE);
                }

                $datainvmain = array(
                    'invoice_id'      => $invoice_id,
                    'customer_id'     => $customer_id,
                    'date'            => (!empty($this->input->post('qdate', TRUE)) ? $this->input->post('qdate', TRUE) : date('Y-m-d')),
                    'total_amount'    => $this->input->post('grand_total_price', TRUE),
                    'paid_amount'     => $this->input->post('paid_amount', TRUE),
                    'total_tax'       => $this->input->post('total_tax', TRUE),
                    'invoice'         => $invoice_id,
                    'invoice_details' => $quotation_id,
                    'invoice_discount' => $this->input->post('invoice_discount', TRUE),
                    'total_discount'  => $this->input->post('total_discount', TRUE),
                    'total_vat_amnt'  => $this->input->post('total_vat_amnt', TRUE),
                    'prevous_due'     => '',
                    'due_amount'      => $due_amount,
                    'shipping_cost'   => '',
                    'sales_by'        => $this->session->userdata('id'),
                    'status'          => 1,
                    'delivery_note_status'          => 1,
                    'payment_type'    =>  1,
                    'bank_id'         => (!empty($this->input->post('bank_id', TRUE)) ? $this->input->post('bank_id', TRUE) : null),
                    'is_fixed'        =>  $is_fixed,
                    'is_dynamic'      =>  $is_dynamic,
                    'no_of_credit_days' =>  $no_of_credit_day,
                );


                $prinfo  = $this->db->select('product_id,Avg(rate) as product_rate')->from('product_purchase_details')->where_in('product_id', $product_id)->group_by('product_id')->get()->result();
                $purchase_ave = [];
                $i = 0;
                foreach ($prinfo as $avg) {
                    $purchase_ave[] =  $avg->product_rate * $quantity[$i];
                    $i++;
                }
                $sumval = array_sum($purchase_ave);

                for ($j = 0; $j < $num_column; $j++) {
                    $taxfield = 'tax' . $j;
                    $taxvalue = 'total_tax' . $j;
                    $taxdata[$taxfield] = $this->input->post($taxvalue);
                }
                $taxdata['customer_id'] = $customer_id;
                $taxdata['date']        = (!empty($this->input->post('qdate', TRUE)) ? $this->input->post('qdate', TRUE) : date('Y-m-d'));
                $taxdata['relation_id'] = $invoice_id;


                if (!empty($quantity)) {
                    $this->db->insert('invoice', $datainvmain);
                    $inv_insert_id =  $this->db->insert_id();
                    $this->db->insert('tax_collection', $taxdata);


                    $multipayamount = $this->input->post('pamount_by_method', TRUE);
                    $multipaytype   = $this->input->post('multipaytype', TRUE);
                }

                $predefine_account  = $this->db->select('*')->from('acc_predefine_account')->get()->row();
                $Narration          = "Sales Voucher";
                $Comment            = "Sales Voucher for customer";
                $reVID              = $predefine_account->salesCode;

                if ($multipaytype && $multipayamount) {

                    $is_credit = NULL;
                    $amnt_type = 'Debit';
                    for ($i = 0; $i < count($multipaytype); $i++) {

                        $COAID = $multipaytype[$i];
                        $amount_pay = $multipayamount[$i];

                        $this->invoice_model->insert_sale_creditvoucher($is_credit, $invoice_id, $COAID, $amnt_type, $amount_pay, $Narration, $Comment, $reVID);
                    }
                }
                // for inventory & cost of goods sold start
                $goodsCOAID     = $predefine_account->costs_of_good_solds;
                $purchasevalue  = $sumval;
                $goodsNarration = "Sales cost of goods Voucher";
                $goodsComment   = "Sales cost of goods Voucher for customer";
                $goodsreVID     = $predefine_account->inventoryCode;

                $this->invoice_model->insert_sale_inventory_voucher($invoice_id, $goodsCOAID, $purchasevalue, $goodsNarration, $goodsComment, $goodsreVID);
                // for inventory & cost of goods sold end

                // for taxs start
                $taxCOAID     = $predefine_account->tax;
                $taxvalue     = $paid_tax;
                $taxNarration = "Tax for Sales Voucher";
                $taxComment   = "Tax for Sales Voucher for customer";
                $taxreVID     = $predefine_account->prov_state_tax;

                $this->invoice_model->insert_sale_taxvoucher($invoice_id, $taxCOAID, $taxvalue, $taxNarration, $taxComment, $taxreVID);
                // for taxs end


                $rate                = $this->input->post('product_rate', TRUE);
                $p_id                = $this->input->post('product_id', TRUE);
                $total_amount        = $this->input->post('total_price', TRUE);
                $discount_rate       = $this->input->post('discountvalue', TRUE);
                $discount_per        = $this->input->post('discount', TRUE);
                $vat_amnt            = $this->input->post('vatvalue', TRUE);
                $vat_amnt_pcnt       = $this->input->post('vatpercent', TRUE);
                $tax_amount          = $this->input->post('tax', TRUE);
                $invoice_description = $this->input->post('desc', TRUE);
                $serial_n            = $this->input->post('serial_no', TRUE);
                $supplier_price      = $this->input->post('supplier_price', TRUE);

                for ($i = 0, $n = count($p_id); $i < $n; $i++) {
                    $product_quantity = $quantity[$i];
                    $product_rate     = $rate[$i];
                    $product_id       = $p_id[$i];
                    $serial_no        = $serial_n[$i];
                    $total_price      = $total_amount[$i];
                    $supplier_rate    = $supplier_price[$i];
                    $disper           = $discount_per[$i];
                    $discount         = $discount_rate[$i];
                    $vatamnt          = $vat_amnt[$i];
                    $vatamntpcnt      = $vat_amnt_pcnt[$i];
                    $tax              = $tax_amount[$i];
                    $description      = $invoice_description[$i];

                    $invoiceDetails = array(
                        'invoice_details_id' => $this->invoice_model->generator(15),
                        'invoice_id'         => $inv_insert_id,
                        'product_id'         => $product_id,
                        'batch_id'           => $serial_no,
                        'quantity'           => $product_quantity,
                        'rate'               => $product_rate,
                        'discount'           => $discount,
                        'description'        => $description,
                        'discount_per'       => $disper,
                        'vat_amnt'           => $vatamnt,
                        'vat_amnt_per'       => $vatamntpcnt,
                        'tax'                => $tax,
                        'paid_amount'        => $this->input->post('grand_total_price', TRUE),
                        'due_amount'         => '',
                        'supplier_rate'      => $supplier_rate,
                        'total_price'        => $total_price,
                        'status'             => 1
                    );

                    $product_price = array(

                        'price' => $product_rate
                    );
                    if (!empty($product_quantity)) {
                        $this->db->insert('invoice_details', $invoiceDetails);
                        $this->db->where('product_id', $product_id)->update('product_information', $product_price);
                    }
                }
                if (!empty($quantity)) {

                    $setting_data = $this->db->select('is_autoapprove_v')->from('web_setting')->where('setting_id', 1)->get()->result_array();
                    if ($setting_data[0]['is_autoapprove_v'] == 1) {

                        $new = $this->invoice_model->autoapprove($invoice_id);
                    }
                    if ($mailsetting[0]['isinvoice'] == 1) {
                        $mail = $this->invoice_model->invoice_pdf_generate($invoice_id);
                        if ($mail == 0) {
                            $data['message2'] = $this->session->set_flashdata(array('exception' => display('please_config_your_mail_setting')));
                        }
                    }
                }

                ##==== SERVICE PART START ====###

                //service invoice
                $fixordyn = $this->db->select('*')->from('vat_tax_setting')->get()->row();
                $is_fixed   = '';
                $is_dynamic = '';
                $srinvoice_id = $this->invoice_model->voucher_no_generator();

                if ($fixordyn->fixed_tax == 1) {
                    $is_fixed   = 1;
                    $is_dynamic = 0;
                    $service_paid_tax = $this->input->post('service_total_vat_amnt', TRUE);
                } elseif ($fixordyn->dynamic_tax == 1) {
                    $is_fixed   = 0;
                    $is_dynamic = 1;
                    $service_paid_tax = $this->input->post('total_service_tax', TRUE);
                }
                $serviceinvoice = array(
                    'employee_id'     => '',
                    'customer_id'     => $customer_id,
                    'date'            => (!empty($this->input->post('qdate', TRUE)) ? $this->input->post('qdate', TRUE) : date('Y-m-d')),
                    'total_amount'    => $this->input->post('grand_total_service_amount', TRUE),
                    'total_tax'       => $this->input->post('total_service_tax', TRUE),
                    'voucher_no'      => $srinvoice_id,
                    'details'         => (!empty($this->input->post('details', TRUE)) ? $this->input->post('details', TRUE) : 'Service From Quotation'),
                    'invoice_discount' => $this->input->post('service_discount', TRUE),
                    'total_vat_amnt'  => $this->input->post('service_total_vat_amnt', true),
                    'total_discount'  => $this->input->post('totalServiceDicount', TRUE),
                    'shipping_cost'   => '',
                    'paid_amount'     => $this->input->post('grand_total_service_amount', TRUE),
                    'due_amount'      => 0,
                    'previous'        => '',
                    'is_fixed'        => $is_fixed,
                    'is_dynamic'      => $is_dynamic,

                );


                if (!empty($squantity) && $squantity[0] != '') {
                    $this->db->insert('service_invoice', $serviceinvoice);
                    $serv_insert_id =  $this->db->insert_id();

                    $smultipayamount = $this->input->post('ser_pamount_by_method', TRUE);
                    $smultipaytype = $this->input->post('ser_multipaytype', TRUE);
                    $i = 0;
                }


                $predefine_account  = $this->db->select('*')->from('acc_predefine_account')->get()->row();
                $Narrationserv      = "Service Sales Voucher";
                $Commentserv        = "Service Sales Voucher for customer";
                $reVIDserv          = $predefine_account->serviceCode;

                if ($smultipaytype && $smultipayamount) {

                    $amnt_type = 'Debit';
                    for ($i = 0; $i < count($smultipaytype); $i++) {

                        $COAIDserv = $smultipaytype[$i];
                        $amount_payserv = $smultipayamount[$i];

                        $this->invoice_model->insert_servsale_creditvoucher($is_credit, $srinvoice_id, $COAIDserv, $amnt_type, $amount_payserv, $Narrationserv, $Commentserv, $reVIDserv);
                    }
                }

                // for taxs start
                $taxCOAIDserv     = $predefine_account->tax;
                $taxvalueserv     = $service_paid_tax;
                $taxNarrationserv = "Tax for Service Sales Voucher";
                $taxCommentserv   = "Tax for Service Sales Voucher for customer";
                $taxreVIDserv     = $predefine_account->prov_state_tax;

                $this->invoice_model->insert_servsale_taxvoucher($srinvoice_id, $taxCOAIDserv, $taxvalueserv, $taxNarrationserv, $taxCommentserv, $taxreVIDserv);
                // for taxs end

                $qty                 = $this->input->post('service_quantity', TRUE);
                $srate               = $this->input->post('service_rate', TRUE);
                $serv_id             = $this->input->post('service_id', TRUE);
                $total_serviceamount = $this->input->post('total_service_amount', TRUE);
                $sdiscount_rate      = $this->input->post('service_discountvalue', TRUE);
                $sdiscount_per       = $this->input->post('sdiscount', TRUE);
                $svat_rate           = $this->input->post('service_vatvalue', TRUE);
                $svat_per            = $this->input->post('service_vatpercent', TRUE);
                $tax_amount          = $this->input->post('stax', TRUE);
                $invoice_description = $this->input->post('details', TRUE);

                for ($i = 0, $n   = count($serv_id); $i < $n; $i++) {
                    $service_qty  = $qty[$i];
                    $service_rate = $srate[$i];
                    $service_id   = $serv_id[$i];
                    $total_amount = $total_serviceamount[$i];
                    $sdisper       = $sdiscount_per[$i];
                    $sdisamnt      = $sdiscount_rate[$i];
                    $svatper       = $svat_per[$i];
                    $svatamnt      = $svat_rate[$i];
                    $coa_info      = $this->db->select('HeadCode')->from('acc_coa')->where('service_id', $service_id)->get()->row();

                    $service_details = array(
                        'service_inv_id'     => $serv_insert_id,
                        'service_id'         => $service_id,
                        'qty'                => $service_qty,
                        'charge'             => $service_rate,
                        'discount'           => $sdisper,
                        'discount_amount'    => $sdisamnt,
                        'vat'                => $svatper,
                        'vat_amnt'           => $svatamnt,
                        'total'              => $total_amount,
                    );

                    if (!empty($service_qty)) {
                        $this->db->insert('service_invoice_details', $service_details);
                    }
                }
                if (!empty($squantity)) {
                    $setting_data = $this->db->select('is_autoapprove_v')->from('web_setting')->where('setting_id', 1)->get()->result_array();
                    if ($setting_data[0]['is_autoapprove_v'] == 1) {

                        $new = $this->invoice_model->autoapprove($srinvoice_id);
                    }
                    if ($mailsetting[0]['isservice'] == 1) {
                        $mail = $this->invoice_model->service_pdf_generate($srinvoice_id);
                        if ($mail == 0) {
                            $this->session->set_flashdata(array('exception' => display('please_config_your_mail_setting')));
                        }
                    }
                }

                for ($j = 0; $j < $num_column; $j++) {
                    $taxfield = 'tax' . $j;
                    $taxvalue = 'total_service_tax' . $j;
                    $taxdata[$taxfield] = $this->input->post($taxvalue);
                }
                $taxdata['customer_id'] = $customer_id;
                $taxdata['date']        = (!empty($this->input->post('qdate', TRUE)) ? $this->input->post('qdate', TRUE) : date('Y-m-d'));
                $taxdata['relation_id'] = $srinvoice_id;
                $this->db->insert('tax_collection', $taxdata);
                $this->session->set_flashdata(array('message' => display('successfully_added')));
                redirect(base_url('manage_sale_order'));
            } else {
                $this->session->set_flashdata('exception', validation_errors());
                redirect("manage_sale_order/" . $quotation_id);
            }
        }
    }
}
