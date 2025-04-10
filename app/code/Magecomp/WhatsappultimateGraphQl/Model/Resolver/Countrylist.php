<?php
namespace Magecomp\WhatsappultimateGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magecomp\Whatsappultimate\Model\Ultimatepost;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Countrylist implements ResolverInterface
{
    protected $ultimatepost;

    public function __construct(
        Ultimatepost $ultimatepost
    ) {
        $this->ultimatepost = $ultimatepost;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $output = [];
        if ((empty($args['status']) || !isset($args['status']))) {
            $output['status'] = false;
            $output['message'] = __('Invalid parameter list.');
            return $output;
        }

        $output['status'] = false;
        $output['country'] = '';
        $output['message'] = __("Country List");
        try {
            if($args['status']==true){
            	 $output['status'] = true;
                $listoutput = $this->ultimatepost->getCountryList();
                $output['country'] =  json_encode($listoutput);
            }
            return $output;
        } catch (\Exception $e) {
            $output['status'] = false;
            $output['country'] = '';
            $output['message'] = __($e->getMessage());
            return $output;
        }
    }
}
