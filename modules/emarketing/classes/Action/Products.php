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

namespace Emarketing\Action;

use Emarketing\ClientError;

/**
 * Class Products
 * @package Emarketing\Action
 */
class Products
{
    /**
     * @param $postData
     * @return array
     * @throws ClientError
     * @throws \Exception
     */
    public function fetchProducts($postData)
    {
        $offset = $this->checkValidParam($postData, 'offset');

        $limit = $this->checkValidParam($postData, 'limit');

        $idLang = $this->getIdLanguage($postData);

        $idCountry = $this->getIdCountry($postData);

        $serviceProducts = new \Emarketing\Service\Products;

        $productData = $serviceProducts->buildProductsInformation($offset, $limit, $idLang, $idCountry);

        return $productData;
    }

    /**
     * @param $postData
     * @param $param
     * @return mixed
     * @throws ClientError
     */
    private function checkValidParam($postData, $param)
    {
        if (!isset($postData[$param]) || !is_numeric($postData[$param]) || $postData[$param] < 0) {
            throw new ClientError('Invalid ' . $param);
        }

        return $postData[$param];
    }

    /**
     * @param $postData
     * @return mixed
     */
    private function getIdLanguage($postData)
    {
        if (!isset($postData['id_language']) || !is_numeric($postData['id_language'])) {
            return \Context::getContext()->language->id;
        }

        return $postData['id_language'];
    }

    /**
     * @param $postData
     * @return string
     */
    private function getIdCountry($postData)
    {
        if (!isset($postData['id_country']) || !is_numeric($postData['id_country'])) {
            return \Configuration::get('PS_COUNTRY_DEFAULT');
        }

        return $postData['id_country'];
    }
}
