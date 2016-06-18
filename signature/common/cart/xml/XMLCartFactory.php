<?php
require_once(libraries_get_path('pay_with_amazon') . "/signature/common/cart/CartFactory.php");
                                                                                                                                                            /**
 * Returns a simple static cart to generate a signature from,
 * and the final complete cart html.
 *
 * Copyright 2008-2011 Amazon.com, Inc., or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *    http://aws.amazon.com/apache2.0/
 *
 * or in the "license" file accompanying this file.
 * This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific language governing permissions and limitations under the License.
 */
class XMLCartFactory extends CartFactory {
   protected static $CART_ORDER_INPUT_FIELD ="type:merchant-signed-order/aws-accesskey/1;order:[ORDER];signature:[SIGNATURE];aws-access-key-id:[AWS_ACCESS_KEY_ID]";
	
   public function XMLCartFactory() {
   }

   /**
    * Gets cart html fragment used to generate entire cart html
    * Base 64 encode the cart.
    * 
    */
    public function getCart($merchantID, $awsAccessKeyID) {
        $cartXML = $this->getCartXML($merchantID, $awsAccessKeyID);
        return base64_encode($cartXML);
    }
   
   /**
    * Returns the concatenated cart used for signature generation.
    * @see CartFactory
    */
   public function getSignatureInput($merchantID, $awsAccessKeyID) {
        return $this->getCartXML($merchantID, $awsAccessKeyID);
   }

   /**
    * Returns a finalized full cart html including the base 64 encoded cart,
    * signature, and buy button image link.
    */
   public function getCartHTML($merchantID, $awsAccessKeyID, $signature) {
        $cartHTML = '';

	$cartHTML = $cartHTML . CartFactory::$CART_JAVASCRIPT_START;
	$cartHTML = $cartHTML . CartFactory::$CBA_BUTTON_DIV;       
	// construct the order-input section
	$encodedCart = $this->getCart($merchantID, $awsAccessKeyID);
        $input = preg_replace("\\[ORDER\\]", $encodedCart, XMLCartFactory::$CART_ORDER_INPUT_FIELD);
        $input = preg_replace("\\[SIGNATURE\\]", $signature, $input);
        $input = preg_replace("\\[AWS_ACCESS_KEY_ID\\]", $awsAccessKeyID, $input);
        $widgetScript = preg_replace("\\[CART_TYPE\\]", "XML",CartFactory::$STANDARD_CHECKOUT_WIDGET_SCRIPT);
        $widgetScript = preg_replace("\\[MERCHANT_ID\\]", $merchantID,$widgetScript);
        $widgetScript =preg_replace ("\\[CART_VALUE\\]",$input ,$widgetScript);

        $cartHTML = $cartHTML . $widgetScript;        

	return $cartHTML;
   }
	
    /**
     * Replace with your own cart here to try out
     * different promotions, tax, shipping, etc. 
     * 
     * @param merchantID
     * @param awsAccessKeyID
     */
    private function getCartXML($merchantID, $awsAccessKeyID) {
      $xml =
	"<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
	"<Order xmlns=\"http://payments.amazon.com/checkout/2008-06-15/\">" .
	"    <ClientRequestId>123457</ClientRequestId>" .
	"    <Cart>" .
	"    <Items>";
      global $user;
      $cart = commerce_cart_order_load($user->uid);
      foreach ($cart->commerce_line_items['und'] as $line_item_id) {
	$line_item = commerce_line_item_load($line_item_id);
	list($quantity) = explode('.', $line_item->quantity);
	$xml .=  "      <Item>" .
	  "         <SKU>" . $line_item->line_item_label . "</SKU>" .
	  "         <MerchantId>" . $merchantID . "</MerchantId>" .
	  "         <Title>" . $line_item->line_item_label . "</Title>" .
	  "         <Description>" . $line_item->line_item_label . "</Description>" .
	  "         <Price>" .
	  "            <Amount>" . (0 + $line_item->commerce_unit_price['und'][0]['amount'] / 100) . "</Amount>" .
	  "            <CurrencyCode>INR</CurrencyCode>" .
	  "         </Price>" .
	  "         <Quantity> " . $quantity . "</Quantity>" .
	  "         <Weight>" .
	  "            <Amount>.5</Amount>" .
	  "            <Unit>kg</Unit>" .
	  "         </Weight>" .
	  "         <Category>Tshirts</Category>" .
	  "      </Item>" ;

      }
      $xml .=
	"    </Items>" .
	"    </Cart>" .
	"</Order>";
	return $xml;
    }
}
