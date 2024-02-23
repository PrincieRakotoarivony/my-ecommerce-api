<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UnauthorizedHttpException;
use yii\web\ServerErrorHttpException;


/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class UserDto extends Model
{
    public $username;
    public $password;
    const SCENARIO_REGISTER = 'register';

    private $_user = false;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            [['username'], 'validateUniqueUsername', 'on' => self::SCENARIO_REGISTER] // on register, username must be unique
        ];
    }

    // login the user, return new auth_token 
    public function login()
    {
        $user = $this->getUser();
        if (!$user || !$user->validatePassword($this->password)) {
            throw new UnauthorizedHttpException('Invalid username or password');
        }
        return $user->generateAuthToken();
    }

    // register the user, return the new user registered
    public function register()
    {
        
        $model = new User;
        $data = [
            'password_hash' => Yii::$app->security->generatePasswordHash($this->password),
            'username' => $this->username,
            'id' => User::getNextId()
        ];

        $model->load($data, '');
        $model->save();
        return $model;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }

    // on register, username must be unique
    public function validateUniqueUsername($attribute, $params)
    {
        $userExist = User::findByUsername($this->$attribute);
        if ($userExist) {
            $this->addError($attribute, 'This username is already taken.');
            return;
        }
    }
}