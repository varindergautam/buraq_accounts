<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-bd lobidrag">
            <div class="panel-heading py-2">
                <div class="panel-title">
                    <span><?php echo display('predefined_accounts') ?></span>

                </div>

            </div>
            <div class="panel-body">
                <div class="col-md-12">
                <?php echo form_open('account/accounts/predefined_accounts')?>
                <?php 
                if($fieldNames){
                  
                     foreach($fieldNames as $key=>$fields){ ?>
                <?php if($fields != 'id'){ ?>
                <div class="row form-group">
                    <label for="head_code" class="font-weight-600 col-sm-2"> <?php echo $fields ;?><i
                            class="text-danger">*</i></label>

                    <div class="col-sm-3">
                        <?php echo  form_dropdown($fields, $allheads, ($filedvalues?$filedvalues->$fields:''), 'class="form-control select2" id="'.$fields.'"') ?>
                    </div>
                    <?php  if ($key==1) {
                    ?>
                    <div class="col-sm-6">
                    <span class="newtooltiop" data-toggle="tooltip" data-html="true" data-placement="right" title="** Warning: Please don't change any Predefined Account 
                    if you are not sure about your accounts otherwise you will get wrong accounting report in your system.**">
                    <i class="color-red fa fa-question-circle fa-2x" aria-hidden="true"></i>
                </span>
                    </div>
                    <?php  }
                    ?>
                </div>
                <?php } }}?>
                <div class="row form-group">

                    <div class="col-sm-5 text-right">
                        <?php if($this->permission1->method('predefined_accounts','update')->access()) { ?>
                        <button type="submit" class="btn btn-success"><?php echo display('submit');?></button>
                        <?php } ?>

                    </div>
                    <div class="col-sm-7">
                    </div>
                </div>
                <?php echo form_close()?>
                </div>
            </div>


        </div>
    </div>
</div>

<style>
    .tooltip-inner {
  font-size: 14px;
  max-width: 450px !important; 
  text-align: left;
}
 </style>