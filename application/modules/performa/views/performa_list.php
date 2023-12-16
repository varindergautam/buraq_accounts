<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4><?php echo 'Manage Performa'; ?> </h4>
                </div>
            </div>
            <div class="panel-body">
                <div class="table-responsive" id="results">
                    <table id="dataTableExample2" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center"><?php echo display('sl') ?></th>
                                <th class=""><?php echo display('customer_name') ?></th>
                                <th class=""><?php echo 'Performa no'; ?></th>
                                <th class=""><?php echo 'Delivery Date'; ?></th>
                                <th class=""><?php echo display('expiry_date') ?></th>
                                <th class="text-right"><?php echo display('item_total') ?></th>
                                <th class="text-right"><?php echo display('service_total') ?></th>
                                <!-- <th class=""><?php echo display('status') ?></th> -->
                                <th class="text-center"><?php echo display('action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($quotation_list) {
                                $sl = 0;
                                foreach ($quotation_list as $quotation) {
                                    $sl++;


                            ?>
                                    <tr>
                                        <td><?php echo $sl; ?></td>
                                        <td>
                                            <?php echo html_escape($quotation->customer_name); ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo base_url('performa_details/' . $quotation->quotation_id); ?>">
                                                <?php echo html_escape($quotation->quot_no); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                            echo date('m-d-Y', strtotime(html_escape($quotation->quotdate)));
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            echo date('m-d-Y', strtotime(html_escape($quotation->expire_date)));
                                            ?>
                                        </td>
                                        <td class="text-right">
                                            <?php echo html_escape((($position == 0) ? "$currency $quotation->item_total_amount" : "$quotation->item_total_amount $currency")); ?>
                                        </td>
                                        <td class="text-right">
                                            <?php echo html_escape((($position == 0) ? "$currency $quotation->service_total_amount" : "$quotation->service_total_amount $currency")); ?>
                                        </td>
                                        <!-- <td>
                                            <?php
                                            $que_id = $quotation->quotation_main_id;
                                            $invinfo = $this->db->select('*')->from('invoice')->where('invoice_details', $que_id)->get()->row();

                                            $saleOrderInfo = $this->db->select('*')->from('sale_orders')->where('quotation_main_id', $que_id)->get()->row();

                                            $deliveryOrderInfo = $this->db->select('*')->from('delivery')->where('quotation_main_id', $que_id)->get()->row();

                                            if (isset($invinfo) && !empty($invinfo)) {
                                                echo '<a href="' . base_url() . 'invoice_details/' . $invinfo->invoice_id . ' " class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="left" title="" data-original-title="Sale"><i class="fa fa-window-restore" aria-hidden="true"></i></a>' . $invinfo->invoice_id . '';
                                            } else { ?>
                                                <a href="<?php echo base_url() . 'to_sales/' . $quotation->quotation_id; ?>" class="btn btn-success btn-sm" title="" data-original-title="<?php echo display('add_to_invoice') ?> "><?php echo display('add_to_invoice') ?></a>
                                            <?php }

                                            if (isset($deliveryOrderInfo) && !empty($deliveryOrderInfo)) {
                                                echo '<a href=" ' . base_url() . 'delivery_details/' . $deliveryOrderInfo->quotation_id . ' " class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="left" title="" data-original-title="Delivery Note"><i class="fa fa-window-restore" aria-hidden="true"></i></a> ' . $deliveryOrderInfo->quotation_id . '';
                                            } else { ?>
                                                <?php if ($this->permission1->method('to_delivery_note', 'create')->access()) { ?>
                                                    <a href="<?php echo base_url() . 'to_delivery_note/' . $quotation->quotation_id; ?>" class="btn btn-success btn-sm" title="" data-original-title="<?php echo "Delivery Note" ?> "><?php echo "Delivery Note" ?></a>
                                                <?php } ?>
                                            <?php } ?>
                                        </td> -->

                                        <td class="text-center">
                                            <a href="<?php echo base_url() . 'performa_details/' . $quotation->quotation_id; ?>" class="btn btn-info btn-sm" title="<?php echo display('details') ?>" data-original-title="<?php echo display('details') ?> "><i class="fa fa-eye" aria-hidden="true"></i></a>

                                            <a href="<?php echo base_url('performa/performa_download/' . $quotation->quotation_id); ?>" class="btn btn-primary btn-sm" title="<?php echo display('download') ?>" data-original-title="<?php echo display('download') ?> "><i class="fa fa-download" aria-hidden="true"></i></a>
                                        </td>
                                    </tr>
                            <?php
                                }
                            }
                            ?>
                        </tbody>
                        <?php if (empty($quotation_list)) { ?>
                            <tfoot>
                                <tr>
                                    <th colspan="9" class="text-danger text-center"><?php echo display('no_result_found'); ?></th>
                                </tr>
                            </tfoot>
                        <?php } ?>
                    </table>
                    <?php echo $links; ?>
                </div>
            </div>
        </div>
    </div>

</div>