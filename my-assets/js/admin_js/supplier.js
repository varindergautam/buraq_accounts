function get_supplier_info(t) {
    var csrf_test_name = $('[name="csrf_test_name"]').val();
    var base_url = $("#base_url").val();
    $.ajax({
        url: base_url + "supplier/supplier/get_supplier_info",
        type: 'POST',
        data: {supplier_id: t,csrf_test_name:csrf_test_name},
        success: function (r) {
            r = JSON.parse(r);
            $("#address").val(r.address);
            $("#mobile").val(r.mobile);
            $("#website").val(r.emailnumber);
            
        }
    });
   
}