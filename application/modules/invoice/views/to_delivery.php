<script src="<?php echo base_url() ?>my-assets/js/admin_js/json/service_quotation.js.php"></script>
<script src="<?php echo base_url() ?>my-assets/js/admin_js/json/productquotation.js"></script>
<script src="<?php echo base_url() ?>my-assets/js/admin_js/invoice.js" type="text/javascript"></script>

<?php

$readonly = 'readonly';
$disabled = 'disabled';
$card_type = $quot_main[0]['payment_type'];
?>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>Add To Delivery Note </h4>
                </div>
            </div>
            <?php echo form_open_multipart('invoice/invoice/add_to_delivery', array('class' => 'form-vertical', 'id' => 'insert_quotation_to_invoice', 'name' => 'insert_quotation_to_invoice')) ?>
            <input type="hidden" name="type" value="<?php echo $type; ?>">
            <div class="panel-body">
                <div class="form-group row">
                    <div class="col-sm-6">
                        <label for="customer_id" class="col-sm-4 col-form-label"><?php echo display('customer') ?> <i class="text-danger">*</i></label>
                        <div class="col-sm-8">
                            <?php if ($disabled == 'disabled') { ?>
                                <input type="hidden" name="customer_id" value="<?php echo $customer_info[0]['customer_id'] ?>">
                            <?php } ?>
                            <select name="customer_id" id="customer_id" readonly required class="form-control" onchange="get_customer_info(this.value)" data-placeholder="<?php echo display('select_one'); ?>" <?php echo $disabled; ?>>
                                <option value=""></option>
                                <?php
                                foreach ($customers as $customer) {
                                ?>
                                    <option value="<?php echo $customer['customer_id'] ?>" <?php if ($customer_info[0]['customer_id'] == $customer['customer_id']) {
                                                                                                echo 'selected';
                                                                                            } ?>>
                                        <?php echo $customer['customer_name'] ?>
                                    </option>
                                <?php } ?>
                            </select>

                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label for="quotation_no" class="col-sm-4 col-form-label"><?php echo "Quotation No." ?>
                        </label>
                        <div class="col-sm-8">
                            <input type="text" name="quotation_no" id="quotation_no" class="form-control" placeholder="<?php echo "Delivery No." ?>" value="<?php echo $quot_main[0]['invoice_id']; ?>" readonly>
                            <input type="hidden" name="quotation_id" id="quotation_id" class="form-control" value="<?php echo $quot_main[0]['invoice_id']; ?>" readonly>
                            <input type="hidden" name="quotation_main_id" id="quotation_main_id" class="form-control" value="<?php echo $quot_main[0]['invoice_id']; ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-6">
                        <label for="address" class="col-sm-4 col-form-label"><?php echo display('address') ?> <i class="text-danger"></i></label>
                        <div class="col-sm-8">
                            <input type="text" name="address" class="form-control" value="<?php echo $customer_info[0]['customer_address']; ?>" id="address" readonly>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label for="qdate" class="col-sm-4 col-form-label"><?php echo "Quotation Date" ?>
                        </label>
                        <div class="col-sm-8">
                            <input type="text" name="qdate" class="form-control" id="qdate" value="<?php echo $quot_main[0]['date']; ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-6">
                        <label for="mobile" class="col-sm-4 col-form-label"><?php echo display('mobile') ?> <i class="text-danger"></i></label>
                        <div class="col-sm-8">
                            <input type="text" name="mobile" class="form-control" value="<?php echo  $customer_info[0]['customer_mobile'] ?>" id="mobile" readonly>
                        </div>
                    </div>
                    <!-- <div class="col-sm-6">
                        <label for="expiry_date" class="col-sm-4 col-form-label"><?php echo display('expiry_date') ?>
                        </label>
                        <div class="col-sm-8">
                            <input type="text" name="expiry_date" class="form-control" id="expiry_date" value="<?php echo $quot_main[0]['expire_date']; ?>" readonly>
                        </div>
                    </div> -->


                </div>

                <div class="form-group row">
                    <div class="col-sm-12">
                        <label for="details" class="col-sm-2 col-form-label"><?php echo display('details') ?> <i class="text-danger"></i></label>
                        <div class="col-sm-10">
                            <textarea name="details" class="form-control" id="details" <?php echo $readonly; ?>><?php echo $quot_main[0]['invoice_details']; ?></textarea>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-sm-12">
                        <?php
                        $amount = 0;
                        if (!empty($quot_product[0]['product_name'])) {
                        ?>
                            <div class="table-responsive margin-top10">
                                <table class="table table-bordered table-hover" id="normalinvoice">
                                    <thead>
                                        <tr>
                                            <th class="text-center product_field"><?php echo display('item_information') ?>
                                                <i class="text-danger">*</i>
                                            </th>
                                            <th class="text-center"><?php echo display('item_description') ?></th>
                                            <th class="text-center"><?php echo display('unit') ?></th>
                                            <th class="text-center"><?php echo display('quantity') ?> <i class="text-danger">*</i></th>
                                            <th class="text-center"><?php echo display('rate') ?> <i class="text-danger">*</i></th>

                                            <?php if ($discount_type == 1) { ?>
                                                <th class="text-center invoice_fields">
                                                    <?php echo display('discount_percentage') ?> %</th>
                                            <?php } elseif ($discount_type == 2) { ?>
                                                <th class="text-center invoice_fields"><?php echo display('discount') ?> </th>
                                            <?php } elseif ($discount_type == 3) { ?>
                                                <th class="text-center invoice_fields"><?php echo display('fixed_dis') ?> </th>
                                            <?php } ?>
                                            <th class="text-center"><?php echo display('dis_val') ?> </th>
                                            <th class="text-center"><?php echo display('vat') . ' %' ?> </th>
                                            <th class="text-center"><?php echo display('vat_val') ?> </th>
                                            <th class="text-center invoice_fields"><?php echo display('total') ?>
                                            </th>
                                            <th class="text-center"><?php echo display('action') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="addinvoiceItem">
                                        <?php
                                        $sl = 1;
                                        $amount = 0;
                                        foreach ($quot_product as $item) {

                                            $product_id = $item['product_id'];
                                            $this->db->select('SUM(a.quantity) as total_purchase');
                                            $this->db->from('product_purchase_details a');
                                            $this->db->where('a.product_id', $product_id);
                                            $total_purchase = $this->db->get()->row();

                                            $this->db->select('SUM(b.quantity) as total_sale');
                                            $this->db->from('invoice_details b');
                                            $this->db->where('b.product_id', $product_id);
                                            $total_sale = $this->db->get()->row();
                                            $available_quantity = $total_purchase->total_purchase - $total_sale->total_sale;

                                        ?>
                                            <tr>
                                                <td class="product_field">
                                                    <input type="text" name="product_name" required onkeypress="invoice_productList(<?php echo $sl; ?>);" class="form-control productSelection" placeholder='<?php echo display('product_name') ?>' value="<?php echo $item['product_name'] . ' (' . $item['product_model'] . ')'; ?>" id="product_name_<?php echo $sl; ?>" tabindex="5" <?php echo $readonly; ?>>

                                                    <input type="hidden" class="autocomplete_hidden_value product_id_<?php echo $sl; ?>" value="<?php echo $item['product_id']; ?>" name="product_id[]" id="SchoolHiddenId" />

                                                    <input type="hidden" class="baseUrl" value="<?php echo base_url(); ?>" />
                                                </td>
                                                <td>
                                                    <input type="text" name="desc[]" class="form-control text-right " value="<?php echo $item['description']; ?>" tabindex="6" <?php echo $readonly; ?> />
                                                <td>
                                                    <input name="" id="" class="form-control text-right unit_<?php echo $sl; ?> valid" value="<?php echo $item['unit']; ?>" aria-invalid="false" type="text" readonly>
                                                </td>
                                                <td>
                                                    <input type="text" required name="product_quantity[]" onkeyup="quantity_calculate(<?php echo $sl; ?>);" onchange="quantity_calculate(<?php echo $sl; ?>);" class="total_qntt_<?php echo $sl; ?> form-control text-right" id="total_qntt_<?php echo $sl; ?>" placeholder="0.00" min="0" tabindex="8" value="<?php echo $item['quantity']; ?>" <?php echo $readonly; ?> />
                                                </td>
                                                <td class="invoice_fields">
                                                    <input type="text" name="product_rate[]" required id="price_item_<?php echo $sl; ?>" class="price_item<?php echo $sl; ?> price_item form-control text-right" tabindex="9" onkeyup="quantity_calculate(<?php echo $sl; ?>);" onchange="quantity_calculate(<?php echo $sl; ?>);" value="<?php echo $item['rate']; ?>" placeholder="0.00" min="0" <?php echo $readonly; ?> />

                                                    <input type="hidden" name="supplier_price[]" id="supplier_price_<?php echo $sl; ?>" value="<?php echo $item['supplier_rate']; ?>">
                                                </td>
                                                <!-- Discount -->
                                                <td>
                                                    <input type="text" name="discount[]" onkeyup="quantity_calculate(<?php echo $sl; ?>);" onchange="quantity_calculate(<?php echo $sl; ?>);" id="discount_<?php echo $sl; ?>" class="form-control text-right" min="0" tabindex="10" placeholder="0.00" value="<?php echo $item['discount_per']; ?>" <?php echo $readonly; ?> />
                                                    <input type="hidden" value="<?php echo $discount_type; ?>" name="discount_type" id="discount_type_<?php echo $sl; ?>">

                                                </td>

                                                <td>
                                                    <input type="text" name="discountvalue[]" id="discount_value_<?php echo $sl; ?>" class="form-control text-right" min="0" value="<?php echo $item['discount']; ?>" tabindex="18" placeholder="0.00" readonly />
                                                </td>

                                                <!-- VAT  -->
                                                <td>
                                                    <input type="text" name="vatpercent[]" onkeyup="quantity_calculate(<?php echo $sl; ?>);" onchange="quantity_calculate(<?php echo $sl; ?>);" id="vat_percent_<?php echo $sl; ?>" class="form-control text-right" min="0" value="<?php echo $item['vat_per']; ?>" tabindex="19" placeholder="0.00" <?php echo $readonly; ?> />


                                                </td>
                                                <td>
                                                    <input type="text" name="vatvalue[]" id="vat_value_<?php echo $sl; ?>" class="form-control text-right total_vatamnt" value="<?php echo $item['vat_amnt']; ?>" min="0" tabindex="20" placeholder="0.00" <?php echo $readonly; ?> />
                                                </td>
                                                <!-- VAT end -->


                                                <td class="invoice_fields">
                                                    <input class="total_price form-control text-right" type="text" name="total_price[]" id="total_price_<?php echo $sl; ?>" value="<?php echo $item['total_price']; ?>" readonly="readonly" />
                                                </td>

                                                <td>
                                                    <?php if ($disabled != 'disabled') { ?>
                                                        <button class='btn btn-danger' type='button' onclick='deleteRow(this)'><i class='fa fa-close'></i></button>
                                                    <?php } ?>
                                                    <!-- Discount calculate start-->
                                                    <input type="hidden" id="total_discount_<?php echo $sl; ?>" class="" value="<?php echo $item['discount']; ?>" />
                                                    <input type="hidden" id="all_discount_<?php echo $sl; ?>" class="total_discount dppr" name="discount_amount[]" value="<?php echo $item['discount']; ?>" />
                                                    <!-- Discount calculate end -->


                                                </td>
                                            </tr>
                                        <?php $sl++;
                                        } ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>

                                            <td class="text-right" colspan="11">
                                                <b><?php echo display('invoice_discount') ?>:</b>
                                            </td>
                                            <td class="text-right">
                                                <input type="text" onkeyup="quantity_calculate(<?php echo $sl; ?>);" onchange="quantity_calculate(<?php echo $sl; ?>);" id="invoice_discount" class="form-control text-right total_discount" name="invoice_discount" placeholder="0.00" value="<?php echo $quot_main[0]['invoice_discount']; ?>" tabindex="13" <?php echo $readonly; ?> />
                                                <input type="hidden" id="txfieldnum" value="<?php echo $taxnumber; ?>">
                                            </td>

                                            <?php if ($disabled != 'disabled') { ?>
                                                <td><a id="add_invoice_item" class="btn btn-info" name="add-invoice-item" onClick="addInputField('addinvoiceItem');" tabindex="11"><i class="fa fa-plus"></i></a></td>
                                            <?php } ?>
                                        </tr>
                                        <tr>
                                            <td class="text-right" colspan="11">
                                                <b><?php echo display('total_discount') ?>:</b>
                                            </td>
                                            <td class="text-right">
                                                <input type="text" id="total_discount_ammount" class="form-control text-right" name="total_discount" value="<?php echo $quot_main[0]['total_discount']; ?>" readonly="readonly" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-right" colspan="11">
                                                <b><?php echo display('ttl_val') ?>:</b>
                                            </td>
                                            <td class="text-right">
                                                <input type="text" id="total_vat_amnt" class="form-control text-right" name="total_vat_amnt" value="<?php echo $quot_main[0]['total_vat_amnt']; ?>" readonly="readonly" />
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-right" colspan="11"><b>Paid Amount:</b></td>
                                            <td class="text-right">
                                                <input type="hidden" name="baseUrl" class="baseUrl" value="<?php echo base_url(); ?>">
                                                <?php if ($card_type == 0) {
                                                ?>
                                                    <input type="text" id="paidAmount" onkeyup="invoice_paidamount();" class="form-control text-right" name="paid_amount" placeholder="0.00" tabindex="22" value="0" data-listener-added_fa723d9e="true" data-listener-added_4789e008="true" readonly>

                                                <?php
                                                } else { ?>
                                                    <input type="text" id="paidAmount" onkeyup="invoice_paidamount();" class="form-control text-right" name="paid_amount" placeholder="0.00" tabindex="22" value="<?php echo $quot_main[0]['total_amount']; ?>" data-listener-added_fa723d9e="true" data-listener-added_4789e008="true" readonly>
                                                <?php } ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-right" colspan="11"><b><?php echo 'due'; ?>:</b></td>
                                            <td class="text-right">
                                                <?php if ($card_type == 0) {
                                                ?>
                                                    <input type="text" id="dueAmmount" class="form-control text-right" name="due_amount" value="<?php echo $quot_main[0]['due_amount']; ?>" readonly="readonly" />
                                                <?php
                                                } else { ?>
                                                    <input type="text" id="dueAmmount" class="form-control text-right" name="due_amount" value="0" readonly="readonly" />
                                                <?php } ?>

                                            </td>
                                        </tr>

                                        <tr>
                                            <td colspan="11" class="text-right"><b><?php echo display('grand_total') ?>:</b>
                                            </td>
                                            <td class="text-right">
                                                <input type="text" id="grandTotal" class="form-control text-right grandTotalamnt" name="grand_total_price" value="<?php echo $quot_main[0]['total_amount']; ?>" readonly="readonly" />
                                            </td>
                                        </tr>


                                    </tfoot>
                                </table>
                            </div>
                        <?php } ?>


                    </div>
                    <input type="hidden" name="finyear" value="<?php echo financial_year(); ?>">
                    <div class="col-md-12">
                        <p hidden id="old-amount"><?php echo 0; ?></p>
                        <p hidden id="pay-amount"><?php echo $quot_main[0]['total_amount']; ?></p>
                        <p hidden id="change-amount"></p>
                        <div class="col-sm-6 table-bordered p-20">
                            <div id="adddiscount" class="display-none">
                                <div class="row no-gutters">
                                    <div class="form-group col-md-6">
                                        <label for="payments" class="col-form-label pb-2"><?php echo display('payment_type'); ?></label>

                                        <?php $card_type = $quot_main[0]['payment_type'];
                                        if (isset($card_type)) {
                                            $cardTypeDisable = 'disabled';
                                        ?>
                                            <input type="hidden" name="multipaytype[]" value="<?php echo $card_type; ?>">
                                        <?php
                                        } else {
                                            $cardTypeDisable = '';
                                        }

                                        echo form_dropdown('multipaytype[]', $all_pmethod, (isset($card_type) ? $card_type : null), 'class="card_typesl postform resizeselect required form-control "  id="cardTypeSelect"' . $cardTypeDisable) ?>

                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="4digit" class="col-form-label pb-2"><?php echo display('paid_amount'); ?></label>

                                        <?php 
                                        if($card_type == 0) {
                                        ?>
                                        <input type="text" id="pamount_by_method" class="form-control number pay " name="pamount_by_method[]" onkeyup="changedueamount()" value="0" placeholder="0" readonly />
                                        <?php } else  { ?>
                                        <input type="text" id="pamount_by_method" class="form-control number pay " name="pamount_by_method[]" onkeyup="changedueamount()" value="<?php echo $quot_main[0]['total_amount']; ?>" placeholder="0" readonly />
                                        <?php } ?>

                                    </div>
                                </div>

                                <div class="" id="add_new_payment">



                                </div>
                                <div class="form-group text-right">
                                    <div class="col-sm-12 pr-0">

                                        <button type="button" id="add_new_payment_type" class="btn btn-success w-md m-b-5"><?php echo display('new_p_method'); ?></button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>


                <div class="form-group row text-right">
                    <label for="example-text-input" class="col-sm-4 col-form-label"></label>
                    <div class="col-sm-12">

                        <input type="submit" id="add_invoice" class="btn btn-success btn-large" name="add-quotation" value="<?php echo 'Add To Delivery'; ?>" />

                    </div>
                </div>
            </div>
            <?php echo form_close() ?>
        </div>
    </div>
</div>

<script src="<?php echo base_url() ?>my-assets/js/admin_js/json/quotation.js"></script>
<script>
    $(document).ready(function() {
        $('#cardTypeSelect').change(function() {
            var selectedValue = $(this).val();

            if (selectedValue === '0') {
                $('#paidAmount').val('0');
            } else {
                $grandTotal = $('#grandTotal').val();
                $('#paidAmount').val($grandTotal);
            }
            invoice_paidamount();
            changedueamount();
        });
    });
</script>