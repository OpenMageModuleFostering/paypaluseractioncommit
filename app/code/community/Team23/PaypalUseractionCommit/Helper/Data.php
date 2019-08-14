<?php

/**
 * Team23_PaypalUseractionCommit
 *
 * @category  Team23
 * @package   Team23_PaypalUseractionCommit
 * @version   1.0.1
 * @copyright 2014 Team23 GmbH & Co. KG (http://www.team23.de)
 * @license   http://opensource.org/licenses/MIT The MIT License (MIT)
 */


class Team23_PaypalUseractionCommit_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Check if module is active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->isModuleEnabled();
    }

}