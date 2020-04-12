<?php
/**
 * @link
 * @copyright
 * @license
 */

namespace common\components;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\web\Application;
use common\models\Data;
use common\helpers\Framework;
use common\interfaces\ActivityInterface;
use common\interfaces\InstallInterface;
use Throwable;
use DateTime;

/**
 * Description
 *
 * @author Maximm Chichkanov
 */
class AppManager extends Component implements BootstrapInterface
{
    /**
     * Dependency injection (DI) container presets.
     */
    public $diPresets = [];

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     * @return void
     */
    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            if (is_array($this->diPresets) && !empty($this->diPresets)) {
                foreach ($this->diPresets as $className => $config) {
                    Yii::$container->set($className, $config);
                }
            }
    
            if ($app->user->identity instanceof ActivityInterface) {
                $app->user->identity->setActivityAt(new DateTime());
            }

            $dataStorage = Framework::getComponent(DataStorage::class, $app);

            if ($dataStorage) {
                /** @var DataStorage $dataStorage */
                $interface = InstallInterface::class;
                foreach ($app->modules as $id => $module) {
                    $class = Framework::getClass($module);
                    $error = null;
                    if ($class && is_subclass_of($class, $interface)) {
                        if (!$this->install($class, $dataStorage, $error)) {
                            $app->session->setFlash('error', "Не удалось установить модуль $id.");
                            $app->session->addFlash('error', $error->getMessage());
                        }
                    }
                }
            }
        }
    }

    /**
     * Description.
     * @param array|string $class
     * @param DataStorage $dataStorage
     * @param Throwable $error
     * @return boolean
     */
    protected function install($class, DataStorage $dataStorage, &$error = null)
    {
        $id = str_replace('\\', '_', $class);
        $key = "appManager.install.$id";
        $installed = $dataStorage->get($key, false);

        if ($installed) {
            return true;
        }

        try {
            $result = $class::install();

            // @todo handle result

            $dataStorage->set($key, true, [
                'modifier' => Data::MODIFIER_HIDDEN
            ]);

            return true;
        } catch (Throwable $e) {
            $error = $e;
            return false;
        }
    }
}
