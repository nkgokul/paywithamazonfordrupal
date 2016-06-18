<?php
require_once(libraries_get_path('pay_with_amazon') . "/signature/common/cart/CartFactory.php");


/**
 * Abstract class that contains utility methods for converting a map of url
 * parameters to its string representation for use with signature generation.
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
abstract class HTMLCartFactory extends CartFactory {
   protected static $CART_FORM_INPUT_FIELD = "<input type=\"hidden\" name=\"[KEY]\" value=\"[VALUE]\" />\n";
   /**
    * rawurlencode follows RFC-3986 while we need RFC-2396
    *
    */
   private static   $encodedValuesToReplacementMap = array(
							   "%21" => "!",
                                                           "%2A" => "*",
                                                           "%27" => "'",
                                                           "%28" => "(",
                                                           "%29" => ")",
							   "%7E" => "~"
							);       
   /**
    * Instantiate an instance of the cart factory.
    */
   public function HTMLCartFactory()
   {
   }


   /**
    * Get map representation of the cart.
    */
   protected function getCartFromMap($merchantID, $awsAccessKeyID, $parameterMap) {
      $cart = '';

      foreach ($parameterMap as $key => $value) {
         $input = preg_replace ("\\[KEY\\]", $key, HTMLCartFactory::$CART_FORM_INPUT_FIELD);
         $input = preg_replace ("\\[VALUE\\]", htmlentities( $value,ENT_QUOTES,"UTF-8"), $input);

         $cart = $cart . $input;
      }

      return $cart;
   }


   /**
    * Generates the finalized cart html, including javascript headers, cart contents,
    * signature and button.
    *
    * NOTE: Inheritence doesn't work correctly in php, (base class can not call 
    * derived classes' implementation of an abstract class). Therefore, we have 
    * to rename this function from 'getCartHTML' and do other workarounds.
    */
   protected function getCartHTMLFromCartInput($merchantID, $awsAccessKeyID, $signature, $cartInput) {
      $cartHTML = '';
      $cartHTML = $cartHTML . CartFactory::$CART_JAVASCRIPT_START;
      $cartHTML = $cartHTML . CartFactory::$CBA_BUTTON_DIV;
      $cartHTML = $cartHTML . preg_replace ("\\[MERCHANT_ID\\]", $merchantID, CartFactory::$CART_FORM_START);
      $cartHTML = $cartHTML . $cartInput;
      $cartHTML = $cartHTML . preg_replace ("\\[SIGNATURE\\]", $signature, CartFactory::$CART_FORM_SIGNATURE_INPUT_FIELD);
      $cartHTML = $cartHTML . CartFactory::$CART_FORM_END;
      $widgetScript = preg_replace ("\\[CART_VALUE\\]","CBACartForm",CartFactory::$STANDARD_CHECKOUT_WIDGET_SCRIPT);
      $widgetScript = preg_replace("\\[CART_TYPE\\]", "HTML",$widgetScript);
      $widgetScript = preg_replace("\\[MERCHANT_ID\\]", $merchantID,$widgetScript);
      $cartHTML = $cartHTML . $widgetScript;
      return $cartHTML;
   }


   /**
    *
    */
   protected function getSignatureInputFromMap($parameterMap) {
       $input = '';
       foreach ($parameterMap as $key => $value) {
       /**
	* encode the parameter values as per RFC-2396
	*/
	  $encodedValue = strtr(rawurlencode($value),HTMLCartFactory::$encodedValuesToReplacementMap); 
          $input = $input . $key . '=' . $encodedValue . '&';
       }

       return $input;
   }
 
   protected abstract function getCartMap($merchantID, $awsAccessKeyID);
}
?>
