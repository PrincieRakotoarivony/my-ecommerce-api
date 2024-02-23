<?php

namespace app\models;
use Yii;
use yii\base\Model;

class User extends Model implements \yii\web\IdentityInterface
{
    public $id;
    public $username;
    public $password_hash;
    public $auth_token;

    private static $users = null;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password_hash', 'id'], 'required'],
            [['auth_token'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return isset(self::getUsers()[$id]) ? new static(self::getUsers()[$id]) : null;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        foreach (self::getUsers() as $user) {
            if ($user['auth_token'] === $token) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        foreach (self::getUsers() as $user) {
            if (strcasecmp($user['username'], $username) === 0) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return true;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }

    // generate unique rest token for user
    public function generateAuthToken(){
        $prefix = $this->id . '_' . $this->username . '_' . microtime(true);
        $this->auth_token = sha1(uniqid($prefix));
        $this->save();
        return $this->auth_token;
    }

    //save the user in the data storage 
    public function save(){
        $users = self::getUsers();
        $users[''.$this->id] = $this->attributes;
        self::$users = $users;
        $this->persistUsers();
    }

    //get next user id
    public static function getNextId(){
        $users = self::getUsers();
        $ids = array_keys($users);
        if(count($ids) == 0) return 1;
        return intval($ids[count($ids) - 1]) + 1;
    }

    // user data storage path
    public static function getFilePath(){
        return Yii::getAlias('@app') . '/data/user.json';
    }

    //get list of users, if null then retrieve them from data storage
    public static function getUsers(){
        if(!self::$users){
            self::$users =  json_decode(file_get_contents(self::getFilePath()), true) ;
        }
        return self::$users;
    }

    //persist users in the data storage
    public static function persistUsers(){
        file_put_contents(self::getFilePath(), json_encode(self::$users, JSON_PRETTY_PRINT));
    }
}
