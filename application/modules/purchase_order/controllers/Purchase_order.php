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
        // $data['quotation_no']    = $this->purchase_order_model->purchase_order_quot_number_generator();
        $data['all_supplier'] = $this->purchase_model->supplier_list();
        $data['all_pmethod'] = pmethod_dropdown();
        $data['page']        = "purchase_order_form";
        $data['module']          = "purchase_order";
        echo modules::run('template/layout', $data);
    }

    public function save_purchase_order_form()
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

                $purchase_data = $this->purchase_order_model->insert_purchase_order();

                if ($purchase_data == 1) {

                    $this->session->set_flashdata('message', display('save_successfully'));
                    redirect("manage_purchase_order");
                }
                if ($purchase_data == 2) {

                    $this->session->set_flashdata('exception', 'Paid Amount Should Equal To Payment Amount');
                    redirect("purchase_order_form");
                }
                if ($purchase_data == 3) {

                    $this->session->set_flashdata('exception', display('ooops_something_went_wrong'));
                    redirect("purchase_order_form");
                }
            } else {
                $this->session->set_flashdata('exception', validation_errors());
                redirect("purchase_order_form");
            }
        }
    }

    public function edit_purchase_order($purchase_id = null)
    {
        $purchase_detail = $this->purchase_order_model->retrieve_purchase_order_editdata($purchase_id);
        $supplier_id = $purchase_detail[0]['supplier_id'];
        $supplier_list = $this->purchase_model->supplier_list();

        if (!empty($purchase_detail)) {
            $i = 0;
            foreach ($purchase_detail as $k => $v) {
                $i++;
                $purchase_detail[$k]['sl'] = $i;
            }
        }
        $multi_pay_data = $this->db->select('RevCodde, Debit')
            ->from('acc_vaucher')
            ->where('referenceNo', $purchase_detail[0]['purchase_id'])
            ->get()->result();



        $data = array(
            'title'             => display('purchase_edit'),
            'dbpurs_id'         => $purchase_detail[0]['dbpurs_id'],
            'purchase_id'       => $purchase_detail[0]['purchaseID'],
            'chalan_no'         => $purchase_detail[0]['chalan_no'],
            'supplier_name'     => $purchase_detail[0]['supplier_name'],
            'supplier_id'       => $purchase_detail[0]['supplier_id'],
            'grand_total'       => $purchase_detail[0]['grand_total_amount'],
            'purchase_details'  => $purchase_detail[0]['purchase_details'],
            'purchase_date'     => $purchase_detail[0]['purchase_date'],
            'total_discount'    => $purchase_detail[0]['total_discount'],
            'invoice_discount'  => $purchase_detail[0]['invoice_discount'],
            'total_vat_amnt'    => $purchase_detail[0]['total_vat_amnt'],
            'payment_type'    => $purchase_detail[0]['payment_type'],
            'total'             => number_format($purchase_detail[0]['grand_total_amount'] + (!empty($purchase_detail[0]['total_discount']) ? $purchase_detail[0]['total_discount'] : 0), 2),
            'bank_id'           =>  $purchase_detail[0]['bank_id'],
            'purchase_info'     => $purchase_detail,
            'supplier_list'     => $supplier_list,
            'paid_amount'       => $purchase_detail[0]['paid_amount'],
            'due_amount'        => $purchase_detail[0]['due_amount'],
            'multi_paytype'     => $multi_pay_data,
            'is_credit'         => $purchase_detail[0]['is_credit'],
        );

        $data['all_pmethod']    = pmethod_dropdown_new();
        $data['all_pmethodwith_cr'] = pmethod_dropdown();
        $data['module']          = "purchase_order";
        $data['page']         = "purchase_order_update";
        echo modules::run('template/layout', $data);
    }

    public function update_purchase_order()
    {
        $purchase_id  = $this->input->post('purchase_id', TRUE);
        $dbpurs_id    = $this->input->post('dbpurs_id', TRUE);
        $this->form_validation->set_rules('supplier_id', display('supplier'), 'required|max_length[15]');
        $this->form_validation->set_rules('chalan_no', display('invoice_no'), 'required|max_length[20]');
        $this->form_validation->set_rules('product_id[]', display('product'), 'required|max_length[20]');
        $this->form_validation->set_rules('multipaytype[]', display('payment_type'), 'required');
        $this->form_validation->set_rules('product_quantity[]', display('quantity'), 'required|max_length[20]');
        $this->form_validation->set_rules('product_rate[]', display('rate'), 'required|max_length[20]');
        $finyear = $this->input->post('finyear', true);
        if ($finyear <= 0) {
            $this->session->set_flashdata('exception', 'Please Create Financial Year First From Accounts > Financial Year.');
            redirect("add_purchase");
        } else {

            if ($this->form_validation->run() === true) {

                $paid_amount  = $this->input->post('paid_amount', TRUE);
                $due_amount   = $this->input->post('due_amount', TRUE);
                $bank_id      = $this->input->post('bank_id', TRUE);
                if (!empty($bank_id)) {
                    $bankname = $this->db->select('bank_name')->from('bank_add')->where('bank_id', $bank_id)->get()->row()->bank_name;
                    $bankcoaid   = $this->db->select('HeadCode')->from('acc_coa')->where('HeadName', $bankname)->get()->row()->HeadCode;
                }
                $p_id        = $this->input->post('product_id', TRUE);
                $supplier_id = $this->input->post('supplier_id', TRUE);
                $supinfo     = $this->db->select('*')->from('supplier_information')->where('supplier_id', $supplier_id)->get()->row();
                $sup_head    = $supinfo->supplier_id . '-' . $supinfo->supplier_name;
                $sup_coa     = $this->db->select('*')->from('acc_coa')->where('HeadName', $sup_head)->get()->row();
                $receive_by  = $this->session->userdata('id');
                $receive_date = date('Y-m-d');
                $createdate  = date('Y-m-d H:i:s');
                $multipayamount = $this->input->post('pamount_by_method', TRUE);
                $multipaytype = $this->input->post('multipaytype', TRUE);

                if ($multipaytype[0] == 0) {
                    $is_credit = 1;
                } else {
                    $is_credit = '';
                }
                $data = array(
                    'purchase_id'        => $purchase_id,
                    'chalan_no'          => $this->input->post('chalan_no', TRUE),
                    'supplier_id'        => $this->input->post('supplier_id', TRUE),
                    'grand_total_amount' => $this->input->post('grand_total_price', TRUE),
                    'total_discount'     => $this->input->post('discount', TRUE),
                    'invoice_discount'   => $this->input->post('total_discount', TRUE),
                    'total_vat_amnt'     => $this->input->post('total_vat_amnt', TRUE),
                    'purchase_date'      => $this->input->post('purchase_date', TRUE),
                    'purchase_details'   => $this->input->post('purchase_details', TRUE),
                    'paid_amount'        => $paid_amount,
                    'due_amount'         => $due_amount,
                    'bank_id'           =>  $this->input->post('bank_id', TRUE),
                    'payment_type'       =>  $multipaytype[0],
                    'is_credit'          =>  $is_credit,
                );

                $predefine_account  = $this->db->select('*')->from('acc_predefine_account')->get()->row();
                $Narration          = "Purchase Voucher";
                $Comment            = "Purchase Voucher for supplier";
                $COAID              = $predefine_account->purchaseCode;

                if ($purchase_id != '') {
                    $this->db->where('id', $dbpurs_id);
                    $this->db->update('purchase_order', $data);

                    //account transaction update
                    $this->db->where('referenceNo', $purchase_id);
                    $this->db->delete('acc_vaucher');

                    $this->db->where('purchase_id', $dbpurs_id);
                    $this->db->delete('purchase_order_details');
                }


                $multipayamount = $this->input->post('pamount_by_method', TRUE);
                $multipaytype = $this->input->post('multipaytype', TRUE);

                if ($multipaytype && $multipayamount) {

                    if ($multipaytype[0] == 0) {

                        $amount_pay = $data['grand_total_amount'];
                        $amnt_type = 'Credit';
                        $reVID     = $predefine_account->supplierCode;
                        $subcode   = $this->db->select('*')->from('acc_subcode')->where('referenceNo', $supplier_id)->where('subTypeId', 4)->get()->row()->id;
                        $insrt_pay_amnt_vcher = $this->purchase_order_model->insert_purchase_debitvoucher($is_credit, $purchase_id, $COAID, $amnt_type, $amount_pay, $Narration, $Comment, $reVID, $subcode);
                    } else {
                        $amnt_type = 'Debit';
                        for ($i = 0; $i < count($multipaytype); $i++) {

                            $reVID = $multipaytype[$i];
                            $amount_pay = $multipayamount[$i];

                            $insrt_pay_amnt_vcher = $this->purchase_order_model->insert_purchase_debitvoucher($is_credit, $purchase_id, $COAID, $amnt_type, $amount_pay, $Narration, $Comment, $reVID);
                        }

                        if ($data['due_amount'] > 0) {

                            $amount_pay2 = $data['due_amount'];
                            $amnt_type2 = 'Credit';
                            $reVID2     = $predefine_account->supplierCode;
                            $subcode2   = $this->db->select('*')->from('acc_subcode')->where('referenceNo', $supplier_id)->where('subTypeId', 4)->get()->row()->id;
                            $this->purchase_order_model->insert_purchase_debitvoucher(1, $purchase_id, $COAID, $amnt_type2, $amount_pay2, $Narration, $Comment, $reVID2, $subcode2);
                        }
                    }
                }

                $rate         = $this->input->post('product_rate', TRUE);
                $p_id         = $this->input->post('product_id', TRUE);
                $quantity     = $this->input->post('product_quantity', TRUE);
                $t_price      = $this->input->post('total_price', TRUE);
                $expiry_date  = $this->input->post('expiry_date', TRUE);
                $batch_no     = $this->input->post('batch_no', TRUE);
                $discountvalue = $this->input->post('discountvalue', TRUE);
                $vatpercent   = $this->input->post('vatpercent', TRUE);
                $vatvalue     = $this->input->post('vatvalue', TRUE);
                $discount_per = $this->input->post('discount_per', TRUE);

                $discount = $this->input->post('discount', TRUE);

                for ($i = 0, $n = count($p_id); $i < $n; $i++) {
                    $product_quantity = $quantity[$i];
                    $product_rate     = $rate[$i];
                    $product_id       = $p_id[$i];
                    $total_price      = $t_price[$i];
                    $disc             = $discount[$i];
                    $ba_no            = $batch_no[$i];
                    $exp_date         = $expiry_date[$i];
                    $dis_per          = $discount_per[$i];
                    $disval           = $discountvalue[$i];
                    $vatper           = $vatpercent[$i];
                    $vatval           = $vatvalue[$i];


                    $data1 = array(
                        'purchase_detail_id' => $this->purchase_order_model->generator(15),
                        'purchase_id'        => $dbpurs_id,
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

                    if (($quantity)) {

                        $this->db->insert('purchase_order_details', $data1);
                        $this->db->where('product_id', $product_id)->update('supplier_product', $product_price);
                    }
                }
                $setting_data = $this->db->select('is_autoapprove_v')->from('web_setting')->where('setting_id', 1)->get()->result_array();
                if ($setting_data[0]['is_autoapprove_v'] == 1) {

                    $new = $this->purchase_order_model->autoapprove($purchase_id);
                }
                $this->session->set_flashdata('message', display('update_successfully'));
                redirect("manage_purchase_order");
            } else {
                $this->session->set_flashdata('exception', validation_errors());
                redirect("edit_purchase_order/" . $purchase_id);
            }
        }
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
        $data['title']         = 'Purchase Order';
        $data['total_purhcase'] = $this->purchase_order_model->count_purchase_order();
        $data['module']     = "purchase_order";
        $data['page']   = "purchase_order_list";
        echo Modules::run('template/layout', $data);
    }

    public function CheckPurchaseOrderList()
    {
        $postData  = $this->input->post();
        $data = $this->purchase_order_model->getPurchaseOrderList($postData);
        echo json_encode($data);
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


        $vat_tax_info   = vatTaxSetting();
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
        // $this->form_validation->set_rules('chalan_no', display('invoice_no'), 'required|max_length[20]|is_unique[product_purchase.chalan_no]');
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

                $purchase_id = $this->purchase_model->number_generator();
                $p_id        = $this->input->post('product_id', TRUE);
                $supplier_id = $this->input->post('supplier_id', TRUE);
                $supinfo     = $this->db->select('*')->from('supplier_information')->where('supplier_id', $supplier_id)->get()->row();
                $sup_head    = $supinfo->supplier_id . '-' . $supinfo->supplier_name;
                $sup_coa     = $this->db->select('*')->from('acc_coa')->where('HeadName', $sup_head)->get()->row();
                $receive_date = date('Y-m-d');
                $createdate  = date('Y-m-d H:i:s');
                $paid_amount = $this->input->post('paid_amount', TRUE);
                $due_amount  = $this->input->post('due_amount', TRUE);
                $bank_id     = $this->input->post('bank_id', TRUE);

                $multipayamount = $this->input->post('pamount_by_method', TRUE);
                $multipaytype = $this->input->post('multipaytype', TRUE);

                $multiamnt = array_sum($multipayamount);

                if ($multiamnt == $paid_amount) {
                    if (!empty($bank_id)) {
                        $bankname = $this->db->select('bank_name')->from('bank_add')->where('bank_id', $bank_id)->get()->row()->bank_name;

                        $bankcoaid = $this->db->select('HeadCode')->from('acc_coa')->where('HeadName', $bankname)->get()->row()->HeadCode;
                    } else {
                        $bankcoaid = '';
                    }

                    if ($multipaytype[0] == 0) {
                        $is_credit = 1;
                    } else {
                        $is_credit = '';
                    }

                    $data = array(
                        'purchase_id'        => $purchase_id,
                        // 'chalan_no'          => $this->input->post('chalan_no', TRUE),
                        'supplier_id'        => $this->input->post('supplier_id', TRUE),
                        'grand_total_amount' => $this->input->post('grand_total_price', TRUE),
                        // 'total_discount'     => $this->input->post('discount', TRUE),
                        // 'invoice_discount'   => $this->input->post('total_discount', TRUE),
                        // 'total_vat_amnt'     => $this->input->post('total_vat_amnt', TRUE),
                        'purchase_date'      => $this->input->post('qdate', TRUE),
                        'purchase_details'   => $this->input->post('details', TRUE),
                        'paid_amount'        => $paid_amount,
                        'due_amount'         => $due_amount,
                        'status'             => 1,
                        'bank_id'            => $this->input->post('bank_id', TRUE),
                        'payment_type'       => $multipaytype[0],
                        'is_credit'          => $is_credit,
                        'quotation_main_id'   => $this->input->post('quotation_main_id', TRUE),
                        'by_order'   => $this->input->post('quotation_main_id', TRUE),
                    );

                    // echo "<pre>";
                    // print_r($data);die;
                    $this->db->insert('product_purchase', $data);
                    $purs_insert_id =  $this->db->insert_id();

                    $rate         = $this->input->post('product_rate', TRUE);
                    $quantity     = $this->input->post('product_quantity', TRUE);
                    $expiry_date  = $this->input->post('expiry_date', TRUE);
                    $batch_no     = $this->input->post('batch_no', TRUE);
                    $t_price      = $this->input->post('total_price', TRUE);
                    $discountvalue = $this->input->post('discountvalue', TRUE);
                    $vatpercent   = $this->input->post('vatpercent', TRUE);
                    $vatvalue     = $this->input->post('vatvalue', TRUE);
                    $discount_per = $this->input->post('discount_per', TRUE);

                    for ($i = 0, $n = count($p_id); $i < $n; $i++) {
                        $product_quantity = $quantity[$i];
                        $product_rate     = $rate[$i];
                        $product_id       = $p_id[$i];
                        $total_price      = $t_price[$i];
                        $ba_no            = isset($batch_no[$i]) ? $batch_no[$i] : NULL;
                        $exp_date         = $expiry_date[$i];
                        $dis_per          = isset($discount_per[$i]) ? $discount_per[$i] : NULL;
                        $disval           = $discountvalue[$i];
                        $vatper           = $vatpercent[$i];
                        $vatval           = $vatvalue[$i];

                        $data1 = array(
                            'purchase_detail_id' => $this->purchase_model->generator(15),
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

                        $this->db->insert('product_purchase_details', $data1);
                        $this->db->where('product_id', $product_id)->update('supplier_product', $product_price);
                    }

                    $this->session->set_flashdata(array('message' => display('successfully_added')));
                    redirect(base_url('manage_purchase_order'));
                }
            } else {
                $this->session->set_flashdata('exception', validation_errors());
                redirect("manage_purchase_order");
            }
        }
    }
}
