<?php
namespace app\controllers;

use Yii;
use yii\rest\Controller;;
use app\models\Product;


class ProductController extends Controller{
    public function actionIndex(){
        return Product::getProductsGroupedByReference();
    }
}