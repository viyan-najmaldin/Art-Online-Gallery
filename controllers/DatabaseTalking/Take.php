<?php

spl_autoload_register(function ($class){
    $arr=[__DIR__.'/../../models/goods',
        __DIR__.'/../../models/orders',
        __DIR__.'/../../models/reviews',
        __DIR__.'/../../models/customer',
        './',
    ];
    foreach ($arr as $val) {
        $path="$val/$class.php";
        if (file_exists($path))
            require_once $path;
    }
});


class Take extends Talking
{
    /**
     * @param Orders $order
     * insert all data of the order
     * includes the customer that make the order and his address data
     * also all the orderDetails related to this order.
     * @return false only if any error happened or  no orderDetails had been found in object $order
     */
    public function takeorder (Orders $order,&$orderNumber=null):bool
    {
        try{
            $customer=$order->getCustomer();
            $address=$customer->getAddress();
            $addressId=rand(1, 1000000000);
            $customerId=rand(1, 1000000000);
            $orderId=rand(1, 1000000000);
            $orderNumber=$orderId;
           $this->conn->beginTransaction();
           //insert address of the customer
           $this->stat=$this->conn->prepare('INSERT INTO address VALUES (?,?,?,?,?)');
           $this->stat->execute(array(
               0=> $addressId,
               1=> $address->getCountry(),
               2=>$address->getCity(),
               3=>$address->getStreet(),
               4=>$address->getBuildNo()));
           //insert the customer data
           $this->stat=$this->conn->prepare('INSERT INTO customer (customer_id, name, address_id, email, phone, password) VALUES (?,?,?,?,?,?)');
           $this->stat->execute(array(
               0=>$customerId,
               1=>$customer->getName(),
               2=>$addressId,
               3=>$customer->getEmail(),
               4=>$customer->getPhone(),
               5=>$customer->getPassword()
           ));
           /**
             * insert order data.
             * order_Date DEFAULT is  CURRENT_TIMESTAMP().
             * delivering_date DEFAULT is null.
             *  by NULL value it's possible to know whether the order delivered or not .
             */
           $this->stat=$this->conn->prepare('INSERT INTO orders (order_id, customer_id) values (?,?)');
           $this->stat->execute(array(
               0=>$orderId,
               1=>$customerId
           ));
          
           if (count($order->getOrderDetails())) {
               foreach ($order->getOrderDetails() as $id => $detail) {
                   $this->insertANOrderDetails($orderId,$detail);
                   sleep(.0000001);
               }
           }
           else {
               $this->conn->rollBack();
               return false;
           }
           //no errors or empty OrderDetails then commit
           return $this->conn->commit();
        }catch (PDOException $PDOException){
           $this->conn->rollBack();
           echo $PDOException->getMessage();
           return false;
       }
    }

    private function insertANOrderDetails($orderId,OrderDetails $detail){
        try{
            $this->stat = $this->conn->prepare('INSERT INTO order_details (order_details_id, order_id, art_id,quantity) VALUES (?,?,?,?)');
            $this->stat->execute(array(
                0 => rand(1, 1000000000),
                1 => $orderId,
                2 => $detail->getart()->getId(),
                3=>$detail->getQuantity()
            ));
            $this->stat=$this->conn->prepare("
            UPDATE art set quantity=(Select quantity from art where art_id=?)-? where art_id=?");
            $this->stat->execute(array(
                    0=>$detail->getart()->getId(),
                    1=>$detail->getQuantity(),
                    2 =>$detail->getart()->getId()
                )
            );
        }catch (PDOException $PDOException){
            $this->conn->rollBack();
            echo $PDOException->getMessage();
        }
    }
    private function undoOrder($orderId) {
        try{
            $this->stat=$this->conn->prepare("
            UPDATE  art b SET quantity=(
            SELECT o.quantity
            FROM  order_details o JOIN orders o3 on o.order_id = o3.order_id
            WHERE o3.order_id=? AND b.art_id=o.art_id )+(
            SELECT b.quantity FROM art c WHERE c.art_id=b.art_id
    )
       WHERE b.art_id in (
            SELECT x.art_id
            FROM art x JOIN order_details od on x.art_id = od.art_id
            JOIN orders o2 on od.order_id = o2.order_id
            WHERE o2.order_id=? );
            ");
            $this->stat->execute([0=>$orderId,1=>$orderId]);
            $this->stat=$this->conn->prepare("
            DELETE FROM order_details WHERE order_id=?
            ");
            $this->stat->execute([0=>$orderId]);
        }catch (PDOException $exception){
            $this->conn->rollBack();
        }
    }

    public function updateOrder($orderId,Orders $order):bool {
        try{
            $this->conn->beginTransaction();
            $this->undoOrder($orderId);
            foreach ($order->getOrderDetails() as $detail)
                $this->insertANOrderDetails($orderId,$detail);
            $this->conn->commit();
            return true;
        }catch (PDOException $PDOException){
            $this->conn->rollBack();
            echo $PDOException->getMessage();
            return false;
        }

    }


    public function updateCustomer($orderId,Customers $customer){

    }

    /**
     * @param art $art
     * insert the art into DB .
     * @return true if the the art inserted successfully else it return false.
     */

    public function takeart(art $art):bool {
        try{
            $this->stat=$this->conn->prepare("
            insert into art(art_id, title, artist_id, publisher_id, genre_id, ISBN, sell_price, buy_price, image, description, quantity, actual_quantity) 
            values (?,?,?,?,?,?,?,?,?,?,?,?);");
            $this->stat->bindValue(1,$art->getId(),PDO::PARAM_INT);
            $this->stat->bindValue(2,$art->getName());
            $this->stat->bindValue(3,$art->getAuthor()->getId(),PDO::PARAM_INT);
            $this->stat->bindValue(4,$art->getPublisher()->getId(),PDO::PARAM_INT);
            $this->stat->bindValue(5,$art->getGenre()->getId(),PDO::PARAM_INT);
            $this->stat->bindValue(6,$art->getIsbn(),PDO::PARAM_INT);
            $this->stat->bindValue(7,$art->getSellPrice());
            $this->stat->bindValue(8,$art->getBuyPrice());
            $this->stat->bindValue(9,$art->getImage());
            $this->stat->bindValue(10,$art->getDescription());
            $this->stat->bindValue(11,$art->getQuantity(),PDO::PARAM_INT);
            $this->stat->bindValue(12,$art->getActualQuantity(),PDO::PARAM_INT);
            $this->stat->execute();
            return true;
        }catch (PDOException $PDOException){
           echo $PDOException->getMessage();
           return false;
        }

    }

    /**
     * @param art $art
     * @return bool
     *update art without updating the image but it delete the old image
     */

    public function updateart(art $art):bool{
        try{

            $this->stat=$this->conn->prepare("SELECT image FROM art WHERE art_id=?");
            $this->stat->execute([0=>$art->getId()]);
            $row=$this->stat->fetch(PDO::FETCH_ASSOC);
            //delete the old image
            $this->deleteImage(__DIR__.'/../../images/'.$row['image']);
            $this->stat=$this->conn->prepare("
            UPDATE art SET artist_id=?,publisher_id=?,buy_price=?,sell_price=?,quantity=?,
            actual_quantity=?,image=? WHERE art_id=?;  
            ");
            $this->stat->bindValue(1,$art->getAuthor()->getId(),PDO::PARAM_INT);
            $this->stat->bindValue(2,$art->getPublisher()->getId(),PDO::PARAM_INT);
            $this->stat->bindValue(3,$art->getBuyPrice());
            $this->stat->bindValue(4,$art->getSellPrice());
            $this->stat->bindValue(5,$art->getQuantity(),PDO::PARAM_INT);
            $this->stat->bindValue(6,$art->getActualQuantity(),PDO::PARAM_INT);
            $this->stat->bindValue(7,$art->getImage(),PDO::PARAM_INT);
            $this->stat->bindValue(8,$art->getId(),PDO::PARAM_INT);

            return $this->stat->execute();
        }catch(PDOException $exception){
            echo $exception->getMessage();
            return false;
        }

    }

}