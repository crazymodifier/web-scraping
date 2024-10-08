jQuery(document).ready(function ($){
    jQuery('.datatable-table').dataTable();
})
jQuery(document).on('click','#save-add-vendor', function(e){
    e.preventDefault();
    var data = {
        'action': 'save_wbs_vendor_details',
        'data': jQuery('form#wbs-add-vendor').serialize()
    };

    jQuery.ajax({
        type: "post",
        url: ajax_object.ajax_url,
        data: data,
        dataType: "json",
        success: function (res) {
            console.log(res);
            jQuery('#submit-add-vendor').click();
        }
    });
    // return false;
})