<?php
/**
 * 2007-2021 PrestaShop
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
 * @author 202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 2007-2021 202-ecommerce
 * @license Commercial license
 *  International Registered Trademark & Property of PrestaShop SA
 */

class AdminEbayOrdersController extends ModuleAdminController
{
    public function ajaxProcessLoadOrderHistory()
    {
        $pagination = EbayOrder::getPaginatedOrdersErrors(Tools::getValue('id_ebay_profile'), Tools::getValue('page'));
        $context = Context::getContext();
        $template_vars = array(
            'id_ebay_profile' => Tools::getValue('id_ebay_profile'),
            'id_employee' => Tools::getValue('id_employee'),
            'currency' => $context->currency,
            );

        $ebay = Module::getInstanceByName('ebay');
        $context->smarty->assign($template_vars);
        $context->smarty->assign($pagination);
        die($ebay->display(realpath(dirname(__FILE__) . '/../../'), '/views/templates/hook/tableOrders_ajax.tpl'));
    }

    public function ajaxProcessResyncOrder()
    {
        $ebay = Module::getInstanceByName('ebay');

        $order =  EbayOrder::getEbayOrder(Tools::getValue('id_order_ebay'));
        if ($order) {
            EbayOrderErrors::deleteByOrderRef(Tools::getValue('id_order_ebay'));
        }

        $ebay->importOrders($order, $ebay_profile = new EbayProfile(Tools::getValue('id_ebay_profile')));
        $new_log = EbayOrderErrors::getErrorByOrderRef(Tools::getValue('id_order_ebay'));

        if ($new_log) {
            foreach ($new_log as $log) {
                $vars = array(
                    'date_ebay' => $log['date_order'],
                    'reference_ebay' => $log['id_order_ebay'],
                    'referance_marchand' => $log['id_order_seller'],
                    'email' => $log['email'],
                    'total' => $log['total'],
                    'error' => $log['error'],
                    'date_import' => $log['date_add'],
                );
            }
        } else {
            $orders = EbayOrder::getOrderByOrderRef(Tools::getValue('id_order_ebay'));
            foreach ($orders as $ord) {
                $order = new Order($ord['id_order']);

                $vars = array(
                    'date_ebay' => $order->date_add,
                    'reference_ebay'  => $ord['id_order_ref'],
                    'referance_marchand' => $order->payment,
                    'email' => $order->getCustomer()->email,
                    'total' => $order->total_paid,
                    'id_prestashop' => $order->id,
                    'reference_ps' => $order->reference,
                    'date_import' => $order->date_add,
                );
            }
        }

        die(Tools::jsonEncode($vars));
    }

    public function ajaxProcessDeleteOrderError()
    {
        $reference_ebay = Tools::getValue('reference_ebay') ? Tools::getValue('reference_ebay') : '';
        EbayOrderErrors::deleteByOrderRef($reference_ebay);
        die();
    }

    public function ajaxProcessLoadReturnsHistory()
    {
        $pagination = EbayOrder::getPaginatedOrderReturns(Tools::getValue('page'));
        $context = Context::getContext();
        $template_vars = array(
            'id_ebay_profile' => Tools::getValue('id_ebay_profile'),
            'id_employee' => Tools::getValue('id_employee')
        );

        $ebay = Module::getInstanceByName('ebay');
        $context->smarty->assign($template_vars);
        $context->smarty->assign($pagination);
        die($ebay->display(realpath(dirname(__FILE__) . '/../../'), '/views/templates/hook/table_returns_ajax.tpl'));
    }
}