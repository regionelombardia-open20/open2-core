jQuery("body").on("click",".eye-toggle-box", function() {
    jQuery(this).toggleClass("am-eye");

    var input = $(".input-group-addon.eye-toggle-box").siblings('input');

    if (!jQuery(this).hasClass('am-eye')){
        input.prop('type','password');
    } else{
        input.prop('type','text');
    }
});