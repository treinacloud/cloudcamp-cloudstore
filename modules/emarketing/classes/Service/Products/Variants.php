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

namespace Emarketing\Service\Products;

/**
* Class Variants
* @package Emarketing\Service
*/
class Variants
{
    /**
     * @var \Context
     */
    private $context;

    /**
     * Variants constructor.
     */
    public function __construct()
    {
        $this->context = \Context::getContext();
    }

    /**
     * @param \Product $psProduct
     * @param $idLang
     * @return mixed
     * @throws \PrestaShopException
     */
    public function buildVariantInformation($psProduct, $idLang)
    {
        $variants = $psProduct->getAttributesResume($idLang);

        if (empty($variants)) {
            return array();
        }

        foreach ($variants as $variantKey => $variant) {
            $variants[$variantKey]['url'] = $this->getUrl($psProduct->id, $idLang, $variant['id_product_attribute']);

            $variants[$variantKey]['special_price'] = \SpecificPrice::getByProductId(
                $psProduct->id,
                $variant['id_product_attribute']
            );

            $variants[$variantKey]['availability'] = $psProduct->checkQty(1);
        }

        return $variants;
    }

    /**
     * @param $idProduct
     * @param $idLang
     * @param $idVariant
     * @return string
     * @throws \PrestaShopException
     */
    private function getUrl($idProduct, $idLang, $idVariant)
    {
        $url = $this->context->link->getProductLink(
            $idProduct,
            null,
            null,
            null,
            $idLang,
            null,
            $idVariant
        );

        return $url;
    }
}
