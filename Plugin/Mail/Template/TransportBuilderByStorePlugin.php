<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * https://www.magepal.com | support@magepal.com
 */
namespace MagePal\CustomSmtp\Plugin\Mail\Template;

use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use MagePal\CustomSmtp\Model\Store;

class TransportBuilderByStorePlugin
{
    /**
     * @var Store
     */
    protected $storeModel;

    /**
     * Sender resolver.
     * @var SenderResolverInterface
     */
    private $senderResolver;

    /**
     * @param Store $storeModel
     * @param SenderResolverInterface $senderResolver
     */
    public function __construct(
        Store $storeModel,
        SenderResolverInterface $senderResolver
    ) {
        $this->storeModel = $storeModel;
        $this->senderResolver = $senderResolver;
    }

    /**
     * Set the sender email address based on the store context
     * @param TransportBuilderByStore $subject
     * @param $from string
     * @param $store int|null
     * @return array
     * @throws MailException
     */
    public function beforeSetFromByStore(
        TransportBuilderByStore $subject,
        $from,
        $store
    ) {
        if (!$this->storeModel->getStoreId()) {
            $this->storeModel->setStoreId($store);
        }

        $email = $this->senderResolver->resolve($from, $store);
        $this->storeModel->setFrom($email);

        return [$from, $store];
    }
}
