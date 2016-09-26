<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * X-Cart
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the software license agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.x-cart.com/license-agreement.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to licensing@x-cart.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not modify this file if you wish to upgrade X-Cart to newer versions
 * in the future. If you wish to customize X-Cart for your needs please
 * refer to http://www.x-cart.com/ for more information.
 *
 * @category  X-Cart 5
 * @author    Qualiteam software Ltd <info@x-cart.com>
 * @copyright Copyright (c) 2011-2016 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\DigitalOrigin\PagaMasTarde\Model\Payment\Processor;

/**
 * PagaMasTarde processor
 *
 * Find the latest API document here:
 * http://docs.pagamastarde.com/
 */
class PagaMasTarde extends \XLite\Model\Payment\Base\WebBased
{

    /**
     * Get settings widget or template
     *
     * @return string Widget class name or template path
     */
    public function getSettingsWidget()
    {
        return 'modules/DigitalOrigin/PagaMasTarde/config.tpl';
    }

    /**
     * Get input template
     *
     * @return string|void
     */
    public function getInputTemplate()
    {
        return 'modules/DigitalOrigin/PagaMasTarde/payment.tpl';
    }

    /**
     * Process return
     *
     * @param \XLite\Model\Payment\Transaction $transaction Return-owner transaction
     *
     * @return void
     */
    public function processReturn(\XLite\Model\Payment\Transaction $transaction)
    {
        parent::processReturn($transaction);

    }

    public function processCallback(\XLite\Model\Payment\Transaction $transaction)
    {
        parent::processCallback($transaction);

        $request = \XLite\Core\Request::getInstance();

        $json = file_get_contents('php://input');

        //\XLite\Logger::logCustom('pmt', var_export($json,1), '');

        $temp = json_decode($json, true);

        if ($this->getSetting('test')) {
            $this->public_key=$this->getSetting('testPublicKey');
            $this->secret_key=$this->getSetting('testSecretKey');
        } else {
            $this->public_key=$this->getSetting('realPublicKey');
            $this->secret_key=$this->getSetting('realSecretKey');
        }

        $signature_check = sha1(
            $this->secret_key .
            $temp['account_id'] .
            $temp['api_version'] .
            $temp['event'] .
            $temp['data']['id']
        );

        $signature_check_sha512 = hash('sha512',
            $this->secret_key .
            $temp['account_id'] .
            $temp['api_version'] .
            $temp['event'] .
            $temp['data']['id']
        );

        if ($signature_check != $temp['signature'] && $signature_check_sha512 != $temp['signature']) {
            //hack detected
            $status = $transaction::STATUS_FAILED;
            $this->setDetail('verification', 'Verification failed', 'Verification');

            $this->transaction->setNote('Verification failed');

        } else {
            $status = $transaction::STATUS_SUCCESS;
            $this->setDetail('result', 'Accept', 'Result');
        }

        $this->transaction->setStatus($status);
    }

    /**
     * Check - payment method is configured or not
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return boolean
     */
    public function isConfigured(\XLite\Model\Payment\Method $method)
    {
        return parent::isConfigured($method)
            && $method->getSetting('testPublicKey')
            && $method->getSetting('testSecretKey');
    }

    /**
     * Get return type
     *
     * @return string
     */
    public function getReturnType()
    {
        return self::RETURN_TYPE_HTML_REDIRECT;
    }

    /**
     * Returns the list of settings available for this payment processor
     *
     * @return array
     */
    public function getAvailableSettings()
    {
        return array(
            'testPublicKey',
            'testSecretKey',
            'realPublicKey',
            'realSecretKey',
            'discount',
            'test',
        );
    }

    /**
     * Get payment method admin zone icon URL
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return string
     */
    public function getAdminIconURL(\XLite\Model\Payment\Method $method)
    {
        return true;
    }

    /**
     * Check - payment method has enabled test mode or not
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return boolean
     */
    public function isTestMode(\XLite\Model\Payment\Method $method)
    {
        return (bool)$method->getSetting('test');
    }

    /**
     * Get allowed currencies
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return array
     */
    protected function getAllowedCurrencies(\XLite\Model\Payment\Method $method)
    {
        return array('AUD', 'USD', 'CAD', 'EUR', 'GBP', 'NZD');
    }

    /**
     * Get redirect form URL
     *
     * @return string
     */
    protected function getFormURL()
    {
        return 'https://pmt.pagantis.com/v1/installments';
    }

    /**
     * Get redirect form fields list
     *
     * @return array
     */
    protected function getFormFields()
    {
        /** @var \XLite\Model\Currency $currency */
        $currency = $this->transaction->getCurrency();

        $bState = $this->getProfile()->getBillingAddress()->getState()->getCode()
            ? $this->getProfile()->getBillingAddress()->getState()->getCode()
            : '';

        $sState = $this->getProfile()->getShippingAddress()->getState()->getCode()
            ? $this->getProfile()->getShippingAddress()->getState()->getCode()
            : '';

        if ($this->getSetting('test')) {
            $this->public_key=$this->getSetting('testPublicKey');
            $this->secret_key=$this->getSetting('testSecretKey');
        } else {
            $this->public_key=$this->getSetting('realPublicKey');
            $this->secret_key=$this->getSetting('realSecretKey');
        }

        $trx_id=$this->transaction->getPublicTxnId();

        $ok_url = $this->getReturnURL(null, true);
        $nok_url = $this->getPaymentReturnURL('decline');
        $callback_url = $this->getCallbackURL(null, true); //$this->getPaymentReturnURL('accept');

        $cancelled_url = $this->getPaymentReturnURL('checkout');

        if ($this->getSetting('discount')) {
            $this->discount = 'true';
        } else {
            $this->discount = 'false';
        }

        $string = $this->secret_key
            . $this->public_key
            . $trx_id
            . round($this->transaction->getValue()*100, 0)
            . $currency->getCode()
            . $ok_url
            . $nok_url
            . $callback_url
            . $this->discount
            . $cancelled_url;

        $signature = hash('sha512', $string);

        $fields = array(
            'account_id'      => $this->public_key,
            'callback_url'    => $callback_url,
            'cancelled_url'    => $cancelled_url,
            'ok_url'          => $ok_url,
            'nok_url'         => $nok_url,
            'signature'       => $signature,
            'amount'          => round($this->transaction->getValue()*100, 0),
            'currency'        => $currency->getCode(),
            'full_name'       => $this->getProfile()->getBillingAddress()->getFirstname() ." "
                                    . $this->getProfile()->getBillingAddress()->getLastname(),
            'mobile_phone'           => $this->getProfile()->getBillingAddress()->getPhone(),
            'email'           => $this->getProfile()->getLogin(),
            'address[street]' => $this->getProfile()->getBillingAddress()->getStreet(),
            'address[city]'   => $this->getProfile()->getBillingAddress()->getCity(),
            'address[province]' => $bState,
            'address[zipcode]'  => $this->getProfile()->getShippingAddress()->getZipcode(),
            'shipping[street]' => $this->getProfile()->getShippingAddress()->getStreet(),
            'shipping[city]'   => $this->getProfile()->getShippingAddress()->getCity(),
            'shipping[province]' => $sState,
            'shipping[zipcode]'  => $this->getProfile()->getShippingAddress()->getZipcode(),
            'discount[full]'  => $this->discount,
            'order_id'        => $trx_id,
            'description'     => $trx_id,
            'pay_method'      => 'CC',
        );

        $i=0;
        $ship_amt = $this->getOrder()->getSurchargeSumByType(\XLite\Model\Base\Surcharge::TYPE_SHIPPING);
        if ($ship > 0) {
            $fields["items[".$i."][quantity]"]= 1;
            $fields["items[".$i."][description]"]= 'Gastos de envio';
            $fields["items[".$i."][amount]"]= $ship_amt;
            $i++;
        }
        $tax_amt = $this->getOrder()->getSurchargeSumByType(\XLite\Model\Base\Surcharge::TYPE_TAX);
        if ($tax_amt > 0) {
            $fields["items[".$i."][quantity]"]= 1;
            $fields["items[".$i."][description]"]= 'Impuestos';
            $fields["items[".$i."][amount]"]= $tax_amt;
            $i++;
        }

        $des=array();
        foreach ($this->getOrder()->getItems() as $item) {

            $product = $item->getProduct();

            $description = $product->getCommonDescription() ?: $product->getName();

            $fields["items[".$i."][quantity]"]     = $item->getAmount();
            if ($item->getAmount() > 1) {
                $desc  = substr($product->getName(), 0, 127) .
                " (".$item->getAmount().") ";
            } else {
                $desc  = substr($product->getName(), 0, 127);
            }
            $fields["items[".$i."][description]"]=$desc;
            $des[]=$desc;
            $fields["items[".$i."][amount]"]       = round($item->getPrice() * $item->getAmount(), 2);
            $i++;
        }

        $fields['description'] = implode(",", $des);

        /* not using shipping address
        $shippingAddress = $this->getProfile()->getShippingAddress();
        if ($shippingAddress) {

            $fields += array(
                'x_ship_to_first_name'  => $shippingAddress->getFirstname(),
                'x_ship_to_last_name'   => $shippingAddress->getLastname(),
                'x_ship_to_address'     => $shippingAddress->getStreet(),
                'x_ship_to_city'        => $shippingAddress->getCity(),
                'x_ship_to_state'       => $shippingAddress->getState()->getCode()
                    ? $shippingAddress->getState()->getCode()
                    : 'n/a',
                'x_ship_to_zip'         => $shippingAddress->getZipcode(),
                'x_ship_to_country'     => $shippingAddress->getCountry()->getCountry(),
            );
        }
        */
        //\XLite\Logger::logCustom('pmt', var_export($fields,1), '');
        return $fields;
    }

    /**
     * Get payment return URL, type: accept, decline, exception, cancel
     *
     * @param string $type Type of return
     *
     * @return string
     */
    protected function getPaymentReturnURL($type)
    {
        return \XLite::getInstance()->getShopURL(
            \XLite\Core\Converter::buildURL(
                'payment_return',
                null,
                array(
                    self::RETURN_TXN_ID => $this->transaction->getPublicTxnId(),
                    'type'              => $type,
                )
            ),
            \XLite\Core\Config::getInstance()->Security->customer_security
        );
    }
}
