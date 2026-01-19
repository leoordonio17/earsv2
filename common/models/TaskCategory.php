<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "task_category".
 *
 * @property int $id
 * @property int $task_type_id
 * @property string $name
 * @property string|null $description
 * @property string|null $color
 * @property int|null $is_active
 * @property int|null $sort_order
 * @property int $created_at
 * @property int $updated_at
 *
 * @property TaskType $taskType
 */
class TaskCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'task_category';
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
            [['task_type_id', 'name'], 'required'],
            [['task_type_id', 'is_active', 'sort_order'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 100],
            [['color'], 'string', 'max' => 7],
            [['color'], 'match', 'pattern' => '/^#[0-9A-Fa-f]{6}$/', 'message' => 'Color must be a valid hex color code (e.g., #B8926A)'],
            [['sort_order'], 'default', 'value' => 0],
            [['is_active'], 'default', 'value' => 1],
            [['color'], 'default', 'value' => '#B8926A'],
            [['task_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaskType::class, 'targetAttribute' => ['task_type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_type_id' => 'Task Type',
            'name' => 'Category Name',
            'description' => 'Description',
            'color' => 'Color',
            'is_active' => 'Active',
            'sort_order' => 'Sort Order',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[TaskType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaskType()
    {
        return $this->hasOne(TaskType::class, ['id' => 'task_type_id']);
    }

    /**
     * Get active categories by task type
     */
    public static function getActiveByTaskType($taskTypeId)
    {
        return self::find()
            ->where(['task_type_id' => $taskTypeId, 'is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->all();
    }

    /**
     * Get categories for dropdown by task type
     */
    public static function getDropdownListByTaskType($taskTypeId)
    {
        return self::find()
            ->select(['name', 'id'])
            ->where(['task_type_id' => $taskTypeId, 'is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }
}
