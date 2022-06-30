
/**
 * 
 * @param {type} options
 * @returns {checkSessionValidation}
 */
var checkSessionValidation = function(options){
 
    var vars = {
        yiiVars  : null
    };
 
    var root = this;
    var notify = true;
 
    /*
     * Constructor
     */
    this.construct = function(options){
        $.extend(vars , options);
        $(window).bind('beforeunload', root.windowUnload);
    };
 
    /**
     * 
     * 
     */
    this.windowUnload = function (){
        notify = false;
    }
 
    /**
     * 
     * 
     */
    this.checkVariableValue = function(){
        //console.log(vars.yiiVars);
        //ajaxCheckSession();
    };
 
    /**
     * 
     * 
     */
    var ajaxCheckSession = function() {
       console.log('ajaxCheckSession method');
       $.ajax({
            url: '/admin/security/check-session-scope',
            success: function(newVal) {
                if (newVal != vars.yiiVars.idScope && notify){
                    showModalMessage('Session values has Changed.',0);
                }else{
                    setTimeout(root.checkVariableValue, 5000);
                }
            }
         });
    };
 
 
    var showModalMessage = function (message, type) {

        BootstrapDialog.show({
          title: (type == 1 ? 'Warning' : 'Error') + ' message',
          message: message,
          buttons: [{
            label: 'Ok',
            cssClass: (type == 1 ? 'btn btn-warning' : 'btn btn-danger'),
            action: function(dialog) {
              dialog.close();
              window.location.href = '/admin/security/reset-dashboard-by-scope';
            }
          }]
        });
      }
      
    this.construct(options);
 
};
 
/**
 * 
 * @type checkSessionValidation
 */
var checkObj = new checkSessionValidation({ yiiVars : yiiOptions });
checkObj.checkVariableValue();