<?php
namespace Panwenbin\FillModel;

class FillModel
{
    /**
     * fillOne fills model with data
     *
     * @param string $className
     * @param array $data
     * @return object
     */
    public static function fillOne(string $className, array $data): object
    {
        $model = new $className();
        $reflector = new \ReflectionClass($className);
        $properties = $reflector->getProperties();
        foreach ($properties as $property) {
            $property_name = $property->getName();
            if (!array_key_exists($property_name, $data)) {
                continue;
            }
            $property->setAccessible(true);
            $property->setValue($model, $data[$property_name]);
        }

        return $model;
    }

    /**
     * fillMany fills models with data
     *
     * @param string $className
     * @param array $data
     * @return array
    */
    public static function fillMany(string $className, array $data): array
    {
        $models = [];
        foreach ($data as $item) {
            $models[] = self::fillOne($className, $item);
        }
        return $models;
    }

    /**
     * fillManyHasOne fills one-to-one relation
     *
     * @param array $models
     * @param string $className
     * @param array $data
     * @param string $asAttribute
     * @param string $foreignKey
     * @param string $localKey
     * @return void
    */
    public static function fillManyHasOne(array $models, string $className, array $data, string $asAttribute, string $foreignKey, string $localKey = 'id')
    {
        $indexedModels = [];
        foreach ($models as &$model) {
            $model->$asAttribute = null;
            $indexedModels[$model->$localKey] = $model;
        }

        $relationModels = self::fillMany($className, $data);
        foreach ($relationModels as $relationModel) {
            if (isset($indexedModels[$relationModel->$foreignKey])) {
                $indexedModels[$relationModel->$foreignKey]->$asAttribute = $relationModel;
            }
        }
    }

    /**
     * fillManyHasMany fills one-to-many relation
     *
     * @param array $models
     * @param string $className
     * @param array $data
     * @param string $asAttribute
     * @param string $foreignKey
     * @param string $localKey
     * @return void
    */
    public static function fillManyHasMany(array $models, string $className, array $data, string $asAttribute, string $foreignKey, string $localKey = 'id')
    {
        $indexedModels = [];
        foreach ($models as &$model) {
            $model->$asAttribute = [];
            $indexedModels[$model->$localKey] = $model;
        }

        $relationModels = self::fillMany($className, $data);
        foreach ($relationModels as $relationModel) {
            if (isset($indexedModels[$relationModel->$foreignKey])) {
                $indexedModels[$relationModel->$foreignKey]->$asAttribute[] = $relationModel;
            }
        }
    }
}
