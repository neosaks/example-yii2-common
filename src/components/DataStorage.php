<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\components;

use yii\base\Component;
use yii\base\BootstrapInterface;
use yii\web\Application;
use yii\helpers\ArrayHelper;
use common\interfaces\VarsInterface;
use common\models\Data;

/**
 * Description
 *
 * @author Maximm Chichkanov
 */
class DataStorage extends Component implements BootstrapInterface, VarsInterface
{
    /**
     * @var Data[]
     */
    protected $data = [];

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     * @return void
     */
    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            // code
        }
    }

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        $this->data = Data::find()
            ->where(['status' => Data::STATUS_ACTIVE])
            ->indexBy('key')
            ->all();
    }

    /**
     * Description.
     *
     * @param array $condition
     * @return Data[]
     */
    public function findData($condition)
    {
        $result = [];
        foreach ($this->data as $data) {
            foreach ($condition as $name => $value) {
                if (!isset($data[$name])) {
                    continue 2;
                }

                if ($data[$name] !== $value) {
                    continue 2;
                }
            }
            $result[] = $data;
        }
        return $result;
    }

    /**
     * Description.
     *
     * @param array $condition
     * @return mixed[]
     */
    public function findValues($condition)
    {
        $result = [];
        foreach ($this->findData($condition) as $data) {
            $result[] = $data['value'];
        }
        return $result;
    }

    /**
     * Description.
     *
     * @param string $key
     * @param string $defaultValue
     * @param array $options
     * @return mixed
     */
    public function get($key, $defaultValue = null, $options = [])
    {
        if (isset($this->data[$key])) {
            return $this->data[$key]['value'];
        }

        return $defaultValue;
    }

    /**
     * Description.
     *
     * @param string $key
     * @param mixed $value
     * @param array $options
     * @return boolean
     */
    public function set($key, $value, $options = [])
    {
        $label = ArrayHelper::getValue($options, 'label');
        $description = ArrayHelper::getValue($options, 'description');
        $modifier = ArrayHelper::getValue($options, 'modifier');
        $type = ArrayHelper::getValue($options, 'type', 'input');
        $overwrite = ArrayHelper::getValue($options, 'overwrite', true);

        $data = isset($this->data[$key]) ? $this->data[$key] : new Data();

        if (!$data->getIsNewRecord() && $overwrite === false) {
            return true;
        }

        $data->key = $key;
        $data->value = $value;

        if ($label !== null) {
            $data->label = $label;
        }

        if ($description !== null) {
            $data->description = $description;
        }

        if ($modifier !== null) {
            $data->modifier = $modifier;
        }

        if ($type !== null) {
            $data->type = $type;
        }

        if ($data->save()) {
            $this->data[$key] = $data;
        }

        return !$data->getIsNewRecord();
    }

    /**
     * Description.
     *
     * @return array
     */
    public function getVars()
    {
        $collectedVars = [];

        foreach ($this->data as $data) {
            if ($data->modifier === Data::MODIFIER_PUBLIC) {
                $collectedVars[$data->key] = $data->value;
            }
        }

        return $collectedVars;
    }
}
