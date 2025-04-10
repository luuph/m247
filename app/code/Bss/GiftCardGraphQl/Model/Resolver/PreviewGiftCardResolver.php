<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GiftCardGraphQl\Model\Resolver;

use Bss\GiftCard\Model\Email;
use Bss\GiftCardGraphQl\Exception\GraphQlUnhandledException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class PreviewGiftCardResolver
 */
class PreviewGiftCardResolver implements \Magento\Framework\GraphQl\Query\ResolverInterface
{
    /**
     * @var Email
     */
    protected $emailModel;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * PreviewGiftCardResolver constructor.
     *
     * @param Email $emailModel
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Email $emailModel,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->emailModel = $emailModel;
        $this->storeManager = $storeManager;
    }

    /**
	 * @inheritDoc
	 */
	public function resolve(
	    Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
		try {
		    $this->validateData($args);
		    $data = $this->prepareData($args);

		    $store = $this->storeManager->getStore();
		    $contentHtml = $this->emailModel->previewEmail($data, $store);
		    return ['content' => $contentHtml];
        } catch (GraphQlInputException $e) {
            throw $e;
        } catch (\Exception $e) {
		    $this->logger->critical($e);
            throw new GraphQlUnhandledException(__("Something went wrong. Please review the log."));
        }
	}

    /**
     * Prepare data before render email
     *
     * @param array $args
     *
     * @return array
     */
	protected function prepareData($args)
    {
        $data = [];
        $data['product'] = $args['product_id'];
        foreach ($args['giftcard_options'] as $key => $value) {
            $data[$key] = $value;
        }
        return $data;
    }

    /**
     * Validate data
     *
     * @param array $args
     * @throws GraphQlInputException
     */
    protected function validateData(&$args)
    {
        $this->validate($args, 'input');

        $args = $args['input'];
        $this->validate($args, 'product_id', "");

        $this->validate($args, 'giftcard_options');

        foreach ($this->getRequiredGiftCardOptionsField() as $field => $value) {
            $this->validate($args['giftcard_options'], $field, $value);
        }
    }

    /**
     * Some awesome cleared describe the code above
     *
     * @param array $params
     * @param null|string $field
     * @param null|string|int $value
     * @throws GraphQlInputException
     */
    protected function validate($params, $field = null, $value = null)
    {
        if (!isset($params[$field]) || $params[$field] == $value)
        {
            throw new GraphQlInputException(__('Specify the "%1" values.', $field));
        }
    }

    /**
     * Get required gift card options field name
     *
     * @return string[]
     */
    private function getRequiredGiftCardOptionsField()
    {
        return [
            "bss_giftcard_amount" => "",
            "bss_giftcard_template" => 0,
            "bss_giftcard_selected_image" => 0,
            "bss_giftcard_sender_name" => "",
            "bss_giftcard_recipient_name" => ""
        ];
    }
}
