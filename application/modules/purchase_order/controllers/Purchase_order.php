<?php
defined('BASEPATH') or exit('No direct script access allowed');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

ini_set('error_reporting', E_ALL & ~E_DEPRECATED);

class Purchase_order extends MX_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'supplier/supplier_model',
            'account/Accounts_model',
            'purchase_order_model',
            'purchase/purchase_model'
        ));

        if (!$this->session->userdata('isLogIn'))
            redirect('login');
    }

    function purchase_order_form()
    {
        $data['title']           = "Add purchase order";
        $data['quotation_no']    = $this->purchase_order_model->purchase_order_quot_number_generator();
        $data['taxes']           = taxFields();
        $data['customers']       = $this->supplier_model->allsupplier();

        // $data['get_productlist'] = getAllProducts();
        $vatortax                = vatTaxSetting();
        if ($vatortax->fixed_tax == 1) {
            $data['page']        = "purchase_order_form";
        }
        if ($vatortax->dynamic_tax == 1) {
            $data['page']        = "purchase_order_form_dynamic";
        }
        $data['module']          = "purchase_order";
        echo modules::run('template/layout', $data);
    }

    public function save_purchase_order_form()
    {
        $this->form_validation->set_rules('supplier_id', display('supplier_name'), 'required|max_length[50]');
        $this->form_validation->set_rules('qdate', display('quotation_date'), 'required|max_length[50]');
        $this->form_validation->set_rules('expiry_date', display('expiry_date'), 'required|max_length[50]');
        if ($this->form_validation->run()) {

            $quot_id     = $this->purchase_order_model->purchase_order_quot_number_generator();

            $tablecolumn = $this->db->list_fields('quotation_taxinfo');
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
            $status = 1;

            $quotation_main_id = $this->input->post('quotation_main_id', TRUE);
            $data = array(
                'quotation_id'        => $quot_id,
                'supplier_id'         => $this->input->post('supplier_id', TRUE),
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
                'is_fixed'            =>  $is_fixed,
                'is_dynamic'          =>  $is_dynamic,
                'by_order'     => isset($quotation_main_id) ? $quotation_main_id : $quot_id,
                'quotation_main_id'     => isset($quotation_main_id) ? $quotation_main_id : $quot_id,
            );

            $result = $this->purchase_order_model->purchase_order_entry($data);

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
                $tax           = isset($item_tax[$j]) ? $item_tax[$j] : NULL;
                $srl           = isset($serial[$j]) ? $serial[$j] : NULL;
                $dcript        = $descrp[$j];
                $total_price   = $totalp[$j];

                if ($rate < $supplier_rate) {
                    $this->db->where('quot_id', $quot_id);
                    $this->db->delete('quot_products_used');
                    echo '<script>alert("' . $rate . ' This price cannot available. Minimum Pirce requirment ' . $supplier_rate . ' ");</script>';
                    echo '<script>setTimeout(function(){ window.history.back(); location.reload(true); }, 1000);</script>';
                    exit();
                }

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

                $this->db->insert('purchase_order_products_used', $quotitem);
            }

            //item tax info
            for ($l = 0; $l < $num_column; $l++) {
                $taxfield = 'tax' . $l;
                $taxvalue = 'total_tax' . $l;
                $taxdata[$taxfield] = $this->input->post($taxvalue);
            }
            $taxdata['supplier_id'] = $this->input->post('supplier_id', TRUE);
            $taxdata['date']        = (!empty($this->input->post('qdate', TRUE)) ? $this->input->post('qdate', TRUE) : date('Y-m-d'));
            $taxdata['relation_id'] = 'item' . $this->input->post('quotation_no', TRUE);
            $this->db->insert('purchase_order_taxinfo', $taxdata);

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
                $stax           = isset($service_tax) ? $service_tax[$k] : NULL;
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
                $this->db->insert('purchase_order_service_used', $quotservice);
            }
            //service taxinfo

            for ($m = 0; $m < $num_column; $m++) {
                $taxfield = 'tax' . $m;
                $taxvalue = 'total_service_tax' . $m;
                $servicetaxinfo[$taxfield] = $this->input->post($taxvalue);
            }
            $servicetaxinfo['supplier_id'] = $this->input->post('supplier_id', TRUE);
            $servicetaxinfo['date']        = (!empty($this->input->post('qdate', TRUE)) ? $this->input->post('qdate', TRUE) : date('Y-m-d'));
            $servicetaxinfo['relation_id'] = 'serv' . $this->input->post('quotation_no', TRUE);
            $this->db->insert('purchase_order_taxinfo', $servicetaxinfo);

            $mailsetting = $this->db->select('*')->from('email_config')->get()->result_array();
            if ($mailsetting[0]['isquotation'] == 1) {
                $mail = $this->quotation_pdf_generate($quot_id);
                if ($mail == 0) {
                    $this->session->set_flashdata(array('exception' => display('please_config_your_mail_setting')));
                }
            }
            $this->session->set_flashdata(array('message' => 'purchase_order Added Successfully'));
            redirect(base_url('manage_purchase_order'));
        } else {
            $this->session->set_flashdata(array('exception' => validation_errors()));
            redirect(base_url('add_purchase_order'));
        }
    }

    public function edit_purchase_order($quot_id = null)
    {
        $vat_tax_info   = vatTaxSetting();
        $data['quot_main']    = $this->purchase_order_model->purchase_order_main_edit($quot_id);

        if ($data['quot_main'][0]['is_dynamic'] == 1) {
            if ($data['quot_main'][0]['is_dynamic'] != $vat_tax_info->dynamic_tax) {

                $this->session->set_flashdata('exception', 'VAT and TAX are set globally, which is not the same as VAT and TAX on this invoice. (which was configured when the invoice was created). It is not editable.');
                redirect("manage_purchase_order");
            }
        } elseif ($data['quot_main'][0]['is_fixed'] == 1) {
            if ($data['quot_main'][0]['is_fixed'] != $vat_tax_info->fixed_tax) {

                $this->session->set_flashdata('exception', 'VAT and TAX are set globally, which is not the same as VAT and TAX on this invoice. (which was configured when the invoice was created). It is not editable.');
                redirect("manage_purchase_order");
            }
        }
        $taxfield = $this->db->select('tax_name,default_value')
            ->from('tax_settings')
            ->get()
            ->result_array();

        $tablecolumn          = $this->db->list_fields('tax_collection');
        $num_column           = count($tablecolumn) - 4;
        $currency_details     = setting_data();
        $data['currency_details'] = $currency_details;
        $data['title']        = "Purchase Order Edit";
        $data['quot_product'] = $this->purchase_order_model->purchase_order_product_detail($quot_id);
        $data['quot_service'] = $this->purchase_order_model->purchase_order_service_detail($quot_id);
        $data['customer_info'] = $this->purchase_order_model->supplierinfo($data['quot_main'][0]['supplier_id']);
        $data['itemtaxin']    = $this->purchase_order_model->itemTaxDetails($data['quot_main'][0]['quot_no']);
        $data['servicetaxin'] = $this->purchase_order_model->serviceTaxDetails($data['quot_main'][0]['quot_no']);
        $data['taxes']       = $taxfield;
        $data['taxnumber']   = $num_column;
        $data['customers']   = $this->supplier_model->allsupplier();;
        $data['get_productlist'] = getAllProducts();
        $data['discount_type'] = $currency_details[0]['discount_type'];
        $data['company_info'] = retrieve_company();
        $data['module']       = "purchase_order";
        // $vatortax              = $this->quotation_model->vat_tax_setting();
        if ($vat_tax_info->fixed_tax == 1) {

            $data['page']         = "purchase_order_update";
        }
        if ($vat_tax_info->dynamic_tax == 1) {
            $data['page']          = "purchase_order_update_dynamic";
        }

        echo modules::run('template/layout', $data);
    }

    public function update_purchase_order()
    {
        $this->form_validation->set_rules('product_rate', display('product_rate'), 'required');
        $this->form_validation->set_rules('product_quantity', display('product_quantity'), 'required');

        $quot_id = $this->input->post('quotation_id', TRUE);
        $tablecolumn = $this->db->list_fields('quotation_taxinfo');
        $num_column = count($tablecolumn) - 4;
        $customershow = 0;
        $status = 1;
        $data = array(
            'quotation_id'        => $quot_id,
            'supplier_id'         => $this->input->post('supplier_id', TRUE),
            'quotdate'            => $this->input->post('qdate', TRUE),
            'expire_date'         => $this->input->post('expiry_date', TRUE),
            'item_total_amount'   => $this->input->post('grand_total_price', TRUE),
            'item_total_dicount'  => $this->input->post('total_discount', TRUE),
            'item_total_tax'      => $this->input->post('total_tax', TRUE),
            'item_total_vat'      => $this->input->post('total_vat_amnt', TRUE),
            'service_total_amount' => $this->input->post('grand_total_service_amount', TRUE),
            'service_total_discount' => $this->input->post('totalServiceDicount', TRUE),
            'service_total_vat'   => $this->input->post('service_total_vat_amnt', TRUE),
            'service_total_tax'   => $this->input->post('total_service_tax', TRUE),
            'quot_dis_item'       => $this->input->post('invoice_discount', TRUE),
            'quot_dis_service'    => $this->input->post('service_discount', TRUE),
            'quot_no'             => $this->input->post('quotation_no', TRUE),
            'create_by'           => $this->session->userdata('id'),
            'quot_description'    => $this->input->post('details', TRUE),
            'status'              => $status,
        );

        $this->db->where('quot_id', $quot_id);
        $this->db->delete('purchase_order_products_used');
        $this->db->where('quot_id', $quot_id);
        $this->db->delete('purchase_order_service_used');
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
            $totaldiscount = $item_total_discount[$j];
            $vatper        = $vat_per[$j];
            $vatvalue      = $vat_value[$j];
            $tax           = isset($item_tax[$j]) ? $item_tax[$j] : NULL;
            $srl           = isset($serial[$j]) ? $serial[$j] : NULL;
            $dcript        = $descrp[$j];
            $total_price   = $totalp[$j];

            if ($rate < $supplier_rate) {
                echo '<script>alert("' . $rate . ' This price cannot available. Minimum Pirce requirment ' . $supplier_rate . ' ");</script>';
                echo '<script>setTimeout(function(){ window.history.back(); location.reload(true); }, 1000);</script>';
                exit();
            }

            $quotitem = array(
                'quot_id'       => $quot_id,
                'product_id'    => $product_id,
                'batch_id'      => $srl,
                'description'   => $dcript,
                'rate'          => $rate,
                'supplier_rate' => $supplier_rate,
                'total_price'   => $total_price,
                'discount_per'  => $discount,
                'discount'      => $totaldiscount,
                'vat_amnt'      => $vatvalue,
                'vat_per'       => $vatper,
                'tax'           => $tax,
                'used_qty'      => $qty,
            );

            $this->db->insert('purchase_order_products_used', $quotitem);
        }

        $result = $this->purchase_order_model->update($data);

        $taxdata['supplier_id'] = $this->input->post('supplier_id', TRUE);
        $taxdata['date']        = (!empty($this->input->post('qdate', TRUE)) ? $this->input->post('qdate', TRUE) : date('Y-m-d'));
        $taxdata['relation_id'] = 'item' . $this->input->post('quotation_no', TRUE);
        $this->db->insert('purchase_order_taxinfo', $taxdata);

        // Used Service Details Part
        $service                = $this->input->post('service_id', TRUE);
        $service_rate           = $this->input->post('service_rate', TRUE);
        $service_qty            = $this->input->post('service_quantity', TRUE);
        $service_dis_per        = $this->input->post('sdiscount', TRUE);
        $service_total_discount = $this->input->post('service_discountvalue', TRUE);
        $service_vatpercent     = $this->input->post('service_vatpercent', TRUE);
        $service_vatvalue       = $this->input->post('service_vatvalue', TRUE);
        $totalservicep          = $this->input->post('total_service_amount', TRUE);
        $service_tax            = $this->input->post('stax', TRUE);
        for ($k = 0, $n = count($service); $k < $n; $k++) {
            $service_id     = $service[$k];
            $charge         = $service_rate[$k];
            $sqty           = $service_qty[$k];
            $sdiscount      = $service_dis_per[$k];
            $stotaldiscount = $service_total_discount[$k];
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
                'tax'            => 0,
                'qty'            => $qty,
            );
            $this->db->insert('purchase_order_service_used', $quotservice);
        }

        $servicetaxinfo['supplier_id'] = $this->input->post('supplier_id', TRUE);
        $servicetaxinfo['date']        = (!empty($this->input->post('qdate', TRUE)) ? $this->input->post('qdate', TRUE) : date('Y-m-d'));
        $servicetaxinfo['relation_id'] = 'serv' . $this->input->post('quotation_no', TRUE);
        $this->db->insert('purchase_order_taxinfo', $servicetaxinfo);

        $mailsetting = $this->db->select('*')->from('email_config')->get()->result_array();
        if ($mailsetting[0]['isquotation'] == 1) {
            $mail = $this->quotation_pdf_generate($quot_id);
            if ($mail == 0) {
                $this->session->set_flashdata(array('exception' => display('please_config_your_mail_setting')));
            }
        }
        $this->session->set_flashdata(array('message' => display('quotation_successfully_updated')));
        redirect(base_url('manage_purchase_order'));
    }

    public function delete($quot_id)
    {
        $this->db->where('quot_id', $quot_id);
        $this->db->delete('purchase_order');
        $this->db->where('quot_id', $quot_id);
        $this->db->delete('purchase_order_products_used');
        $this->db->where('quot_id', $quot_id);
        $this->db->delete('purchase_order_service_used');
    }

    public function purchase_order_details($quot_id = null)
    {
        $currency_details     = setting_data();
        $data['currency_details'] = $currency_details;
        $data['quot_main']    = $this->purchase_order_model->purchase_order_main_edit($quot_id);
        $data['quot_product'] = $this->purchase_order_model->purchase_order_product_detail($quot_id);
        $data['quot_service'] = $this->purchase_order_model->purchase_order_service_detail($quot_id);
        $data['customer_info'] = $this->purchase_order_model->supplierinfo($data['quot_main'][0]['supplier_id']);
        $data['itemtaxin']    = $this->purchase_order_model->itemTaxDetails($data['quot_main'][0]['quot_no']);
        $data['servicetaxin'] = $this->purchase_order_model->serviceTaxDetails($data['quot_main'][0]['quot_no']);
        $data['customers']   = $this->supplier_model->allsupplier();;
        $data['get_productlist'] = getAllProducts();
        $data['discount_type'] = $currency_details[0]['discount_type'];
        $data['company_info'] = retrieve_company();

        $data['title']        = 'Purchase Order Details';

        $data['module']        = "purchase_order";
        $data['page']          = "purchase_order_details";
        echo modules::run('template/layout', $data);
    }

    public function manage_purchase_order()
    {
        $data['title']         = 'purchase_orders';
        $config["base_url"]    = base_url('manage_purchase_order');
        $config["total_rows"]  = $this->db->count_all('purchase_order');
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
        $data['module'] = "purchase_order";
        $data['quotation_list'] = $this->purchase_order_model->list($config["per_page"], $page);

        $data['page']   = "purchase_order_list";
        echo Modules::run('template/layout', $data);
    }

    public function purchase_order_download($quot_id = null)
    {
        $currency_details         = setting_data();
        $data['currency_details'] = $currency_details;
        $data['title']            = "purchase_order Details";
        $data['quot_main']    = $this->purchase_order_model->purchase_order_main_edit($quot_id);
        $data['quot_product'] = $this->purchase_order_model->purchase_order_product_detail($quot_id);
        $data['quot_service'] = $this->purchase_order_model->purchase_order_service_detail($quot_id);
        $data['customer_info'] = $this->purchase_order_model->supplierinfo($data['quot_main'][0]['supplier_id']);
        $data['discount_type']   = $currency_details[0]['discount_type'];
        $data['company_info'] = retrieve_company();
        $data['currency_details'] = $currency_details;

        $this->load->library('pdfgenerator');
        $dompdf = new Dompdf\Dompdf();
        $page = $this->load->view('purchase_order/purchase_order_download', $data, true);
        $file_name = time();
        $dompdf->load_html($page);
        $dompdf->render();
        $output = $dompdf->output();
        file_put_contents("assets/data/pdf/quotation/$file_name.pdf", $output);
        $filename = $file_name . '.pdf';
        $file_path = base_url() . 'assets/data/pdf/quotation/' . $filename;

        $this->load->helper('download');
        force_download('./assets/data/pdf/quotation/' . $filename, NULL);
        redirect("manage_purchase_order");
    }


    public function quotation_pdf_generate($quot_id = null)
    {
        $id = $quot_id;
        $currency_details         = setting_data();
        $data['currency_details'] = $currency_details;
        $data['discount_type']    = $currency_details[0]['discount_type'];
        $data['title']            = display('quotation_details');
        $data['quot_service']     = $this->purchase_order_model->purchase_order_service_detail($quot_id);
        $data['quot_main']        = $this->purchase_order_model->purchase_order_main_edit($quot_id);
        $data['quot_product']     = $this->purchase_order_model->purchase_order_product_detail($quot_id);
        $data['customer_info']    = $this->purchase_order_model->supplierinfo($data['quot_main'][0]['supplier_id']);
        $data['company_info'] = retrieve_company();
        $name    = $data['customer_info'][0]['customer_name'];
        $email   = $data['customer_info'][0]['customer_email'];
        $this->load->library('pdfgenerator');
        $html   = $this->load->view('quotation/quotation_download', $data, true);
        $dompdf = new Dompdf\Dompdf();
        $dompdf->load_html($html);
        $dompdf->render();
        $output = $dompdf->output();
        file_put_contents('assets/data/pdf/quotation/' . $id . '.pdf', $output);
        $file_path = getcwd() . '/assets/data/pdf/quotation/' . $id . '.pdf';
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

    public function setmail($email, $file_path, $id = null, $name = null)
    {
        $setting_detail = $this->db->select('*')->from('email_config')->get()->row();
        $subject = 'Quotation Information';
        $message = strtoupper($name) . '-' . $id;

        $config = array(
            'protocol'  => $setting_detail->protocol,
            'smtp_host' => $setting_detail->smtp_host,
            'smtp_port' => $setting_detail->smtp_port,
            'smtp_user' => $setting_detail->smtp_user,
            'smtp_pass' => $setting_detail->smtp_pass,
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'wordwrap'  => TRUE
        );

        $this->load->library('email');
        $this->email->initialize($config);
        $this->email->set_newline("\r\n");
        $this->email->set_mailtype("html");
        $this->email->from($setting_detail->smtp_user);
        $this->email->to($email);

        $config = array(
            'protocol'  => $setting_detail->protocol,
            'smtp_host' => $setting_detail->smtp_host,
            'smtp_port' => $setting_detail->smtp_port,
            'smtp_user' => $setting_detail->smtp_user,
            'smtp_pass' => $setting_detail->smtp_pass,
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'wordwrap'  => TRUE
        );

        $this->load->library('email');
        $this->email->initialize($config);
        $this->email->set_newline("\r\n");
        $this->email->set_mailtype("html");
        $this->email->from($setting_detail->smtp_user);
        $this->email->to($email);
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->attach($file_path);
        $check_email = $this->test_input($email);
        if (filter_var($check_email, FILTER_VALIDATE_EMAIL)) {
            if ($this->email->send()) {
                return true;
            } else {
                $this->session->set_flashdata(array('exception' => display('please_configure_your_mail.')));
                return false;
            }
        } else {

            return false;
        }
    }

    //Email testing for email
    public function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    public function updatePaymentType($purchase_orderId, $payment_type)
    {
        $data = array(
            'payment_type' => $payment_type[0],
        );

        $this->db->where('quotation_id', $purchase_orderId);
        $this->db->update('purchase_order', $data);
    }


    public function to_purchase($quot_id = null)
    {
        $currency_details     = setting_data();
        $data['currency_details'] = $currency_details;
        $data['quot_main']    = $this->purchase_order_model->purchase_order_main_edit($quot_id);
        $data['quot_product'] = $this->purchase_order_model->purchase_order_product_detail($quot_id);
        $data['quot_service'] = $this->purchase_order_model->purchase_order_service_detail($quot_id);
        $data['customer_info'] = $this->purchase_order_model->supplierinfo($data['quot_main'][0]['supplier_id']);
        $data['itemtaxin']    = $this->purchase_order_model->itemTaxDetails($data['quot_main'][0]['quot_no']);
        $data['servicetaxin'] = $this->purchase_order_model->serviceTaxDetails($data['quot_main'][0]['quot_no']);
        $data['customers']   = $this->supplier_model->allsupplier();;
        $data['get_productlist'] = getAllProducts();
        $data['discount_type'] = $currency_details[0]['discount_type'];
        $data['company_info'] = retrieve_company();


        $vat_tax_info   = $this->quotation_model->vat_tax_setting();
        $data['quot_main']    = $this->purchase_order_model->purchase_order_main_edit($quot_id);
        if ($data['quot_main'][0]['is_dynamic'] == 1) {
            if ($data['quot_main'][0]['is_dynamic'] != $vat_tax_info->dynamic_tax) {

                $this->session->set_flashdata('exception', 'VAT and TAX are set globally, which is not the same as VAT and TAX on this invoice. (which was configured when the invoice was created). It is not editable.');
                redirect("manage_performa");
            }
        } elseif ($data['quot_main'][0]['is_fixed'] == 1) {
            if ($data['quot_main'][0]['is_fixed'] != $vat_tax_info->fixed_tax) {

                $this->session->set_flashdata('exception', 'VAT and TAX are set globally, which is not the same as VAT and TAX on this invoice. (which was configured when the invoice was created). It is not editable.');
                redirect("manage_performa");
            }
        }
        $taxfield = taxFields();

        $tablecolumn = $this->db->list_fields('tax_collection');
        $num_column = count($tablecolumn) - 4;
        $currency_details        = setting_data();
        $data['currency_details'] = $currency_details;
        $data['title']           = "Delivery to Purchase";
        $data['quot_product'] = $this->purchase_order_model->purchase_order_product_detail($quot_id);
        $data['quot_service'] = $this->purchase_order_model->purchase_order_service_detail($quot_id);
        $data['customer_info'] = $this->purchase_order_model->supplierinfo($data['quot_main'][0]['supplier_id']);
        $data['itemtaxin']    = $this->purchase_order_model->itemTaxDetails($data['quot_main'][0]['quot_no']);
        $data['servicetaxin'] = $this->purchase_order_model->serviceTaxDetails($data['quot_main'][0]['quot_no']);
        $data['taxes']           = $taxfield;
        $data['taxnumber']       = $num_column;
        $data['customers']   = $this->supplier_model->allsupplier();;
        $data['get_productlist'] = getAllProducts();
        $data['all_pmethod']     = pmethod_dropdown();
        $data['module']          = "purchase_order";
        $vatortax              = vatTaxSetting();
        if ($vatortax->fixed_tax == 1) {
            $data['page']            = "to_purchase";
        }
        if ($vatortax->dynamic_tax == 1) {
            $data['page']          = "to_purchase_dynamic";
        }
        echo modules::run('template/layout', $data);
    }

    public function save_to_purchase()
    {
        $this->form_validation->set_rules('supplier_id', display('supplier'), 'required|max_length[15]');
        $this->form_validation->set_rules('chalan_no', display('invoice_no'), 'required|max_length[20]|is_unique[product_purchase.chalan_no]');
        $this->form_validation->set_rules('product_id[]', display('product'), 'required|max_length[20]');
        $this->form_validation->set_rules('multipaytype[]', display('payment_type'), 'required');
        $this->form_validation->set_rules('product_quantity[]', display('quantity'), 'required|max_length[20]');
        $this->form_validation->set_rules('product_rate[]', display('rate'), 'required|max_length[20]');
        $discount_per = $this->input->post('discount_per', TRUE);
        $finyear = $this->input->post('finyear', true);
        if ($finyear <= 0) {
            $this->session->set_flashdata('exception', 'Please Create Financial Year First From Accounts > Financial Year.');
            redirect("add_purchase");
        } else {

            if ($this->form_validation->run() === true) {

                $purchase_data = $this->purchase_model->insert_purchase();

                if ($purchase_data == 1) {

                    $this->session->set_flashdata('message', display('save_successfully'));
                    redirect("manage_purchase_order");
                }
                if ($purchase_data == 2) {

                    $this->session->set_flashdata('exception', 'Paid Amount Should Equal To Payment Amount');
                    redirect("manage_purchase_order");
                }
                if ($purchase_data == 3) {

                    $this->session->set_flashdata('exception', display('ooops_something_went_wrong'));
                    redirect("manage_purchase_order");
                }
            } else {
                $this->session->set_flashdata('exception', validation_errors());
                redirect("manage_purchase_order");
            }
        }
    }
}
