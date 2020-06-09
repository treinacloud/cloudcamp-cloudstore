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

use Emarketing\Service\Products\Variants;
use Emarketing\Service\Products\Images;
use Emarketing\Service\Products\Carriers;
use Emarketing\Service\Products\Features;

/**
 * Class Products
 * @package Emarketing\Service
 */
class Products
{
    /**
     * @var Carriers
     */
    private $serviceCarriers;

    /**
     * @var Variants
     */
    private $serviceVariants;

    /**
     * @var Images
     */
    private $serviceImages;

    /**
     * @var Features
     */
    private $serviceFeatures;

    /**
     * @var \Context
     */
    private $context;

    /**
     * Products constructor.
     */
    public function __construct()
    {
        $this->serviceVariants = new Variants();
        $this->serviceImages = new Images();
        $this->serviceCarriers = new Carriers();
        $this->serviceFeatures = new Features();

        $this->context = \Context::getContext();
    }

    /**
     * @param $offset
     * @param $limit
     * @param $idLang
     * @param $idCountry
     * @return array
     * @throws \Exception
     */
    public function buildProductsInformation($offset, $limit, $idLang, $idCountry)
    {
        $productsData = array();

        $productsData['settings'] = $this->getShopSettings();

        $products = $this->getAllProducts($offset, $limit, $idLang);

        foreach ($products as $product) {
            $productData = $this->getProductDetails($product, $idLang, $idCountry);

            $productsData[$product['id_product']] = $productData;
        }

        return $productsData;
    }

    /**
     * @param $offset
     * @param $limit
     * @param $idLang
     * @return array
     * @throws \Exception
     */
    private function getAllProducts($offset, $limit, $idLang)
    {
        $products = \Product::getProducts($idLang, $offset, $limit, 'id_product', 'ASC', false, true);

        if ($products === false) {
            throw new \Exception('Error while fetching products.');
        }

        return $products;
    }

    /**
     * @param $product
     * @param $idLang
     * @param $idCountry
     * @return array
     * @throws \PrestaShopException
     */
    private function getProductDetails($product, $idLang, $idCountry)
    {
        $productData = array();

        $psProduct = new \Product($product['id_product'], false, $idLang);

        $productData['details'] = $product;

        $productData['additional'] = $this->getAdditionalInformation($psProduct, $idLang);

        $productData['variants'] = $this->serviceVariants->buildVariantInformation($psProduct, $idLang);

        $productData['images'] = $this->serviceImages->buildImageInformation($psProduct, $idLang);

        $productData['carriers'] = $this->serviceCarriers->buildCarrierInformation($idLang, $idCountry, $psProduct);

        $productData['features'] = $this->serviceFeatures->buildFeatureInformation($psProduct, $idLang);

        $productData['attributes'] = $psProduct->getAttributesGroups($idLang);

        return $productData;
    }

    /**
     * @return array
     */
    private function getShopSettings()
    {
        $settings = array();

        $settings['dimension_unit'] = \Configuration::get('PS_DIMENSION_UNIT');
        $settings['weight_unit'] = \Configuration::get('PS_WEIGHT_UNIT');
        $settings['allow_ordering_oos'] = \Configuration::get('PS_ORDER_OUT_OF_STOCK');

        return $settings;
    }

    /**
     * @param \Product $psProduct
     * @param $idLang
     * @return array
     * @throws \PrestaShopException
     */
    public function getAdditionalInformation($psProduct, $idLang)
    {
        $additional = array();

        $additional['categories'] = $psProduct->getCategories();

        $additional['url'] = $this->context->link->getProductLink(
            $psProduct->id,
            null,
            null,
            null,
            $idLang
        );

        $additional['quantity'] = \StockAvailable::getQuantityAvailableByProduct($psProduct->id);

        $additional['availability'] = $psProduct->checkQty(1);

        $additional['special_price'] = \SpecificPrice::getByProductId($psProduct->id);

        return $additional;
    }
}
