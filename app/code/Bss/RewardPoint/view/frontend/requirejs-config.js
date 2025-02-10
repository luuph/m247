var config = {
    map: {
        '*': {
            'Magento_Review/js/process-reviews':'Bss_RewardPoint/js/process-reviews'
        }
    },
    config: {
        mixins: {
            "Magento_Swatches/js/swatch-renderer" : {
                "Bss_RewardPoint/js/mixins/swatch-renderer": true
            },
        }
    }
};