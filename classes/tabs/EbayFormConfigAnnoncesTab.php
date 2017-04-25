<?php
/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class EbayFormConfigAnnoncesTab extends EbayTab
{
    public function getContent()
    {
        // Loading config currency
        $config_currency = new Currency((int)$this->ebay_profile->getConfiguration('EBAY_CURRENCY'));

        $url_vars = array(
            'id_tab'  => '101',
            'section' => 'parametersannonces'
        );

        $url_vars['controller'] = Tools::getValue('controller');


        $url = $this->_getUrl($url_vars);

        $ebayShop = $this->ebay_profile->getConfiguration('EBAY_SHOP') ? $this->ebay_profile->getConfiguration('EBAY_SHOP') : $this->ebay->StoreName;

        $ebayShopValue = Tools::getValue('ebay_shop', $ebayShop);

        $ebay_country = EbayCountrySpec::getInstanceByKey($this->ebay_profile->getConfiguration('EBAY_COUNTRY_DEFAULT'));

        $createShopUrl = 'http://cgi3.ebay.'.$ebay_country->getSiteExtension().'/ws/eBayISAPI.dll?CreateProductSubscription&&productId=3&guest=1';

        $ebay_request = new EbayRequest();


        $returns_policy_configuration = $this->ebay_profile->getReturnsPolicyConfiguration();

        $returnsConditionAccepted = Tools::getValue('ebay_returns_accepted_option', Configuration::get('EBAY_RETURNS_ACCEPTED_OPTION'));


        $ebayListingDuration = $this->ebay_profile->getConfiguration('EBAY_LISTING_DURATION') ? $this->ebay_profile->getConfiguration('EBAY_LISTING_DURATION') : 'GTC';

        $is_multishop =  Shop::isFeatureActive();

        $order_states = OrderState::getOrderStates($this->ebay_profile->id_lang);
        
        $current_order_state = $this->ebay_profile->getConfiguration('EBAY_SHIPPED_ORDER_STATE');
        if ($current_order_state === null) {
            foreach ($order_states as $order_state) {
                if ($order_state['template'] === 'shipped') {
                    // NDRArbuz: is this the best way to find it with no doubt?
                    $current_order_state = $order_state['id_order_state'];
                    break;
                }
            }
        }

        $smarty_vars = array(
            'url'                       => $url,
            // pictures
            'sizes'               => ImageType::getImagesTypes('products'),
            'itemspecifics'               => AttributeGroup::getAttributesGroups(1),
            'sizedefault'         => $this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_DEFAULT'),
            'sizebig'             => (int)$this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_BIG'),
            'sizesmall'           => (int)$this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_SMALL'),
            'picture_per_listing' => (int)$this->ebay_profile->getConfiguration('EBAY_PICTURE_PER_LISTING'),
            'picture_skip_variations' => (bool)$this->ebay_profile->getConfiguration('EBAY_PICTURE_SKIP_VARIATIONS'),
            'picture_charact_variations' => (int)$this->ebay_profile->getConfiguration('EBAY_PICTURE_CHARACT_VARIATIONS'),
            'policies'                  => EbayReturnsPolicy::getReturnsPolicies(),
            'createShopUrl'             => $createShopUrl,
            'ebayReturns'               => preg_replace('#<br\s*?/?>#i', "\n", $this->ebay_profile->getReturnsPolicyConfiguration()->ebay_returns_description),
            'ebayShopValue'             => $ebayShopValue,
            'shopPostalCode'            => Tools::getValue('ebay_shop_postalcode', $this->ebay_profile->getConfiguration('EBAY_SHOP_POSTALCODE')),
            'listingDurations'          => $this->_getListingDurations(),
            'ebayShop'                  => $this->ebay_profile->getConfiguration('EBAY_SHOP'),
            'returnsConditionAccepted'  => Tools::getValue('ebay_returns_accepted_option', $returns_policy_configuration->ebay_returns_accepted_option),
            'automaticallyRelist'       => $this->ebay_profile->getConfiguration('EBAY_AUTOMATICALLY_RELIST'),
            'returnsConditionAccepted'  => $returnsConditionAccepted,
            'ebayListingDuration'       => $ebayListingDuration,
            'is_multishop'              => $is_multishop,
            'within_values'             => unserialize(Configuration::get('EBAY_RETURNS_WITHIN_VALUES')),
            'within'                    => $returns_policy_configuration->ebay_returns_within,
            'whopays_values'            => unserialize(Configuration::get('EBAY_RETURNS_WHO_PAYS_VALUES')),
            'whopays'                   => $returns_policy_configuration->ebay_returns_who_pays,
            //EAN
            'synchronize_ean'    => (string)Configuration::get('EBAY_SYNCHRONIZE_EAN'),
            'synchronize_mpn'    => (string)Configuration::get('EBAY_SYNCHRONIZE_MPN'),
            'synchronize_upc'    => (string)Configuration::get('EBAY_SYNCHRONIZE_UPC'),
            'synchronize_isbn'   => (string)Configuration::get('EBAY_SYNCHRONIZE_ISBN'),
            'ean_not_applicable' => (int)Configuration::get('EBAY_EAN_NOT_APPLICABLE'),
            'help_ean' => array(
                'lang'           => $this->context->country->iso_code,
                'module_version' => $this->ebay->version,
                'ps_version'     => _PS_VERSION_,
                'error_code'     => 'HELP-ADV-SETTINGS-EAN',
            ),
            'help_gtc' => array(
                'lang'           => $this->context->country->iso_code,
                'module_version' => $this->ebay->version,
                'ps_version'     => _PS_VERSION_,
                'error_code'     => 'HELP-SETTINGS-LISTING-DURATION',
            ),
            'id_shop' => $this->context->shop->id,
            'out_of_stock_value' => (bool)EbayConfiguration::get($this->ebay_profile->id, 'EBAY_OUT_OF_STOCK'),
            'help_out_of_stock' => array(
                'lang'           => $this->context->country->iso_code,
                'module_version' => $this->ebay->version,
                'ps_version'     => _PS_VERSION_,
                'error_code'     => 'HELP-SETTINGS-OUT-OF-STOCK',
            ),
        );

        return $this->display('formParametersAnnonces.tpl', $smarty_vars);
    }


    public function postProcess()
    {

        // Saving new configurations
        $picture_per_listing = (int) Tools::getValue('picture_per_listing');
        if ($picture_per_listing < 0) {
            $picture_per_listing = 0;
        }
        // we retrieve the potential currencies to make sure the selected currency exists in this shop
        $currencies = TotCompatibility::getCurrenciesByIdShop($this->ebay_profile->id_shop);
        $currencies_ids = array_map(array($this, 'getCurrencyId'), $currencies);
        $this->ebay->setConfiguration('EBAY_SYNCHRONIZE_EAN', (string)Tools::getValue('synchronize_ean'));
        $this->ebay->setConfiguration('EBAY_SYNCHRONIZE_MPN', (string)Tools::getValue('synchronize_mpn'));
        $this->ebay->setConfiguration('EBAY_SYNCHRONIZE_UPC', (string)Tools::getValue('synchronize_upc'));
        $this->ebay->setConfiguration('EBAY_SYNCHRONIZE_ISBN', (string)Tools::getValue('synchronize_isbn'));
        $this->ebay->setConfiguration('EBAY_EAN_NOT_APPLICABLE', Tools::getValue('ean_not_applicable') ? 1 : 0);


        // Reset Image if they have modification on the size
        if ($this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_DEFAULT') != (int)Tools::getValue('sizedefault')
            || $this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_SMALL') != (int)Tools::getValue('sizesmall')
            || $this->ebay_profile->getConfiguration('EBAY_PICTURE_SIZE_BIG') != (int)Tools::getValue('sizebig')
        ) {
            EbayProductImage::removeAllProductImage();
        }

        if ($this->ebay_profile->setConfiguration('EBAY_PICTURE_SIZE_DEFAULT', (int)Tools::getValue('sizedefault'))
            && $this->ebay_profile->setConfiguration('EBAY_PICTURE_SIZE_SMALL', (int)Tools::getValue('sizesmall'))
            && $this->ebay_profile->setConfiguration('EBAY_PICTURE_SIZE_BIG', (int)Tools::getValue('sizebig'))
            && $this->ebay_profile->setConfiguration('EBAY_PICTURE_PER_LISTING', $picture_per_listing)
            && $this->ebay_profile->setConfiguration('EBAY_PICTURE_CHARACT_VARIATIONS', (int)Tools::getValue('picture_charact_variations'))
            && $this->ebay_profile->setConfiguration('EBAY_PICTURE_SKIP_VARIATIONS', (bool)Tools::getValue('picture_skip_variations'))
            && $this->ebay_profile->setConfiguration('EBAY_LISTING_DURATION', Tools::getValue('listingdurations'))
            && $this->ebay_profile->setConfiguration('EBAY_AUTOMATICALLY_RELIST', Tools::getValue('automaticallyrelist'))
            && $this->ebay_profile->setReturnsPolicyConfiguration(
                pSQL(Tools::getValue('returnswithin')),
                pSQL(Tools::getValue('returnswhopays')),
                (Tools::nl2br(Tools::getValue('ebay_returns_description'))),
                pSQL(Tools::getValue('ebay_returns_accepted_option'))
            )
        ) {
            $products = EbayProduct::getProductsWithoutBlacklisted($this->ebay_profile->id_lang, $this->ebay_profile->id, true);

            foreach ($products as $product_id) {
                $product = new Product($product_id['id_product'], false, $this->ebay_profile->id_lang);
                EbayTaskManager::addTask('update',$product, null, $this->ebay_profile->id);
            }
                $link = new Link();
                $url = $link->getAdminLink('AdminModules');

            Tools::redirectAdmin($url.'&configure=ebay&module_name=ebay&id_tab=101&section=category#dashbord');
        } else {
            return $this->ebay->displayError($this->ebay->l('Settings failed'));
        }
    }

    private function _getListingDurations()
    {
        return array(
            'Days_1'  => $this->ebay->l('1 Day'),
            'Days_3'  => $this->ebay->l('3 Days'),
            'Days_5'  => $this->ebay->l('5 Days'),
            'Days_7'  => $this->ebay->l('7 Days'),
            'Days_10' => $this->ebay->l('10 Days'),
            'Days_30' => $this->ebay->l('30 Days'),
            'GTC'     => $this->ebay->l('Good \'Till Canceled')
        );
    }
}
