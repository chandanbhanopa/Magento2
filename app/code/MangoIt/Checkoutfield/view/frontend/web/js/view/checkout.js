define(
    [
    'jquery',
    'customerAttributesCheckoutGuest'
    ],
    function (
        $,
        Component
        ) {
        return Component.extend({
            defaults: {
                template: 'MangoIt_Checkoutfield/checkout'
            },
            initialize: function () {
                this._super();
                this.loadcustom();

                
            },
            loadcustom: function () {
                var self = this;
                $(document).on('change', "select[name='custom_attributes[account_number]']", function (e) {

                self.removeOptions($("select[name='custom_attributes[account_number]']").val());
                if(!$('option[value="showfield"]').length) {
                    $("select[name='custom_attributes[account_number]']").prepend("<option value='1' selected='selected'>Top Option</option>");
                    $("select[name='custom_attributes[account_number]']").append("<option value='showfield'>Enter account number</option>");
                    }
                });
            },
            removeOptions: function (str) {
                if(str == "showfield"){
                    var block = 'block';
                } else {
                    var block = 'none';
                }
                $("#account-number").attr("value","");      
                $("#account-number").css("display",block);    
            },
        });
    });
