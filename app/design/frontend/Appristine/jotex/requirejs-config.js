var config = {
    paths: {            
            'flexslider': "flex/jquery.flexslider-min",
            'local': "js/local"
        },   
    shim: {
        'flexslider': {
            deps: ['jquery']
        },
        'local': {
            deps: ['jquery','mage/accordion']
        }
    }
};