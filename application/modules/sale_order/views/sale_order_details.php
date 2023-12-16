
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd">
            <div id="printableArea" onload="printDiv('printableArea')">
                <div class="panel-body print-font-size">
                    <div class="row print_header">

                        <div class="col-xs-4">
                            <?php foreach($company_info as $company){?>
                            <img src="<?php
                                    if (isset($setting->invoice_logo)) {
                                        echo base_url().$setting->invoice_logo;
                                    }
                                    ?>" class="img-bottom-m print-logo invoice-img-position" alt=""
                                >
                            <br>
                            <span
                                class="label label-success-outline m-r-15 p-10"><?php echo display('billing_from') ?></span>
                            <address class="margin-top10">
                                <strong class=""><?php echo $company['company_name']?></strong><br>
                                <span class="comp-web"><?php echo $company['address']?></span><br>
                                <abbr class="font-bold"><?php echo display('mobile') ?>: </abbr>
                                <?php echo $company['mobile']?><br>
                                <abbr><b><?php echo display('email') ?>:</b></abbr>
                                <?php echo $company['email']?><br>
                                <abbr><b><?php echo display('website') ?>:</b></abbr>
                                <span class="comp-web"><?php echo $company['website']?></span><br>
                                <?php if (!empty($company['vat_no'])) {?>
                                <abbr class="font-bold"><?php echo display('vat_no') ?>: </abbr>
                                <?php echo $company['vat_no']?><br>
                                <?php }?>
                                <?php if (!empty($company['cr_no'])) {?>
                                <abbr class="font-bold"><?php echo display('cr_no') ?>: </abbr>
                                <?php echo $company['cr_no']?><br>
                                <?php }?>
                                <?php }?>
                                <abbr><?php echo $tax_regno?></abbr>
                            </address>



                        </div>
                        <div class="col-xs-4">
                            <?php $web_setting = $this->db->select("*")->from("web_setting")->get()->row();
                            if ($web_setting->is_qr == 1) { ?>
                            <div class="print-qr">
                                <?php  $text = base64_encode(display('invoice_no').': '.$invoice_no.' '.display('customer_name').': '. $customer_name);
                                ?>
                                <img src="http://chart.apis.google.com/chart?cht=qr&chs=250x250&chld=L|4&chl=<?php echo $text?>"
                                    alt="">
                            </div>
                            <?php }?>
                        </div>

                        <div class="col-xs-4 text-left ">
                            <h2 class="m-t-0"><?php echo "Sales Order" ?></h2>
                            <div>
                                <abbr class="font-bold">
                                    <?php echo display('invoice_no') ?>: <span dir="ltr"></span>
                                </abbr>
                                <?php echo $quot_main['0']['quot_no']?>
                            </div>
                            <div class="m-b-15">
                                <abbr class="font-bold"><?php echo display('billing_date') ?></abbr>
                                <?php echo date("d-M-Y",strtotime($quot_main['0']['quotdate']));?>
                                <br>

                                <!-- <?php $create_at = $this->db->select('CreateDate')
                                            ->from('acc_vaucher')
                                            ->where('referenceNo',$invoice_no)
                                            ->get()
                                            ->row();?>
                                <abbr class="font-bold"><?php echo display('invoice_time') ?>:</abbr>
                                <?php // echo date("H:i:s",strtotime($create_at->CreateDate));?> -->
                            </div>

                            <span class="label label-success-outline m-r-15"><?php echo display('billing_to') ?></span>

                            <address style="margin-top: 10px;" class="">
                                <strong class=""><?php echo $customer_info[0]['customer_name']; ?> </strong><br>
                                <?php if ($customer_info[0]['customer_address']) { ?>
                                <?php echo $customer_info[0]['customer_address'];?>
                                <br>
                                <?php } ?>
                                <?php if ($customer_info[0]['customer_mobile']) { ?>
                                <abbr class="font-bold"><?php echo display('mobile') ?>: </abbr>
                                <?php echo $customer_info[0]['customer_mobile'];?>
                                <br>
                                <?php }  ?>
                                <?php  if ($customer_info[0]['customer_email']){ ?>
                                <abbr class="font-bold"><?php echo display('email') ?>: </abbr>
                                <?php echo $customer_info[0]['customer_email'];?>
                                <br>
                                <?php } ?>
                                <?php if (!empty($customer_info[0]['email_address'])) {?>
                                <abbr class="font-bold"><?php echo display('vat_no') ?>: </abbr>
                                <?php echo $customer_info[0]['email_address'];?>
                                <br>
                                <?php } ?>
                                <?php if (!empty($customer_info[0]['contact'])) {?>
                                <abbr class="font-bold"><?php echo display('cr_no') ?>: </abbr>
                                <?php echo $customer_info[0]['contact']; ?>
                                <?php } ?>


                            </address>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped print-font-size">
                            <thead>
                                <tr>
                                    <th width="8%" class="text-center"><?php echo display('sl') ?></th>
                                    <th width="22%" class="text-center"><?php echo display('product_name') ?></th>
                                    <th class="text-center">
                                        <?php  echo display('unit');
                                              ?></th>
                              
                                    <th class="text-right"><?php echo display('quantity') ?></th>
                        
                                    <th class="text-right">
                                        <?php echo display('discount_percentage') .'%'?></th>

                                    <th class="text-right">
                                        <?php echo display('dis_val');?>
                                        </th>

                                    <th class="text-right">
                                        <?php echo display('vat').' %'; ?>
                                    </th>

                                    <th class="text-right">
                                        <?php echo display('vat_val');?>
                                    </th>

                                    <th class="text-right"><?php echo display('rate') ?></th>

                                    <th class="text-right"><?php echo display('ammount') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                 $itemprice = 0;
                                 $totalQuanity = 0;
                                 $vat_amnt = 0;
                                $total_price = 0;
                                foreach($quot_product as $key => $details){
                                    $totalQuanity += $details['used_qty'];
                                    $total_price += $details['total_price'];
                                    $vat_amnt += $details['vat_amnt'];
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $key+1; ?></td>

                                    <td class="text-center">
                                        <div><span class="comp-web"><?php echo $details['product_name']?> -
                                                (<?php echo $details['product_model']?>)</span></div>
                                    </td>

                                    <td class="text-center comp-web">
                                        <div><?php echo $details['unit']?></div>
                                    </td>
                                    
                                    <td align="right" class="comp-web"><?php echo $details['used_qty']?></td>

                                    <td class="comp-web" align="right"><?php echo $details['discount_per']?></td>

                                    <td class="comp-web" align="right">
                                        <nobr>
                                            <?php 
                                                if(!empty($details['discount'])){
                                                    $curicon = $currency;
                                                }else{
                                                    $curicon = '';
                                                }
                                            if($position == 0){
                                            echo  $curicon.' '.html_escape($details['discount']);
                                            }else{
                                            echo html_escape($details['discount']).' '.$curicon;
                                            }
                                                ?>
                                        </nobr>
                                    </td>
                                    <td class="comp-web" align="right">
                                        <nobr>
                                            <?php 
                                                if(!empty($details['vat_per'])){
                                                    $curicon = $currency;
                                                }else{
                                                    $curicon = '';
                                                }
                                            if($position == 0){
                                            echo  html_escape($details['vat_per']);
                                            }else{
                                            echo html_escape($details['vat_per']);
                                            }
                                                ?>
                                        </nobr>
                                    </td>

                                    <td class="comp-web" align="right">
                                        <nobr>
                                            <?php 
                                                if(!empty($details['vat_amnt'])){
                                                    $curicon = $currency;
                                                }else{
                                                    $curicon = '';
                                                }
                                            if($position == 0){
                                            echo  $curicon.' '.html_escape($details['vat_amnt']);
                                            }else{
                                            echo html_escape($details['vat_amnt']).' '.$curicon;
                                            }
                                                ?>
                                        </nobr>
                                    </td>
                                    <td class="comp-web" align="right">
                                        <?php echo (($position == 0) ? $currency.' '.$details['rate'] : $details['rate'].' '. $currency);
                                        $itemprice += $details['rate'] * $details['used_qty'];
                                        
                                        ?>
                                    </td>
                                    <td class="comp-web" align="right">
                                        <?php echo (($position == 0) ? $currency.' '.$details['rate'] * $details['used_qty'] : $details['rate'] * $details['used_qty'].' '. $currency) ?>
                                    </td>
                                </tr>
                                <?php }?>
                                <tr>
                                    <td class="text-left" colspan="3"><b><?php echo display('grand_total') ?>:</b>
                                    </td>

                                    <td align="right"><b><?php echo number_format($totalQuanity,2)?></b></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>

                                    <td colspan="2" align="right">
                                        <b><?php echo (($position == 0) ? $currency.' '.$itemprice  : $itemprice.' '. $currency) ?></b>
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>
                    <div class="row">

                        <div class="col-xs-6">

                            <p></p>
                            <p><strong><?php echo $invoice_details?></strong></p>

                        </div>
                        <div class="col-xs-6 inline-block">

                            <table class="table print-font-size">
                                <?php
                                   
                                    $price_bef_dis = (($subTotal_amount_cal?$subTotal_amount_cal:0) + ($total_discount_cal?$total_discount_cal:0));
                                   
                                        if ($invoice_all_data[0]['total_discount'] != 0) {
                                            ?>
                                <tr>
                                    <th><?php echo 'Total Price Before Discount' ?> :</th>
                                    <td class="text-right">
                                        <?php 
                                        
                                        echo (($position == 0) ? $currency.' '.$itemprice : $itemprice.' '.$currency) ?>
                                    </td>
                                </tr>
                                <?php }?>
                                <?php
                                        if ($invoice_all_data[0]['total_discount'] != 0) {
                                            ?>
                                <tr>
                                    <th><?php echo display('dis_val') ?> :</th>
                                    <td class="text-right">
                                        <?php echo (($position == 0) ? $currency.' '.$total_discount : $total_discount.' '.$currency) ?>
                                    </td>
                                </tr>
                                <?php }?>
                                <tr>
                                    <th><?php echo 'Total Price After Discount' ?> :</th>
                                    <td class="text-right">
                                        <?php
                                          
                                         echo (($position == 0) ? $currency.' '.($total_price - ($total_discount_cal?$total_discount_cal:0)) : ($total_price - ($total_discount_cal?$total_discount_cal:0)).' '.$currency) ?>
                                    </td>
                                </tr>
                        
                                <tr>
                                    <th><?php echo display('vat_val') ?> : </th>
                                    <td class="text-right">
                                        <?php
                                         echo (($position == 0) ? $currency.' '.$quot_main[0]['item_total_vat'] : $quot_main[0]['item_total_vat'].' '.$currency) ?>
                                    </td>
                                </tr>
                                <?php
                          
                                        if ($invoice_all_data[0]['total_tax'] != 0) {
                                            ?>
                                <tr>
                                    <th class="text-left"><?php echo display('tax') ?> : </th>
                                    <td class="text-right">
                                        <?php echo (($position == 0) ? $currency.' '.$total_tax : $total_tax.' '.$currency) ?>
                                    </td>
                                </tr>
                                <?php } ?>
                                <?php if ($invoice_all_data[0]['shipping_cost'] != 0) {
                                            ?>
                                <tr>
                                    <th class="text-left"><?php echo 'Shipping Cost' ?> : </th>
                                    <td class="text-right">
                                        <?php echo (($position == 0) ? $currency.' '.$shipping_cost :  $shipping_cost.' '.$currency) ?>
                                    </td>
                                </tr>
                                <?php } ?>
                                <tr>
                                    <th class="text-left grand_total"><?php echo display('grand_total') ?> :</th>
                                    <td class="text-right grand_total">
                                        <?php
                                        echo (($position == 0) ? $currency.' '.$quot_main[0]['item_total_amount'] : $quot_main[0]['item_total_amount'].' '.$currency) ?>
                                    </td>
                                </tr>
                                <?php if($previous > 0){?>
                                <tr>
                                    <th class="text-left grand_total"><?php echo display('previous'); ?> :</th>
                                    <td class="text-right grand_total">
                                        <?php echo (($position == 0) ? $currency.' '.$previous : $previous.' '.$currency) ?>
                                    </td>
                                </tr>
                                <?php }?>

                                <tr>
                                    <th class="text-left grand_total"><?php echo display('paid_ammount') ?> :</th>
                                    <td class="text-right grand_total">
                                    <?php
                                        echo (($position == 0) ? $currency.' '.$quot_main[0]['item_total_amount'] : $quot_main[0]['item_total_amount'].' '.$currency) ?>
                                    </td>
                                </tr>
                                <?php
                                        if ($invoice_all_data[0]['due_amount'] != 0) {
                                            ?>
                                <tr>
                                    <th class="text-left grand_total"><?php echo display('due') ?> :</th>
                                    <td class="text-right grand_total">
                                        <?php echo (($position == 0) ? $currency.' '.$due_amount : $due_amount.' '.$currency) ?>
                                    </td>
                                </tr>
                                <?php
                                        }
                                        ?>
                            </table>

                        </div>
                    </div>
                    <div class="row margin-top50">
                        <div class="col-sm-4">
                            <div class="inv-footer-left">
                                <?php echo display('received_by') ?>
                            </div>
                        </div>
                        <div class="col-sm-4"></div>
                        <div class="col-sm-4">
                            <div class="inv-footer-right">
                                <?php echo display('authorised_by') ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="panel-footer text-left">
                        <button class="btn btn-info" onclick="printDivnew('printableArea')"><span
                        class="fa fa-print"></span></button>

            </div>
        </div>
    </div>
</div>

