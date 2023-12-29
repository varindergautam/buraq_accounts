<?php
$user_type = $this->session->userdata('user_type');
$user_id = $this->session->userdata('id');
?>
<!-- <link href="<?php echo base_url('assets/custom/quotation.css') ?>" rel="stylesheet" type="text/css" />
 -->

<style>
    .customer_detail td {
        width: 50%;
    }

    .customer_detail_1 th.heading {
        width: 100px;
    }

    .customer_detail_1 th.colon {
        width: 30px;
        border: 0px solid;
        text-align: center;
    }

    .customer_detail_1 td.data-text {
        width: 65%;
    }

    .customer_detail_2 th.heading {
        width: 150px;
    }

    .customer_detail_2 th.colon {
        width: 30px;
        border: 0px solid;
        text-align: center;
    }

    .customer_detail_2 td.data-text {
        /* width: 65%; */
    }

    .table {
        border-collapse: collapse;
        border-color: #ccc;
        margin: 0px;
    }
</style>
<div id="printableArea" style="padding: 0 10px;">
    <table style="width: 100%;" class="print-font-size">
        <tr>
            <td style="width: 200px;">logo</td>
            <td style="text-align: center;">
                <h3 style="color:#1a3f85;font-weight:bold;">SHAMIS MOHAMED GENERAL TRADING LLC</h3>
                <p class="" style="font-weight:bold;">Near Hor Al Anz Post Office Opp,Dubai Municipality <br>
                    Dubai-U.A.E,P O BOX.21099 <br>
                    Tel: +971 42964336 Mob:+971 521060170
                </p>

                <h3 style="font-weight:bold;">CUSTOMER QUOTATION</h3>
                <p style="font-weight:bold;">TRN: 100433744800003</p>
            </td>
        </tr>
    </table>
    <table width="100%" border="1px solid" class="table customer_detail print-font-size">
        <tr>
            <td>
                <table width="100%" class="customer_detail_1">
                    <tr>
                        <th class="heading">
                            <p>Party Name</p>
                        </th>
                        <th class="colon">
                            <p>:</p>
                        </th>
                        <td class="data-text">
                            <p>NEW MISK BUILDING MAITANANCE LLC.</p>
                        </td>
                    </tr>

                    <tr>
                        <th class="heading">
                            <p>Address</p>
                        </th>
                        <th class="colon">
                            <p>:</p>
                        </th>
                        <td class="data-text">
                            <p>P.O.BOX : 50484,OFFICE 512</p>
                        </td>
                    </tr>

                    <tr>
                        <th class="heading">
                            <p>City</p>
                        </th>
                        <th class="colon">
                            <p>:</p>
                        </th>
                        <td class="data-text">
                            <p>OUD METHA,BUR DUBAI</p>
                        </td>
                    </tr>

                    <tr>
                        <th class="heading">
                            <p>Contact</p>
                        </th>
                        <th class="colon">
                            <p>:</p>
                        </th>
                        <td class="data-text">
                            <p>Mr Joshi - +971566986306</p>
                        </td>
                    </tr>

                    <tr>
                        <th class="heading">
                            <p>Emirate</p>
                        </th>
                        <th class="colon">
                            <p>:</p>
                        </th>
                        <td class="data-text">
                            <p>Dubai</p>
                        </td>
                    </tr>
                    <tr>
                        <th class="heading">
                            <p>Country</p>
                        </th>
                        <th class="colon">
                            <p>:</p>
                        </th>
                        <td class="data-text">
                            <p>UAE</p>
                        </td>
                    </tr>


                    <tr>
                        <th class="heading">
                            <p>TRN</p>
                        </th>
                        <th class="colon">
                            <p>:</p>
                        </th>
                        <td class="data-text">
                            <p>100390022000003</p>
                        </td>
                    </tr>

                </table>
            </td>
            <td style="vertical-align: top;">
                <table width="100%" class="customer_detail_2">
                    <tr>
                        <th class="heading">
                            <p>Customer Qtn No</p>
                        </th>
                        <th class="colon">
                            <p>:</p>
                        </th>
                        <td class="data-text">
                            <p>SMQ/10038</p>
                        </td>
                    </tr>

                    <tr>
                        <th class="heading">
                            <p>Voucher Date</p>
                        </th>
                        <th class="colon">
                            <p>:</p>
                        </th>
                        <td class="data-text">
                            <p>18-Dec-2023</p>
                        </td>
                    </tr>

                    <tr>
                        <th class="heading">
                            <p>Reference</p>
                        </th>
                        <th class="colon">
                            <p>:</p>
                        </th>
                        <td class="data-text">
                            <p>B503 COMMON AREA</p>
                        </td>
                    </tr>

                    <tr>
                        <th class="heading">
                            <p>Salesman</p>
                        </th>
                        <th class="colon">
                            <p>:</p>
                        </th>
                        <td class="data-text">
                            <p></p>
                        </td>
                    </tr>

                    <tr>
                        <th class="heading">
                            <p>Payment Terms</p>
                        </th>
                        <th class="colon">
                            <p>:</p>
                        </th>
                        <td class="data-text">
                            <p></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <?php
    $amount = 0;
    if (!empty($quot_product[0]['product_name'])) {
    ?>
        <table width="100%" border="1px solid" style="margin-top: 20px;;" class="table">

            <tr>
                <th width="8%" class="text-center"><?php echo display('sl') ?></th>
                <th width="22%" class="text-center"><?php echo display('item') ?></th>
                <th class="text-center"><?php echo display('qty') ?></th>
                <th class="text-center"><?php echo display('price') ?></th>
                <?php if ($discount_type == 1) { ?>
                    <th class="text-center"><?php echo display('discount_percentage') ?> %
                    </th>
                <?php } elseif ($discount_type == 2) { ?>
                    <th class="text-center"><?php echo display('discount') ?> </th>
                <?php } elseif ($discount_type == 3) { ?>
                    <th class="text-center"><?php echo display('fixed_dis') ?> </th>
                <?php } ?>
                <th class="text-center "><?php echo display('dis_val') ?> </th>
                <?php if (!empty($quot_product[0]['vat_per'])) { ?>
                    <th class="text-center "><?php echo display('vat') . ' %' ?> </th>
                <?php } ?>
                <?php if (!empty($quot_product[0]['vat_amnt'])) { ?>
                    <th class="text-center "><?php echo display('vat_val') ?></th>
                <?php } ?>
                <th class="text-center"><?php echo display('total') ?></th>
            </tr>



            <?php
            $sl = 1;
            $amount = 0;
            foreach ($quot_product as $item) {

            ?>
                <tr>
                    <td class="text-center"><?php echo $sl ?></td>
                    <td class="text-center">
                        <span class="comp-web"> <?php echo $item['product_name'] . ' (' . $item['product_model'] . ')'; ?></span>
                    </td>
                    <td class="text-center comp-web"><?php echo $item['used_qty']; ?></td>
                    <td class="text-center comp-web">
                        <?php
                        $rate = $item['rate'];
                        echo (($position == 0) ? "$currency $rate" : "$rate $currency");
                        ?>
                    </td>
                    <td class="text-center comp-web">
                        <?php
                        $itemdiscountper = $item['discount_per'];
                        echo (!empty($itemdiscountper) ? $itemdiscountper : '0.00');
                        ?>
                    </td>
                    <td class="text-center comp-web">
                        <?php
                        $discount = $item['discount'];
                        echo (!empty($discount) ? $discount : '');
                        ?>
                    </td>
                    <?php if (!empty($item['vat_per'])) { ?>
                        <td class="text-center comp-web">
                            <?php
                            $vat_per = $item['vat_per'];
                            echo (!empty($vat_per) ? $vat_per : '');
                            ?>
                        </td>
                    <?php } ?>
                    <?php if (!empty($item['vat_amnt'])) { ?>
                        <td class="text-center comp-web">
                            <?php
                            $vat_amnt = $item['vat_amnt'];
                            echo (!empty($vat_amnt) ? $vat_amnt : '');
                            ?>
                        </td>
                    <?php } ?>
                    <td class="text-center comp-web">
                        <?php
                        $amount += $item['total_price'];
                        $rate_total = $item['total_price'];
                        echo (($position == 0) ? "$currency $rate_total" : "$rate_total $currency");
                        ?>
                    </td>
                </tr>
            <?php
                $sl++;
            }
            ?>
            <tr>
                <td>1</td>
                <td>
                    <table class="table">
                        <tr>
                            <td>1</td>
                            <td>12</td>
                        </tr>
                    </table>
                </td>
            </tr>

        </table>
    <?php } ?>
</div>
<div class="panel-footer text-left" style="margin-top: 20px;;">
    <a class="btn btn-danger" href="<?php echo base_url('Cquotation/manage_quotation'); ?>"><?php echo display('cancel') ?></a>
    <button class="btn btn-info" onclick="printDivnew('printableArea')"><span class="fa fa-print"></span></button>
</div>