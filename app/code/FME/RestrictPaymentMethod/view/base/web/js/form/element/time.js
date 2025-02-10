define([
    'jquery',
    'Magento_Ui/js/form/element/abstract'
    ], function ($,AbstractElement) {
        'use strict';

        return AbstractElement.extend({
            defaults: {
                elementTmpl: 'FME_RestrictPaymentMethod/form/time'
            },

            initialize: function () {
                this._super();
                this.openhours = '00';
                this.openminutes = '00';
                this.closehours = '00';
                this.closeminutes = '00';
                // this.status='Disable';
                this.observe(['openhours', 'openminutes', 'closehours','closeminutes']);
                var value = this.value();
                this.openhours(value.slice(0,2));
                this.openminutes(value.slice(2));
                this.closehours(value.slice(0,2));
                this.closeminutes(value.slice(2));
                // this.status(value.slice(2));
            },

            userChanges: function () {
                this._super();
                this.value(this.openhours() + this.openminutes() + this.closehours() + this.closeminutes());
            },
            openhoursOpts: (function () {
                var opts = [];
                for (var i=0; i<24; i++) {
                    opts.push({
                        label: (('0'+ i).slice(-2)).toString(),
                        value: ('0' + i).slice(-2)
                    })
                }
                return opts;
            })(),
            openminutesOpts: (function () {
                var opts = [];
                for (var i=0; i<60; i++) {
                    opts.push({
                        label: ('0' + i).slice(-2),
                        value: ('0' + i).slice(-2)
                    })
                }
                return opts;
            })(),
            closehoursOpts: (function () {
                var opts = [];
                for (var i=0; i<24; i++) {
                    opts.push({
                        label: (('0'+ i).slice(-2)).toString(),
                        value: ('0' + i).slice(-2)
                    })
                }
                return opts;
            })(),
            closeminutesOpts: (function () {
                var opts = [];
                for (var i=0; i<60; i++) {
                    opts.push({
                        label: ('0' + i).slice(-2),
                        value: ('0' + i).slice(-2)
                    })
                }
                return opts;
            })(),
            statusOpts: (function () {
                var opts = [];
                opts.push({
                    label: ('Disable'),
                    value: ('0').slice(-2)
                })
                opts.push({
                    label: ('Enable'),
                    value: ('1').slice(-2)
                })
                return opts;
            })(),
            selectOption: function(e){
                    var timing=new Array();
                // if($('div[data-index="after_monday"] input[name="after_monday"]').val() !=='')
                // {
                //     timing=$('div[data-index="after_monday"] input[name="after_monday"]').val().match(/.{1,2}/g);
                //     if($('div[data-index="dayandtime"] div[data-index="monday"] select').val() == '0' && $('div[data-index="dayandtime"] div[data-index="monday"] select:eq(1)').prop('disabled'))
                //         $('div[data-index="dayandtime"] div[data-index="monday"] select').val(1);
                //     $('div[data-index="dayandtime"] div[data-index="monday"] select:eq(1)').val(timing[0]);
                //     $('div[data-index="dayandtime"] div[data-index="monday"] select:eq(2)').val(timing[1]);
                //     $('div[data-index="dayandtime"] div[data-index="monday"] select:eq(3)').val(timing[2]);
                //     $('div[data-index="dayandtime"] div[data-index="monday"] select:eq(4)').val(timing[3]);
                // }
                // if($('div[data-index="after_tuesday"] input[name="after_tuesday"]').val() !=='')
                // {
                //     timing=$('div[data-index="after_tuesday"] input[name="after_tuesday"]').val().match(/.{1,2}/g);
                //     if($('div[data-index="dayandtime"] div[data-index="tuesday"] select:eq(1)').prop('disabled'))
                //     $('div[data-index="dayandtime"] div[data-index="tuesday"] select').val(1);
                //     $('div[data-index="dayandtime"] div[data-index="tuesday"] select:eq(1)').val(timing[0]);
                //     $('div[data-index="dayandtime"] div[data-index="tuesday"] select:eq(2)').val(timing[1]);;
                //     $('div[data-index="dayandtime"] div[data-index="tuesday"] select:eq(3)').val(timing[2]);
                //     $('div[data-index="dayandtime"] div[data-index="tuesday"] select:eq(4)').val(timing[3]);
                // }
                // if($('div[data-index="after_wednesday"] input[name="after_wednesday"]').val() !=='')
                // {
                //     timing=$('div[data-index="after_wednesday"] input[name="after_wednesday"]').val().match(/.{1,2}/g);
                //     if($('div[data-index="dayandtime"] div[data-index="wednesday"] select').val() == '0' && $('div[data-index="dayandtime"] div[data-index="wednesday"] select:eq(1)').prop('disabled'))
                //     $('div[data-index="dayandtime"] div[data-index="wednesday"] select').val(1);
                //     $('div[data-index="dayandtime"] div[data-index="wednesday"] select:eq(1)').val(timing[0]);
                //     $('div[data-index="dayandtime"] div[data-index="wednesday"] select:eq(2)').val(timing[1]);;
                //     $('div[data-index="dayandtime"] div[data-index="wednesday"] select:eq(3)').val(timing[2]);
                //     $('div[data-index="dayandtime"] div[data-index="wednesday"] select:eq(4)').val(timing[3]);
                // }
                // if($('div[data-index="after_thursday"] input[name="after_thursday"]').val() !=='')
                // {
                //     timing=$('div[data-index="after_thursday"] input[name="after_thursday"]').val().match(/.{1,2}/g);
                //     if($('div[data-index="dayandtime"] div[data-index="thursday"] select').val() == '0' && $('div[data-index="dayandtime"] div[data-index="thursday"] select:eq(1)').prop('disabled'))
                //     $('div[data-index="dayandtime"] div[data-index="thursday"] select').val(1);
                //     $('div[data-index="dayandtime"] div[data-index="thursday"] select:eq(1)').val(timing[0]);
                //     $('div[data-index="dayandtime"] div[data-index="thursday"] select:eq(2)').val(timing[1]);;
                //     $('div[data-index="dayandtime"] div[data-index="thursday"] select:eq(3)').val(timing[2]);
                //     $('div[data-index="dayandtime"] div[data-index="thursday"] select:eq(4)').val(timing[3]);
                // }
                // if($('div[data-index="after_friday"] input[name="after_friday"]').val() !=='')
                // {
                //     timing=$('div[data-index="after_friday"] input[name="after_friday"]').val().match(/.{1,2}/g);
                //     if($('div[data-index="dayandtime"] div[data-index="friday"] select').val() == '0' && $('div[data-index="dayandtime"] div[data-index="friday"] select:eq(1)').prop('disabled'))
                //     $('div[data-index="dayandtime"] div[data-index="friday"] select').val(1);
                //     $('div[data-index="dayandtime"] div[data-index="friday"] select:eq(1)').val(timing[0]);
                //     $('div[data-index="dayandtime"] div[data-index="friday"] select:eq(2)').val(timing[1]);;
                //     $('div[data-index="dayandtime"] div[data-index="friday"] select:eq(3)').val(timing[2]);
                //     $('div[data-index="dayandtime"] div[data-index="friday"] select:eq(4)').val(timing[3]);
                // }
                // if($('div[data-index="after_saturday"] input[name="after_saturday"]').val() !=='')
                // {
                //     timing=$('div[data-index="after_saturday"] input[name="after_saturday"]').val().match(/.{1,2}/g);
                //     if($('div[data-index="dayandtime"] div[data-index="saturday"] select').val() == '0' && $('div[data-index="dayandtime"] div[data-index="saturday"] select:eq(1)').prop('disabled'))
                //     $('div[data-index="dayandtime"] div[data-index="saturday"] select').val(1);
                //     $('div[data-index="dayandtime"] div[data-index="saturday"] select:eq(1)').val(timing[0]);
                //     $('div[data-index="dayandtime"] div[data-index="saturday"] select:eq(2)').val(timing[1]);;
                //     $('div[data-index="dayandtime"] div[data-index="saturday"] select:eq(3)').val(timing[2]);
                //     $('div[data-index="dayandtime"] div[data-index="saturday"] select:eq(4)').val(timing[3]);
                // }
                // if($('div[data-index="after_sunday"] input[name="after_sunday"]').val() !=='')
                // {
                //     timing=$('div[data-index="after_sunday"] input[name="after_sunday"]').val().match(/.{1,2}/g);
                //     if($('div[data-index="dayandtime"] div[data-index="sunday"] select').val() == '0' && $('div[data-index="dayandtime"] div[data-index="sunday"] select:eq(1)').prop('disabled'))
                //     $('div[data-index="dayandtime"] div[data-index="sunday"] select').val(1);
                //     $('div[data-index="dayandtime"] div[data-index="sunday"] select:eq(1)').val(timing[0]);
                //     $('div[data-index="dayandtime"] div[data-index="sunday"] select:eq(2)').val(timing[1]);;
                //     $('div[data-index="dayandtime"] div[data-index="sunday"] select:eq(3)').val(timing[2]);
                //     $('div[data-index="dayandtime"] div[data-index="sunday"] select:eq(4)').val(timing[3]);
                // }
                // if($('div[data-index="dayandtime"] div[data-index="monday"] select').val() == '0' || $('div[data-index="dayandtime"] div[data-index="monday"] select').val() == undefined || $('div[data-index="dayandtime"] div[data-index="monday"] select').val() == '') {
                //     $('div[data-index="dayandtime"] div[data-index="monday"] select:eq(1)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="monday"] select:eq(2)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="monday"] select:eq(3)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="monday"] select:eq(4)').prop('disabled', true);
                // } else if($('div[data-index="dayandtime"] div[data-index="monday"] select').val() == 1) {
                //     $('div[data-index="dayandtime"] div[data-index="monday"] select:eq(1)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="monday"] select:eq(2)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="monday"] select:eq(3)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="monday"] select:eq(4)').prop('disabled', false);
                // }
                // if($('div[data-index="dayandtime"] div[data-index="tuesday"] select').val() == '0' || $('div[data-index="dayandtime"] div[data-index="tuesday"] select').val() == undefined || $('div[data-index="dayandtime"] div[data-index="tuesday"] select').val() == '') {
                //     $('div[data-index="dayandtime"] div[data-index="tuesday"] select:eq(1)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="tuesday"] select:eq(2)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="tuesday"] select:eq(3)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="tuesday"] select:eq(4)').prop('disabled', true);
                // } else if($('div[data-index="dayandtime"] div[data-index="tuesday"] select').val() == 1) {
                //     $('div[data-index="dayandtime"] div[data-index="tuesday"] select:eq(1)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="tuesday"] select:eq(2)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="tuesday"] select:eq(3)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="tuesday"] select:eq(4)').prop('disabled', false);
                // }
                // if(($('div[data-index="dayandtime"] div[data-index="wednesday"] select').val() == '0')||($('div[data-index="dayandtime"] div[data-index="wednesday"] select').val() == undefined)|| ($('div[data-index="dayandtime"] div[data-index="wednesday"] select').val() == '')) {
                //     $('div[data-index="dayandtime"] div[data-index="wednesday"] select:eq(1)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="wednesday"] select:eq(2)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="wednesday"] select:eq(3)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="wednesday"] select:eq(4)').prop('disabled', true);
                // } else if($('div[data-index="dayandtime"] div[data-index="wednesday"] select').val() == 1) {
                //     $('div[data-index="dayandtime"] div[data-index="wednesday"] select:eq(1)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="wednesday"] select:eq(2)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="wednesday"] select:eq(3)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="wednesday"] select:eq(4)').prop('disabled', false);
                // }
                // if(($('div[data-index="dayandtime"] div[data-index="thursday"] select').val() == '0')||($('div[data-index="dayandtime"] div[data-index="thursday"] select').val() == undefined)||($('div[data-index="dayandtime"] div[data-index="thursday"] select').val() == '')) {
                //     $('div[data-index="dayandtime"] div[data-index="thursday"] select:eq(1)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="thursday"] select:eq(2)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="thursday"] select:eq(3)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="thursday"] select:eq(4)').prop('disabled', true);
                // } else if($('div[data-index="dayandtime"] div[data-index="thursday"] select').val() == 1) {
                //     $('div[data-index="dayandtime"] div[data-index="thursday"] select:eq(1)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="thursday"] select:eq(2)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="thursday"] select:eq(3)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="thursday"] select:eq(4)').prop('disabled', false);
                // }
                // if(($('div[data-index="dayandtime"] div[data-index="friday"] select').val() == '0')||($('div[data-index="dayandtime"] div[data-index="friday"] select').val() == undefined)||($('div[data-index="dayandtime"] div[data-index="friday"] select').val() == '')) {
                //     $('div[data-index="dayandtime"] div[data-index="friday"] select:eq(1)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="friday"] select:eq(2)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="friday"] select:eq(3)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="friday"] select:eq(4)').prop('disabled', true);
                // } else if($('div[data-index="dayandtime"] div[data-index="friday"] select').val() == 1) {
                //     $('div[data-index="dayandtime"] div[data-index="friday"] select:eq(1)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="friday"] select:eq(2)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="friday"] select:eq(3)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="friday"] select:eq(4)').prop('disabled', false);
                // }
                // if(($('div[data-index="dayandtime"] div[data-index="saturday"] select').val() == '0')||($('div[data-index="dayandtime"] div[data-index="saturday"] select').val() == undefined)||($('div[data-index="dayandtime"] div[data-index="saturday"] select').val() == '')) {
                //     $('div[data-index="dayandtime"] div[data-index="saturday"] select:eq(1)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="saturday"] select:eq(2)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="saturday"] select:eq(3)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="saturday"] select:eq(4)').prop('disabled', true);
                // } else if($('div[data-index="dayandtime"] div[data-index="saturday"] select').val() == 1) {
                //     $('div[data-index="dayandtime"] div[data-index="saturday"] select:eq(1)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="saturday"] select:eq(2)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="saturday"] select:eq(3)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="saturday"] select:eq(4)').prop('disabled', false);
                // }
                // if(($('div[data-index="dayandtime"] div[data-index="sunday"] select').val() == '0')||($('div[data-index="dayandtime"] div[data-index="sunday"] select').val() == undefined)||($('div[data-index="dayandtime"] div[data-index="sunday"] select').val() == '')) {
                //     $('div[data-index="dayandtime"] div[data-index="sunday"] select:eq(1)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="sunday"] select:eq(2)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="sunday"] select:eq(3)').prop('disabled', true);
                //     $('div[data-index="dayandtime"] div[data-index="sunday"] select:eq(4)').prop('disabled', true);
                // } else if($('div[data-index="dayandtime"] div[data-index="sunday"] select').val() == 1) {
                //     $('div[data-index="dayandtime"] div[data-index="sunday"] select:eq(1)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="sunday"] select:eq(2)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="sunday"] select:eq(3)').prop('disabled', false);
                //     $('div[data-index="dayandtime"] div[data-index="sunday"] select:eq(4)').prop('disabled', false);
                // }
            }
        });
});