/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/

define(
    [
		'Magento_Payment/js/view/payment/cc-form',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator',
		
           'Magento_Checkout/js/view/payment/default'
		
	],
    function (Component, $) {
        'use strict';
	
        return Component.extend({
            defaults: {
                 template: 'Rudracomputech_Dinnerclub/payment/dinnerclubpay',
				  redirectAfterPlaceOrder: false
            },

            getCode: function() {
                return 'dinnerclubpay';
            },
			
			getData: function() {
				return {
					'method': this.item.method,
					'additional_data': {
						'cc_cid': this.creditCardVerificationNumber(),
						'cc_ss_start_month': this.creditCardSsStartMonth(),
						'cc_ss_start_year': this.creditCardSsStartYear(),
						'cc_type': this.creditCardType(),
						'cc_exp_year': this.creditCardExpYear(),
						'cc_exp_month': this.creditCardExpMonth(),
						'cc_number': this.creditCardNumber(),
						'installments': $('#' + this.getCode() + '_installments').val(),
						
					}
				};
			},
			
            isActive: function() {
                return true;
            },

            validate: function() {
				
               var $form = $('#' + this.getCode() + '-form');
			   return $form.validation() && $form.validation('isValid');
			   
			   
			  
			   
            },
			 afterPlaceOrder: function () {
				 var dinnerpayafterPlaceOrder = window.checkout.baseUrl+"dinnerclub/payment/redirect";
                $.mage.redirect(dinnerpayafterPlaceOrder);
            },
			getPaymentIcon: function(){
                return window.checkoutConfig.payment.icon[this.item.method];
            },
			 getInstructions: function () {
                return window.checkoutConfig.payment.instructions[this.item.method];
            },
			
        });
    },
	
);
