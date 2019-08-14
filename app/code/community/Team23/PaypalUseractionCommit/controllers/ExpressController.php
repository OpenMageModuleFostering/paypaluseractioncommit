<?php

/**
 * Team23_PaypalUseractionCommit
 *
 * @category  Team23
 * @package   Team23_PaypalUseractionCommit
 * @version   1.0.0
 * @copyright 2014 Team23 GmbH & Co. KG (http://www.team23.de)
 * @license   http://opensource.org/licenses/MIT The MIT License (MIT)
 */


require_once('Mage/Paypal/controllers/ExpressController.php');

class Team23_PaypalUseractionCommit_ExpressController extends Mage_Paypal_ExpressController
{

    /**
     * PayPal session instance getter
     *
     * @return Mage_PayPal_Model_Session
     */
    private function _getSession()
    {
        return Mage::getSingleton('paypal/session');
    }

    /**
     * Return checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    private function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Return checkout quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    private function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }

        return $this->_quote;
    }

    /**
     * Instantiate quote and checkout
     *
     * @throws Mage_Core_Exception
     */
    private function _initCheckout()
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $this->_getQuote();

        if (!$quote->hasItems() || $quote->getHasError())
        {
            $this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
            Mage::throwException(Mage::helper('paypal')->__('Unable to initialize Express Checkout.'));
        }

        $this->_checkout = Mage::getSingleton($this->_checkoutType, array(
            'config' => $this->_config,
            'quote'  => $quote,
        ));
    }

    /**
     * Return from PayPal and dispatch customer to order review page
     */
    public function returnAction()
    {
        try {
            $this->_initCheckout();

            // Call GetExpressCheckoutDetails
            $this->_checkout->returnFromPaypal($this->_initToken());

            // COPY OF placeOrderAction, without agreement check
            $this->_checkout->place($this->_initToken());

            // prepare session to success or cancellation page
            $session = $this->_getCheckoutSession();

            $session->clearHelperData();

            // "last successful quote"
            $quoteId = $this->_getQuote()->getId();

            $session->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

            // an order may be created
            $order = $this->_checkout->getOrder();

            if ($order)
            {
                $session->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId());

                // as well a billing agreement can be created
                $agreement = $this->_checkout->getBillingAgreement();

                if ($agreement)
                    $session->setLastBillingAgreementId($agreement->getId());
            }

            // recurring profiles may be created along with the order or without it
            $profiles = $this->_checkout->getRecurringPaymentProfiles();

            if ($profiles)
            {
                $ids = array();

                foreach($profiles as $profile)
                    $ids[] = $profile->getId();

                $session->setLastRecurringProfileIds($ids);
            }

            // redirect if PayPal specified some URL (for example, to Giropay bank)
            $url = $this->_checkout->getRedirectUrl();

            if ($url)
            {
                $this->getResponse()->setRedirect($url);

                return;
            }

            $this->_initToken(false); // no need in token anymore
            $this->_redirect('checkout/onepage/success');

            return;
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addError($this->__('Unable to process Express Checkout approval.'));
            Mage::logException($e);
        }

        $this->_redirect('checkout/cart');
    }

}
