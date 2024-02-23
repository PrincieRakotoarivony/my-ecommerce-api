<?php
namespace app\controllers;

use Yii;
use yii\rest\Controller;;
use app\models\User;
use app\models\UserDto;
use yii\web\UnprocessableEntityHttpException;
use yii\filters\VerbFilter;


class AuthController extends Controller{
    
    public function behaviors()
    {
        return [
            
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'login' => ['post'], //login and register must be only accessible with post method
                    'register' => ['post'] 
                ],
            ],
        ];
    }

    public function actionIndex(){
        return 'Welcome!';
    }
    
    public function actionLogin()
    {
        $model = new UserDto; // use default scenario
        $model->load(Yii::$app->request->post(), '');

        if (!$model->validate()) {
            
            Yii::$app->response->statusCode = 422; //unprocessable entity http exception;
            return ['errors' => $model->errors];
        }

        $auth_token = $model->login();
        return ['auth_token' => $auth_token];
    }

    public function actionRegister()
    {
        $model = new UserDto(['scenario' => UserDto::SCENARIO_REGISTER]); // use the scenario register for data validation
        $model->load(Yii::$app->request->post(), '');

        if (!$model->validate()) {
            
            Yii::$app->response->statusCode = 422; //unprocessable entity http exception;
            return ['errors' => $model->errors];
        }

        $user = $model->register();
        return ['message' => 'User created successfully', 'user_id' => $user->id];
    }
}