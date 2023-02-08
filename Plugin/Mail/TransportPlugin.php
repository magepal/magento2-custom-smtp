<?php
/**
 * Copyright © MagePal LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.magepal.com | support@magepal.com
 */

namespace MagePal\CustomSmtp\Plugin\Mail;

use Closure;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\Message;
use Magento\Framework\Mail\TransportInterface;
use MagePal\CustomSmtp\Helper\Data;
use MagePal\CustomSmtp\Mail\Smtp;
use MagePal\CustomSmtp\Model\Store;

class TransportPlugin
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var Store
     */
    protected $storeModel;
    private SmtpFactory $smtpFactory;

    /**
     * @param Data $dataHelper
     * @param Store $storeModel
     */
    public function __construct(
        Data $dataHelper,
        Store $storeModel,
        SmtpFactory $smtpFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->storeModel = $storeModel;
        $this->smtpFactory = $smtpFactory;
    }

    /**
     * @param TransportInterface $subject
     * @param Closure $proceed
     * @throws MailException
     */
    public function aroundSendMessage(
        TransportInterface $subject,
        Closure $proceed
    ) {
        if ($this->dataHelper->isActive()) {
            if (method_exists($subject, 'getStoreId')) {
                $this->storeModel->setStoreId($subject->getStoreId());
            }

            $message = $subject->getMessage();

            if ($message instanceof Message || $message instanceof EmailMessageInterface) {
                $smtp = $this->smtpFactory->create(
                    ['dataHelper' => $this->dataHelper, 'storeModel' => $this->storeModel]
                );
                $smtp->sendSmtpMessage($message);
            } else {
                $proceed();
            }
        } else {
            $proceed();
        }
    }
}
