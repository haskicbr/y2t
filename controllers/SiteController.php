<?php

namespace app\controllers;

use app\models\LoginForm;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only'  => ['login', 'logout', 'signup'],
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => ['login', 'signup'],
                        'roles'   => ['?'],
                    ],
                    [
                        'allow'   => true,
                        'actions' => ['logout'],
                        'roles'   => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error'   => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }


    public function actionRegistration()
    {
        $model = new User;
        $model->setScenario(User::SCENARIO_SIGN_UP);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            if (Yii::$app->user->login($model)) {
                return $this->goHome();
            };
        }

        return $this->render('registration', [
            'model' => $model
        ]);
    }

    /**
     * @param $hash
     * @param $email
     * @throws NotFoundHttpException
     */
    public function actionConfirm($hash, $email)
    {
        if (User::confirmEmail($hash, $email)) {
            $this->redirect('/msg', [
                'msg' => 'регистрация подтверждена'
            ]);
        };

        throw new NotFoundHttpException('Страница не найдена');
    }


    /**
     * @param $msg
     * @return string
     */
    public function actionMsg($msg)
    {
        return $this->render('msg', [
            'msg' => $msg
        ]);
    }

}
