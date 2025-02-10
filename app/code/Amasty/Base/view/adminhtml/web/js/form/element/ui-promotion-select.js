define([
    'Magento_Ui/js/form/element/ui-select',
    'mage/translate',
    'text!Amasty_Base/images/components/ui-promotion-select/lock.svg'
],function (UiSelect, $t, defaultPromoIcon) {
    'use strict';

    return UiSelect.extend({
        defaults: {
            elementTmpl: 'Amasty_Base/grid/filters/elements/ui-promo-select',
            optgroupTmpl: 'Amasty_Base/grid/filters/elements/ui-promo-select-optgroup',
            promoConfig: {
                promoIcon: defaultPromoIcon,
                badgeText: $t('Subscribe to Unlock'),
                badgeColor: '#523cc0',
                badgeBgColor: 'rgba(123, 97, 255, 0.15)'
            },
        },

        getInitialValue: function () {
            const values = [this.value(), this.default];
            let value = [];

            values.some(function (v) {
                if (v !== null && v !== undefined && v.length !== 0) {
                    value = v;

                    return true;
                }

                return false;
            });

            return this.normalizeData(value);
        },

        toggleOptionSelected: function (data) {
            if (data.isPromo) {
                return this;
            }

            return this._super(data);
        },

        openChildLevel: function (data) {
            return !data?.disableExpand && this._super(data);
        }
    });
});
