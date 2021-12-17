<?php

spl_autoload_register(/**
 * @param $class
 */ function ($class){
    $arr=['goods','interfaces','orders','reviews','serve','customer'];
    foreach ($arr as $val) {
        $path=__DIR__."/../$val/$class.php";
        if (file_exists($path))
            require_once $path;
    }
});

/**
 * Class OrderDetails
 */
class OrderDetails extends Review implements Damage
{
    /**
     * @var
     */
    private $art;
    /**
     * @var
     */
    private $quantity;

    /**
     * OrderDetails constructor.
     * @param $art
     * @param $quantity
     */
    public function __construct ($art, $quantity,$openion=NULL,$reviewDegree=NULL)
    {
        parent::__construct($openion,$reviewDegree);
        $this->art = $art;
        $this->quantity = $quantity;
    }


    /**
     * @return mixed
     */
    public function getart():art
    {
        return $this->art;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @param int $quantity
     */
    public function addQuantity(int $quantity){
        $this->quantity+=$quantity;
    }

    /**
     * @param mixed $art
     */
    public function setart($art): void
    {
        $this->art = $art;
    }

    /**
     *
     */
    public function damageAllData()
    {
        unset(
        $this->quantity,
        $this->art
        );
    }
    public function getDetailsPrice(){
        return $this->getart()->getSellPrice()*$this->quantity;
    }

}