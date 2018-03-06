<?php

namespace app\models;

use yii\helpers\Url;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "users".
 *
 * @property integer $id
 * @property string $email
 * @property string $password
 * @property integer $status
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{

    const STATUS_EMAIL_CONFIRM = 5;
    const STATUS_EMAIL_NOT_CONFIRM = 0;

    const SCENARIO_SIGN_UP ='sign_up';

    public $rememberMe;
    public $password_repeat;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['email'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['email'], 'unique'],
            [['email', 'password'], 'required', 'message' => 'Пожалуйста заполните поле "{attribute}"'],
            [['password_repeat'], 'required', 'message' => 'Пожалуйста заполните поле "{attribute}"',  'on' => self::SCENARIO_SIGN_UP],
            [['password'], 'string', 'max' => 500],
            [['password'], 'compare', 'compareAttribute' => 'password_repeat', 'message' => 'пароли должны совпадать', 'on' => self::SCENARIO_SIGN_UP],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'email'           => 'Email',
            'password'        => 'Пароль',
            'status'          => 'Статус',
            'password_repeat' => 'повторите пароль',
            'rememberMe'      => 'оставаться в системе',
        ];
    }

    /**
     * @param $password
     * @return bool
     */
    public function validatePassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if($this->validate()) {
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);

            if ($this->isNewRecord) {
                $this->status = self::STATUS_EMAIL_NOT_CONFIRM;
            }
        }

        return parent::beforeSave($insert);
    }


    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if($this->isNewRecord) {
            $this->sendConfirmEmail();
        }

        parent::afterSave($insert, $changedAttributes);
    }


    /**
     * @param $hash string
     * @param $email string
     * @return bool
     */
    public static function confirmEmail($hash, $email)
    {
        $user = User::findOne([
            'password'   => $hash,
            'email'  => $email,
            'status' => self::STATUS_EMAIL_NOT_CONFIRM
        ]);

        if($user) {
            $user->status = self::STATUS_EMAIL_CONFIRM;
            return $user->save(false);
        }

        return false;
    }


    /**
     * @return bool
     */
    public function sendConfirmEmail()
    {
        $url = Url::toRoute(['site/confirm', 'hash' => $this->password, 'email' => $this->email], 'http');

        $sub = 'Подтверждение регистрации';
        $text = "подтвердить регистрацию --> <a href='$url'>Перейти</a>";

        return \Yii::$app->mailer->compose()
            ->setFrom('admin@test.haskicbr.ru')
            ->setTo($this->email)
            ->setSubject($sub)
            ->setTextBody($text)
            ->setHtmlBody($text)
            ->send();
    }


    /**
     * @param $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return self::findOne(['email' => $username]);
    }

    /**
     * @return string
     */
    public function getStatusString() {

        if($this->status == self::STATUS_EMAIL_NOT_CONFIRM) {
            return "Email не подтвержден";
        } else if ($this->status == self::STATUS_EMAIL_CONFIRM) {
            return "Email подтвержден";
        }

        return "Статус не распознан";
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public function getAuthKey()
    {
        return true;
    }

    public function validateAuthKey($authKey)
    {
        return true;
    }
}
