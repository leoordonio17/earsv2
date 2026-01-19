<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "task_type".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $color
 * @property int|null $is_active
 * @property int|null $sort_order
 * @property int $created_at
 * @property int $updated_at
 *
 * @property TaskCategory[] $taskCategories
 */
class TaskType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'task_type';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['is_active', 'sort_order'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['color'], 'string', 'max' => 7],
            [['color'], 'match', 'pattern' => '/^#[0-9A-Fa-f]{6}$/', 'message' => 'Color must be a valid hex color code (e.g., #967259)'],
            [['name'], 'unique'],
            [['sort_order'], 'default', 'value' => 0],
            [['is_active'], 'default', 'value' => 1],
            [['color'], 'default', 'value' => '#967259'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Task Type Name',
            'description' => 'Description',
            'color' => 'Color',
            'is_active' => 'Active',
            'sort_order' => 'Sort Order',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[TaskCategories]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaskCategories()
    {
        return $this->hasMany(TaskCategory::class, ['task_type_id' => 'id']);
    }

    /**
     * Get active task types ordered by sort_order
     */
    public static function getActiveTaskTypes()
    {
        return self::find()
            ->where(['is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->all();
    }

    /**
     * Get task types for dropdown
     */
    public static function getDropdownList()
    {
        return self::find()
            ->select(['name', 'id'])
            ->where(['is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }
}
