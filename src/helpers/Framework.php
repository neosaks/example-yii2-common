<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\helpers;

use Yii;
use yii\base\Component;
use yii\di\ServiceLocator;

/**
 * Yii2 framework helper.
 *
 * @author Maxim Chichkanov <neosaks@mail.ru>
 */
class Framework
{
    /**
     * @param string $className
     * @param Component $component
     * @return boolean
     */
    public static function isAttached($className, Component $component)
    {
        foreach ($component->getBehaviors() as $behavior) {
            if (get_class($behavior) === $className) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $type
     * @return false|string
     */
    public static function getClass($type)
    {
        if (is_string($type)) {
            return $type;
        }

        if (!is_array($type)) {
            return false;
        }

        if (isset($type['__class'])) {
            return $type['__class'];
        }

        if (isset($type['class'])) {
            return $type['class'];
        }

        return false;
    }

    /**
     * @param string $className
     * @param ServiceLocator $serviceLocator
     * @return object|null
     * @throws InvalidConfigException
     */
    public static function getComponent($className, ServiceLocator $serviceLocator = null)
    {
        if (!$serviceLocator) {
            $serviceLocator = Yii::$app;
        }

        if (($id = self::getComponentId($className, $serviceLocator))) {
            return $serviceLocator->get($id);
        }
    }

    /**
     * @param string $className
     * @param ServiceLocator $serviceLocator
     * @return string|false
     */
    public static function getComponentId($className, ServiceLocator $serviceLocator = null)
    {
        if (!$serviceLocator) {
            $serviceLocator = Yii::$app;
        }

        $components = $serviceLocator->getComponents();
        return self::findIdByClassName($className, $components);
    }

    /**
     * @param string $className
     * @return \yii\base\Module|null
     */
    public static function getModule($className)
    {
        if (($id = self::getModuleId($className))) {
            return Yii::$app->getModule($id);
        }
    }

    /**
     * @param string $className
     * @return string|false
     */
    public static function getModuleId($className)
    {
        $modules = Yii::$app->getModules();
        return self::findIdByClassName($className, $modules);
    }

    /**
     * @param string $className
     * @param array $haystack
     * @return mixed
     */
    public static function findIdByClassName($className, $haystack)
    {
        foreach ($haystack as $id => $params) {
            if (is_array($params) && isset($params['class'])) {
                $params = $params['class'];
            }

            if (isset($params) && (is_string($params) || is_object($params))) {
                if ($params instanceof $className || $params === $className) {
                    return $id;
                }
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public static function getCurrentLayoutFile()
    {
        return Yii::$app->controller->findLayoutFile(Yii::$app->controller->getView());
    }

    /**
     * @return string
     */
    public static function getMainLayoutFile()
    {
        $file = Yii::$app->getLayoutPath() . DIRECTORY_SEPARATOR . Yii::$app->layout;

        if (pathinfo($file, PATHINFO_EXTENSION) !== '') {
            return $file;
        }
        $path = $file . '.' . Yii::$app->getView()->defaultExtension;
        if (Yii::$app->getView()->defaultExtension !== 'php' && !is_file($path)) {
            $path = $file . '.php';
        }

        return $path;
    }

    /**
     * @param string $module
     * @param string $action
     * @param array $params
     * @return array
     */
    public static function composeUrl($module, $action, $params = [])
    {
        return ['/' . static::getModuleId($module) . '/' . $action] + $params;
    }
}
