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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/vendor/autoload.php');

/**
 * Class Emarketing
 */
class Emarketing extends Module
{
    /**
     * Emarketing constructor.
     */
    public function __construct()
    {
        $this->name = 'emarketing';
        $this->tab = 'advertising_marketing';
        $this->version = '2.0.8';
        $this->author = 'emarketing';
        $this->module_key = 'f28d5933d349ca55af63ed0b10f6ca33';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array(
            'min' => '1.6.0.5',
            'max' => _PS_VERSION_
        );
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'PrestaShop Ads';
        $this->description = $this->l('Boost your sales on Google Shopping! This module is your
            one-click-solution for advertising on Google Shopping, Amazon & Facebook. Easy campaign creation,
            price-comparison and competitor-check included.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        \Configuration::updateValue('EMARKETING_SHOPTOKEN', "");
        \Configuration::updateValue('EMARKETING_GLOBAL_SITE_TRACKER', "");
        \Configuration::updateValue('EMARKETING_CONVERSION_TRACKER', "");
        \Configuration::updateValue('EMARKETING_VERIFICATION_TAG', "");

        $this->registerHook('displayHeader');

        $this->registerHook('displayBackOfficeHeader');

        $this->installTab();

        return true;
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        $this->uninstallTab();

        return true;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function hookDisplayHeader()
    {
        $serviceFrontendHeader = new \Emarketing\Service\FrontendHeader;
        $html = $serviceFrontendHeader->buildHtml();

        return $html;
    }

    /**
     *
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCss($this->_path . 'views/css/menuTabIcon.css');
    }
    
    /**
     * @return mixed
     */
    public function getContent()
    {
        $link = new \Link();

        $templateData = array(
            'ecom_url' => $link->getModuleLink($this->name, 'ecom')
        );

        $this->smarty->assign($templateData);

        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function installTab()
    {
        $tab = new Tab();

        $tab->module = $this->name;

        $languages = \Language::getLanguages(false);
        $name = array();
        foreach ($languages as $language) {
            $name[$language['id_lang']] = 'Advertising';
        }

        $tab->name = $name;
        $tab->class_name = 'AdminEmarketing';

        if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            $tab->icon = 'track_changes';
            $tab->id_parent = (int)Tab::getIdFromClassName('IMPROVE');
            $tab->save();
        } else {
            $tab->id_parent = 0;
            $tab->add();
        }
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function uninstallTab()
    {
        $tabId = (int)Tab::getIdFromClassName('AdminEmarketing');
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }
}
