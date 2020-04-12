<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\core\editors;

use Yii;

/**
 * Description
 *
 * @author Maxim Chichkanov
 */
trait PresetTrait
{
    /**
     * @var string Description.
     */
    public $preset = 'default';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        Yii::configure($this, $this->getPreset());
        parent::init();
    }

    /**
     * Description.
     * @return array
     */
    abstract public function getPreset();
}
