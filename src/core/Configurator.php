<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\core;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Description
 *
 * @author Maxim Chichkanov <email>
 */
class Configurator extends \ArrayObject
{
    /**
     * @var string $_path Рабочая директория компонента.
     * В этом каталоге сохраняются все сериализованные
     * конфигурационные файлы.
     */
    private $_path;

    /**
     * Конструктор.
     */
    public function __construct(array $files = [], array $dependens = [], $path = '@common/runtime/configs')
    {
        Yii::setAlias('@configurator', __DIR__);

        $this->_path = FileHelper::normalizePath(
            Yii::getAlias($path)
        );
        FileHelper::createDirectory($this->_path);

        foreach ($files as $file) {
            $dependens[] = filemtime($file);
        }

        $filename = md5(serialize($dependens));

        if (!($config = $this->getConfig($filename))) {
            $config = $this->includes($files);
            $this->setConfig($filename, $config);
        }

        parent::__construct($config);
    }

    /**
     * Читает и десериализует файл конфигурации.
     * @param string $name Имя файла конфигурации.
     * @return array Массив конфигурации.
     */
    public function getConfig($name)
    {
        $filename = implode(DIRECTORY_SEPARATOR, [$this->_path, $name]);

        if (file_exists($filename)) {
            return unserialize(file_get_contents($filename));
        }
    }

    /**
     * Сериализует и записывает конфигурацию в файл.
     * @param string $name Имя файла конфигурации.
     * @param array $config Массив конфигурации.
     * @param boolean $replace Если файл с таким именем
     * уже существует, данные будут перезаписанны.
     */
    public function setConfig($name, $config, $replace = true)
    {
        $filename = implode(DIRECTORY_SEPARATOR, [$this->_path, $name]);

        if (!file_exists($filename) || $replace) {
            file_put_contents($filename, serialize($config));
        }
    }

    /**
     * Подключает конфигурационные файлы и возвращает результат их слияния.
     * @param array $files Массив имён файлов конфигурации.
     * @return array Результат слияния файлов конфигурации.
     */
    public static function includes($files = [])
    {
        $configs = [];
        foreach ($files as $file) {
            $configs[] = require($file);
        }

        return ArrayHelper::merge(...$configs);
    }
}
