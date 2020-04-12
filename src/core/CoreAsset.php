<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\core;

use common\interfaces\OptionsInterface;
use common\components\DataStorage;
use common\models\Data;
use common\helpers\Framework;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\web\View;
use yii\web\AssetBundle;

/**
 * Client application asset bundle.
 *
 * Config Keys:
 *  - defineGlobally <boolean>
 *  - globalName <string>
 *  - serviceWorker <string> <required>
 *  - push.publicKey <string> <required>
 *  - user.isGuestAction <string> <required>
 *  - backend.enabled <boolean>
 *  - frontend.enabled <boolean>
 *  - frontend.delay <number>
 *  - frontend.pushSubscriptionCheck <boolean>
 *  - frontend.pushSubscriptionCheckMethod <string>
 *  - frontend.pushSubscriptionCheckAction <string> <required>
 *  - frontend.pushSubscriptionConfirmMessage <string> <required>
 *  - frontend.pushSubscriptionRegisterAction <string>
 *  - frontend.pushSbuscriptionRegisterMethod <string> <required>
 *
 * Push Keys:
 * @public-key: BPgBgUpLdotDNdslWcWS-5qYh9N6viFbbVE05bHeNgP6ai4Y7yLz8jBfLIsqP8MAOO2-HsvWfxR5nSLZ37phnkQ
 * @private-key: IkXV6Yl_UxJRGIIon6XsSmgmvTdVIXhq9-7CBMLF-88
 *
 * @author Maxim Chichkanov <email>
 */
class CoreAsset extends AssetBundle
{
    public $sourcePath = '@common/core/assets';
    public $css = [
    ];
    public $js = [
        'js/bundle.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'common\widgets\bootstrap4\BootstrapPluginAsset'
    ];

    /**
     * @todo переместить в параметры кофигурации Yii::$app->params?
     */
    public static $config = [
        'defineGlobally' => true,
        'globalName' => 'app',
        'serviceWorker' => 'sw.js',
        'push.publicKey' => 'BPgBgUpLdotDNdslWcWS-5qYh9N6viFbbVE05bHeNgP6ai4Y7yLz8jBfLIsqP8MAOO2-HsvWfxR5nSLZ37phnkQ',
        'user.isGuestAction' => ['/service/is-guest'],
        'backend.enabled' => true,
        'frontend.enabled' => true,
        'frontend.delay' => 10000,
        'frontend.pushSubscriptionCheck' => true,
        'frontend.pushSubscriptionCheckAction' => [
            '/subscriptions/default/number-of-subscriptions',
            'type' => 'notification'
        ],
        'frontend.pushSubscriptionConfirmMessage' =>
            'В вашем аккаунте найдены подписки на уведомления, но '
          . 'на этом устройстве нет разрешения на показ уведомлений.'
          . '<br>'
          . 'Что бы вы могли получать уведомления на это устройство, '
          . 'нажимте кнопку "Разрешить" и согласитесь на показ уведомлений.',
        'frontend.pushSubscriptionRegisterAction' => ['/registry/push-subscription'],
        'frontend.pushSbuscriptionRegisterMethod' => 'POST'
    ];

    /**
     * Registers this asset bundle with a view.
     * @param View $view the view to be registered with
     * @return static the registered asset bundle instance
     */
    public static function register($view)
    {
        parent::register($view);

        /**
         * @todo
         */
        self::$config['user.isGuestAction'] =
            Url::to(self::$config['user.isGuestAction']);

        self::$config['frontend.pushSubscriptionCheckAction'] =
            Url::to(self::$config['frontend.pushSubscriptionCheckAction']);

        self::$config['frontend.pushSubscriptionRegisterAction'] =
            Url::to(self::$config['frontend.pushSubscriptionRegisterAction']);

        $clientConfig = Json::encode(self::$config);
        $view->registerJs("new Client($clientConfig);", View::POS_READY, self::class);
    }

    /**
     * @var array
     */
    public $config2 = [
        'defineGlobally' => 'corejs.defineGlobally',
        'globalName' => 'corejs.appName',
        'globalName' => 'corejs.globalVarName',
        'serviceWorker' => 'corejs.serviceWorker.scriptUrl',
        'push.publicKey' => 'corejs.modules.pushManager.publicKey',
        'user.isGuestAction' => 'corejs.modules.userManager.actions.isGuest',
        'backend.enabled' => 'corejs.modules.backendManager.enable',
        'frontend.enabled' => 'corejs.modules.frontendManager.enable',
        'frontend.delay' => 'corejs.modules.frontendManager.initializationDelay', // wtf?
        'frontend.pushSubscriptionCheck' => 'corejs.modules.frontendManager.pushSubscription.check',
        'frontend.delay' => 'corejs.modules.frontendManager.pushSubscription.checkDelay',
        'frontend.pushSubscriptionCheckAction' => 'corejs.modules.frontendManager.pushSubscription.checkAction',
        'frontend.pushSubscriptionConfirmMessage' => 'corejs.modules.frontendManager.pushSubscription.confirmMessage',
        'frontend.pushSubscriptionRegisterAction' => 'corejs.modules.frontendManager.pushSubscription.registerAction',
        'frontend.pushSbuscriptionRegisterMethod' => 'corejs.modules.frontendManager.pushSubscription.registerMethod'
    ];

    /**
     * Registers this asset bundle with a view.
     * @param View $view the view to be registered with
     * @return static the registered asset bundle instance
     */
    public static function register2($view)
    {
        $dataStorage = Framework::getComponent(DataStorage::class);

        if (!$dataStorage) {
            return;
        }

        /** @var DataStorage $dataStorage */

        $config = [];

        foreach ($dataStorage->findData(['tag' => 'corejs']) as $data) {

            /** @var Data&OptionsInterface $data */

            $key = $data->key;
            $value = $data->value;

            $prefix = $data->getOptions('prefix', 'corejs.');
            $isUrl = $data->getOptions('isUrl', false);
            $url = $data->getOptions('url', $value);

            // trim prefix
            if (strlen($key) > strlen($prefix) && strpos(strtolower($key), $prefix) === 0) {
                $key = substr($key, 0, strlen($prefix));
            }

            // url
            if ($isUrl) {
                $value = Url::to($url);
            }

            $config[$key] = $value;
        }

        $jsonConfig = Json::encode($config);
        $view->registerJs("new Client($jsonConfig);", View::POS_READY, static::class);
    }
}
