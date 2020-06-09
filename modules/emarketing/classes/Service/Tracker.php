<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the GNU General Public License, version 3 (GPL-3.0).
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * @author    emarketing www.emarketing.com <integrations@emarketing.com>
 * @copyright 2019 easymarketing AG
 * @license   https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

namespace Emarketing\Service;

/**
 * Class Tracker
 * @package Emarketing\Service
 */
class Tracker
{
    /**
     * @param $globalSiteTracker
     * @param $conversionTracker
     * @return bool
     * @throws \Exception
     */
    public function saveTracker($globalSiteTracker, $conversionTracker)
    {
        $this->saveInConfiguration('GLOBAL_SITE_TRACKER', $globalSiteTracker);
        $this->saveInConfiguration('CONVERSION_TRACKER', $conversionTracker);

        return true;
    }

    /**
     * @return string
     */
    public function getGlobalSiteTracker()
    {
        $globalSiteTracker = $this->getFromConfiguration('GLOBAL_SITE_TRACKER');

        return $globalSiteTracker;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getConversionTracker()
    {
        $conversionTracker = $this->getFromConfiguration('CONVERSION_TRACKER');

        $idOrder = (int)\Tools::getValue('id_order');

        $cart = new \Cart($idOrder);

        $currency = new \Currency($cart->id_currency);

        $conversionTracker = preg_replace(
            '/\'value\'\:[\s\S]+?,/',
            "'value': " . $cart->getOrderTotal() . ",",
            $conversionTracker
        );

        $conversionTracker = preg_replace(
            '/\'currency\'\:[\s\S]+?,/',
            "'currency': '" . $currency->iso_code  . "',",
            $conversionTracker
        );

        $conversionTracker = preg_replace(
            '/\'transaction_id\'\:[\s\S]+?\'\'/',
            "'transaction_id': '" . $idOrder . "',",
            $conversionTracker
        );

        return $conversionTracker;
    }

    /**
     * @param $key
     * @param $code
     * @return bool
     * @throws \Exception
     */
    private function saveInConfiguration($key, $code)
    {
        if (is_null($code) || !is_string($code)) {
            return false;
        }

        $code = htmlentities($code, ENT_QUOTES);

        $saved = \Configuration::updateValue('EMARKETING_' . $key, $code);

        if (!$saved) {
            throw new \Exception('Error while saving ' . $key);
        }

        return true;
    }

    /**
     * @param $key
     * @return string
     */
    private function getFromConfiguration($key)
    {
        $code = \Configuration::get('EMARKETING_' . $key);

        return html_entity_decode($code, ENT_QUOTES);
    }
}
