<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApiGraphql
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Extra;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * SearchTermList resolver
 */
class SearchTermList extends AbstractMobikul implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->wholeData = $args;
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $termBlock = $this->queryCollection
                ->addFieldToFilter("store_id", [["finset"=>[$this->storeId]]])
                ->setPopularQueryFilter($this->storeId);
            $maxPopularity = $termBlock->getFirstitem()->getPopularity();
            $minPopularity = $termBlock->getFirstitem()->getPopularity();
            $range = $maxPopularity - $minPopularity;
            $range = $range == 0 ? 1 : $range;
            if ($termBlock->getSize() > 0) {
                foreach ($termBlock as $term) {
                    $eachTerm = [];
                    $eachTerm["ratio"] = ((($term->getPopularity() - $minPopularity) / $range));
                    if ($eachTerm["ratio"] < 0) {
                        $eachTerm["ratio"] = 0;
                    } else {
                        $eachTerm["ratio"] *= 70 ;
                        $eachTerm["ratio"] += 75 ;
                    }
                    $eachTerm["term"] = $this->helperCatalog->stripTags($term->getQueryText());
                    $this->returnArray["termList"][] = $eachTerm;
                }
            }
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }

    /**
     * VerifyRequest function
     *
     * @return void
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = $this->wholeData["storeId"] ?? 0;
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
