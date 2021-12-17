<?php
	/*
		loop through array of $_SESSION['cart'][art_isbn] => number
		get isbn => take from database => take art price
		price * number (quantity)
		return sum of price
	*/
	function total_price($cart){
		$price = 0.0;
		if(is_array($cart)){
		  	foreach($cart as $isbn => $qty){
		  		$artprice = getartprice($isbn);
		  		if($artprice){
		  			$price += $artprice * $qty;
		  		}
		  	}
		}
		return $price;
	}

	/*
		loop through array of $_SESSION['cart'][art_isbn] => number
		$_SESSION['cart'] is associative array which is [art_isbn] => number of arts for each art_isbn
		calculate sum of arts 
	*/
	function total_items($cart){
		$items = 0;
		if(is_array($cart)){
			foreach($cart as $isbn => $qty){
				$items += $qty;
			}
		}
		return $items;
	}
?>