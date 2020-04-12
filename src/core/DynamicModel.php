<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\core;

/**
 * Description.
 *
 * @author Maxim Chichkanov
 */
class DynamicModel extends \yii\base\DynamicModel
{
    /**
     * @var string
     */
    private $_formName;

    /**
     * @var array
     */
    private $_labels = [];

    /**
     * {@inheritdoc}
     */
    public function formName()
    {
        return $this->_formName ?? parent::formName();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return $this->_labels;
    }

    /**
     * Description.
     *
     * @param mixed $formName
     * @return void
     */
    public function setFormName($formName)
    {
        $this->_formName = $formName;
    }

    /**
     * Description.
     *
     * @param string $attribute
     * @param string $label
     * @return void
     */
    public function defineLabel($attribute, $label)
    {
        $this->_labels[$attribute] = $label;
    }
}
