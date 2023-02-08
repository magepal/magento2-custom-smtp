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
use MagePal\CustomSmtp\Mail\ZF1\Smtp as ZF1Smtp;
use MagePal\CustomSmtp\Mail\ZF2\Smtp as ZF2Smtp;
use MagePal\CustomSmtp\Model\Store;
use Zend_Mail;
use Zend_Mail_Exception;

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

    /**
     * @param Data $dataHelper
     * @param Store $storeModel
     */
    public function __construct(
        Data $dataHelper,
        Store $storeModel
    ) {
        $this->dataHelper = $dataHelper;
        $this->storeModel = $storeModel;
    }

    /**
     * @param TransportInterface $subject
     * @param Closure $proceed
     * @throws MailException
     * @throws Zend_Mail_Exception
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

            //ZendMail1 - Magento <= 2.2.7
            //ZendMail2 - Magento >= 2.2.8
            if ($message instanceof Zend_Mail) {
                $smtp = new ZF1Smtp($this->dataHelper, $this->storeModel);
                $smtp->sendSmtpMessage($message);
            } elseif ($message instanceof Message || $message instanceof EmailMessageInterface) {
                $smtp = new ZF2Smtp($this->dataHelper, $this->storeModel);
                $smtp->sendSmtpMessage($message);
            } else {
                $proceed();
            }
        } else {
            $proceed();
        }
    }
}
