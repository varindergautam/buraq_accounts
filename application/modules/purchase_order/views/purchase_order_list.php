<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4><?php echo 'Manage purchase_order'; ?> </h4>
                </div>
            </div>
            <div class="panel-body">
                <div class="table-responsive" id="results">
                    <table id="dataTableExample2" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center"><?php echo display('sl') ?></th>
                                <th class=""><?php echo display('supplier_name') ?></th>
                                <th class=""><?php echo 'Quotation no'; ?></th>
                                <th class=""><?php echo 'purchase_order no'; ?></th>
                                <th class=""><?php echo 'Delivery Date'; ?></th>
                                <th class=""><?php echo display('expiry_date') ?></th>
                                <th class="text-right"><?php echo display('item_total') ?></th>
                                <th class="text-right"><?php echo display('service_total') ?></th>
                                <th class=""><?php echo display('status') ?></th>
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
                                            <?php echo html_escape($quotation->supplier_name); ?>
                                        </td>
                                        <td>
                                            <?php echo html_escape($quotation->quotation_main_id); ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo base_url('purchase_order_details/' . $quotation->quotation_id); ?>">
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
                                        <td>

                                        </td>

                                        <td class="text-center">
                                            <a href="<?php echo base_url() . 'purchase_order_details/' . $quotation->quotation_id; ?>" class="btn btn-info btn-sm" title="<?php echo display('details') ?>" data-original-title="<?php echo display('details') ?> "><i class="fa fa-eye" aria-hidden="true"></i></a>

                                            <a href="<?php echo base_url('purchase_order/purchase_order_download/' . $quotation->quotation_id); ?>" class="btn btn-primary btn-sm" title="<?php echo display('download') ?>" data-original-title="<?php echo display('download') ?> "><i class="fa fa-download" aria-hidden="true"></i></a>

                                            <?php

                                        
                                            if ($this->permission1->method('manage_purchase_order', 'update')->access()) { ?>
                                                <a href="<?php echo base_url() . 'purchase_order/edit_purchase_order/' . $quotation->quotation_id; ?>" class="btn btn-primary btn-sm" title="<?php echo display('update') ?>" data-original-title="<?php echo display('update') ?> "><i class="fa fa-edit" aria-hidden="true"></i></a>
                                            <?php }
                                            ?>
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