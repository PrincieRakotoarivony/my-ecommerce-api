<?php

namespace app\models;
use Yii;
use yii\base\Model;

class Product extends Model
{
    public $id;
    public $name;
    public $reference;
    public $category;
    public $color;

    private static $products = null;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['name', 'reference', 'id', 'category', 'color'], 'required'],
        ];
    }

    // data storage path for product
    public static function getFilePath(){
        return Yii::getAlias('@app') . '/data/product.json';
    }

    //retrieve products from the json file
    public static function getProducts(){
        if(!self::$products){
            self::$products =  json_decode(file_get_contents(self::getFilePath()), true) ;
        }
        return self::$products;
    }

    //persist products in the json file
    public static function persistProducts(){
        file_put_contents(self::getFilePath(), json_encode(self::$products, JSON_PRETTY_PRINT));
    }

    //get products grouped by reference
    public static function getProductsGroupedByReference(){
        $products = self::getProducts();
        $result = [];
        foreach ($products as $product) {
            if(!isset($result[$product['reference']])){
                $result[$product['reference']] = [
                    'reference' => $product['reference'],
                    'name' => $product['name'],
                    'category' => $product['category'],
                    'color' => []
                ];
            }
            $data = $result[$product['reference']];
            if(!in_array($product['color'], $data['color'])){
                $data['color'][] = $product['color'];
            }
            $result[$product['reference']] = $data;
        }
        return array_values($result);
    }
}