<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\helpers;

use yii\helpers\Html;

/**
 * Site helper
 *
 * @author Maxim Chichkanov
 */
class SiteHelper
{
    /**
     * @var array|string|null
     */
    public static $linkPrivacy = ['/pages/default/view', 'id' => 'privacy-policy'];

    /**
     * @var array|string|null
     */
    public static $linkPolicy = ['/pages/default/view', 'id' => 'terms-of-use'];

    /**
     * @var array
     */
    public static $linkOptions = [
        'target' => '_blank',
        'data-pjax' => 0
    ];

    /**
     *
     */
    public static function getAgreementLabel()
    {
        $policy = Html::a('персональных данных', self::$linkPrivacy, self::$linkOptions);
        $privacy = Html::a('политикой конфиденциальности', self::$linkPolicy, self::$linkOptions);
        return "Даю согласие на обработку $policy и соглашаюсь с $privacy";
    }
}
