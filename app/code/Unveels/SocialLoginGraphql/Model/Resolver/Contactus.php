<?php

namespace Unveels\SocialLoginGraphql\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;


class Contactus implements ResolverInterface
{
    private $contactusDataProvider;

    /**
     * @param
     */
    public function __construct(
        \Unveels\SocialLoginGraphql\Model\Resolver\DataProvider\Contactus $contactusDataProvider
    ) {
        $this->contactusDataProvider = $contactusDataProvider;
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
        $fullname = $args['input']['fullname'];
        $email = $args['input']['email'];
        $telephone = $args['input']['telephone'];
        $message = $args['input']['message'];

        $success_message = $this->contactusDataProvider->contactUs(
            $fullname,
            $email,
            $telephone,
            $message
        );
        return $success_message;
    }
}