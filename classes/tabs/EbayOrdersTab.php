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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2017 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class EbayOrdersTab extends EbayTab
{

    public function getContent()
    {

        $orders_error = EbayOrderErrors::getAll();
        $orders = EbayOrder::getOrders();
        $vars = array();
        if (!empty($orders_error)) {

            foreach ($orders_error as $order_er) {
                $vars['errors'][] = array(
                    'date_ebay' => $order_er['date_order'],
                    'reference_ebay' => $order_er['id_order_ebay'],
                    'referance_marchand' => $order_er['id_order_seller'],
                    'email' => $order_er['email'],
                    'total' => $order_er['total'],
                    'error' => $order_er['error'],
                    'date_import' => $order_er['date_add'],
                );
            }
        }
        if (!empty($orders)){
        foreach ($orders as $ord) {
            $order = new Order($ord['id_order']);

            $vars['orders_tab'][] = array(
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
        return $this->display('tableOrders.tpl', $vars);
    }
}
