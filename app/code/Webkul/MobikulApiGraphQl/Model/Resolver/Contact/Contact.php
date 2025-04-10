<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

declare(strict_types=1);

namespace Webkul\MobikulApiGraphQl\Model\Resolver\Contact;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Contact resolver
 */
class Contact extends \Webkul\MobikulApiGraphQl\Model\Resolver\ApiController implements ResolverInterface
{
    /**
     * Emulate variable
     *
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulate;

    /**
     * Mail variable
     *
     * @var \Magento\Contact\Model\MailInterface
     */
    protected $mail;

    /**
     * StateInterface variable
     *
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $stateInterface;

    /**
     * Name variable
     *
     * @var string
     */
    protected $name;

    /**
     * Comment variable
     *
     * @var mixed
     */
    protected $comment;

    /**
     * Telephone variable
     *
     * @var mixed
     */
    protected $telephone;

    /**
     * Construct Function
     *
     * @param \Webkul\MobikulCore\Helper\Data $helper
     * @param \Magento\Store\Model\App\Emulation $emulate
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\MobikulCore\Helper\Catalog $helperCatalog
     * @param \Magento\Contact\Model\MailInterface $contactMail
     * @param \Magento\Framework\Translate\Inline\StateInterface $stateInterface
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Magento\Contact\Model\MailInterface $contactMail,
        \Magento\Framework\Translate\Inline\StateInterface $stateInterface,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->emulate = $emulate;
        $this->mail = $contactMail;
        $this->jsonHelper = $jsonHelper;
        $this->stateInterface = $stateInterface;
        parent::__construct($helper, $request, $jsonHelper);
    }

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
            $this->stateInterface->suspend();
            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($this->wholeData);
            $error = false;
            $checkEmptyValidator = new \Laminas\Validator\NotEmpty();
            if (!$checkEmptyValidator->isValid(trim($this->wholeData["name"]))) {
                $error = true;
            }
            if (!$checkEmptyValidator->isValid(trim($this->wholeData["comment"]))) {
                $error = true;
            }
            $emailValidator = new \Laminas\Validator\EmailAddress();
            if (!$emailValidator->isValid(trim($this->wholeData["email"]))) {
                $error = true;
            }
            if ($error) {
                $this->stateInterface->resume();
                $this->returnArray["message"] = __(
                    "We can't process your request right now. Sorry, that's all we know."
                );
                return $this->returnArray;
            }
            $this->mail->send(
                $this->wholeData["email"],
                ["data" => $postObject]
            );
            $this->returnArray["message"] = __(
                "Thanks for contacting us with your comments and questions. We'll respond to you very soon."
            );
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->returnArray;
        } catch (\Exception $e) {
            $this->returnArray["message"] = __("We can't process your request right now. Sorry, that's all we know.");
            $this->helper->printLog($this->returnArray);
            return $this->returnArray;
        }
    }

    /**
     * Function verify Request to authenticate the request
     *
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->name = $this->wholeData["name"] ?? "";
            $this->email = $this->wholeData["email"] ?? "";
            $this->comment = $this->wholeData["comment"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->telephone = $this->wholeData["telephone"] ?? "";
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
