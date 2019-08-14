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


class Team23_PaypalUseractionCommit_Model_Config extends Mage_Paypal_Model_Config
{

    /**
     * Get url for dispatching customer to express checkout start
     *
     * @param string $token
     * @return string
     */
    public function getExpressCheckoutStartUrl($token)
    {
        return $this->getPaypalUrl(array(
            'cmd'   => '_express-checkout',
            'useraction' => 'commit',
            'token' => $token,
        ));
    }

}
