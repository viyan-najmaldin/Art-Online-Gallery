<?php
/**
 * this class  coded  by Ahmed  Embaby in  25  SEP  2019
 */

class UnAvailableOrderDetails extends OrderDetails
{
   public function __construct ($art, $quantity, $openion = NULL, $reviewDegree = NULL)
   {
       parent::__construct($art, $quantity, $openion, $reviewDegree);
   }


}