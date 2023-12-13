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
            'service/service_model', 'account/Accounts_model', 'quotation/quotation_model', 'sale_order_model'
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
        $config["total_rows"]  = $this->db->count_all('quotation');
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
        $data['quotation_list'] = $this->quotation_model->quotation_list($config["per_page"], $page);
        $data['page']   = "sale_order_list";
        echo Modules::run('template/layout', $data);
    }
}
