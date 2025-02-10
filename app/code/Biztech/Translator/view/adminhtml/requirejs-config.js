var config = {
    "map": {
        'biztechTranslator': {
            "biztechTranslator": "Biztech_Translator/js/jquery/biztechTranslator",
        },
        'biztechTranslatorv213': {
            "biztechTranslatorv213": "Biztech_Translator/js/jquery/biztechTranslatorv213"
        },
    },
    "shim" : {
        "tinymce": {
            "exports": "tinymce"
        }
    },
    "paths" : {
        "tinymce": "tiny_mce/tiny_mce_src"
    },
    "config" : {
        "mixins" : {
            'Magento_Ui/js/grid/tree-massactions' : {
                'Biztech_Translator/js/grid/biz-tree-massactions' :  true
            },
            'Magento_Ui/js/grid/massactions' : {
                'Biztech_Translator/js/grid/biz-massactions' :  true
            }
        },
    }
};
