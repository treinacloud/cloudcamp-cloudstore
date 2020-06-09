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
 * Class FrontendHeader
 * @package Emarketing\Service
 */
class FrontendHeader
{
    /**
     * @return string
     * @throws \Exception
     */
    public function buildHtml()
    {
        $currentPage = \Context::getContext()->controller->php_self;

        $html = "<!-- emarketing start -->\n";

        $serviceVerification = new Verification;
        $html .= $serviceVerification->getTag() . "\n";

        $serviceTracker = new Tracker;
        $html .= $serviceTracker->getGlobalSiteTracker() . "\n";

        if ($currentPage == 'order-confirmation') {
            $html .= $serviceTracker->getConversionTracker() . "\n";
        }

        $html .= "<!-- emarketing end -->";

        return $html;
    }
}
