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

use Emarketing\CurlRequest;

/**
 * Class Gateway
 * @package Emarketing\Service
 */
class Gateway
{
    /**
     * @var string
     */
    private $ecomConnectUrl = "https://app.emarketing.com/shopsystem-auth/connect?connect-jwt=";

    /**
     * @var string
     */
    private $ecomSignInUrl = "https://app.emarketing.com/?jwt=";

    /**
     * @var string
     */
    private $gatewayUrl = "https://gateway.emarketing.com/gateway_api/sso";

    /**
     * @return string
     */
    public function getEcomUrl()
    {
        $token = $this->getToken();
        $url = $this->ecomSignInUrl;

        if (empty($token)) {
            $token = $this->register();
            $url = $this->ecomConnectUrl;

            if (empty($token)) {
                return false;
            }
        }

        $jwt = $this->fetchJwt($token);

        if (empty($jwt)) {
            return false;
        }

        return $url . $jwt;
    }

    /**
     * @return string
     */
    private function getToken()
    {
        return \Configuration::get('EMARKETING_SHOPTOKEN');
    }

    /**
     * @param $token
     * @return bool
     */
    private function saveToken($token)
    {
        return \Configuration::updateValue('EMARKETING_SHOPTOKEN', $token);
    }

    /**
     * @return bool|string
     */
    private function register()
    {
        $data = array(
            "partner_short_name" => "prestashop",
            "language" => strtolower(substr(\Language::getIsoById(\Configuration::get('PS_LANG_DEFAULT')), 0, 2)),
            "country" => strtoupper(\Country::getIsoById(\Configuration::get('PS_COUNTRY_DEFAULT'))),
            "email" => \Configuration::get('PS_SHOP_EMAIL')
        );

        $data = $this->validateRegistrationData($data);

        $header = "Origin: " . \Context::getContext()->shop->getBaseURL(true);

        $token = $this->registerRequest($data, $header);

        if (empty($token)) {
            return false;
        }

        $this->saveToken($token);

        return $token;
    }

    /**
     * @param $data
     * @return mixed
     */
    private function validateRegistrationData($data)
    {
        $schema = json_decode(file_get_contents('https://ecommerce-os.s3.eu-west-1.amazonaws.com/schema/partner.json'));
        $languages = $schema->links[0]->schema->properties->language->enum;
        $countries = $schema->links[0]->schema->properties->country->enum;

        if (!in_array($data['language'], $languages)) {
            $data['language'] = 'en';
        }

        if (!in_array($data['country'], $countries)) {
            $data['country'] = 'DE';
        }

        return $data;
    }

    /**
     * @param $data
     * @param $header
     * @return bool|string
     */
    private function registerRequest($data, $header)
    {
        $response = $this->sendRequest('/signup', $header, $data);

        if ($response['code'] == 400) {
            unset($data['email']);
            $response = $this->sendRequest('/signup', $header, $data);
        }

        if ($response['code'] !== 200 || empty($response['body']->token)) {
            return false;
        }

        return $response['body']->token;
    }

    /**
     * @param $token
     * @return bool|string
     */
    private function fetchJwt($token)
    {
        $header = "Authorization: " . $token;

        $response = $this->sendRequest('/jwt', $header);

        if (empty($response['body']->jwt)) {
            return false;
        }

        return $response['body']->jwt;
    }

    /**
     * @param $path
     * @param string $additionalHeader
     * @param array $data
     * @return array
     */
    private function sendRequest($path, $additionalHeader = "", $data = array())
    {
        $curl = new CurlRequest($this->gatewayUrl . $path);

        $dataJson = "";

        if (!empty($data)) {
            $curl->setOption(CURLOPT_CUSTOMREQUEST, 'POST');

            $dataJson = json_encode($data);
            $curl->setOption(CURLOPT_POSTFIELDS, $dataJson);
        }

        $header = array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . \Tools::strlen($dataJson),
            $additionalHeader
        );

        $curl->setOption(CURLOPT_HTTPHEADER, $header);

        $response = $curl->execute();

        $return = array(
            'body' => json_decode($response),
            'code' => $curl->getInfo(CURLINFO_HTTP_CODE)
        );

        $curl->close();

        return $return;
    }
}
