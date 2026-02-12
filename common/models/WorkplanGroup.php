<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "workplan_group".
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $start_date
 * @property string $end_date
 * @property string|null $description
 * @property int|null $is_template
 * @property string|null $template_name
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 * @property Workplan[] $workplans
 */
class WorkplanGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'workplan_group';
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
            [['user_id', 'title', 'start_date', 'end_date'], 'required'],
            [['user_id', 'is_template'], 'integer'],
            [['start_date', 'end_date'], 'safe'],
            [['description'], 'string'],
            [['title', 'template_name'], 'string', 'max' => 255],
            [['end_date'], 'compare', 'compareAttribute' => 'start_date', 'operator' => '>=', 'message' => 'End date must not be earlier than start date.'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            ['is_template', 'boolean'],
            ['is_template', 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User',
            'title' => 'Workplan Title',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'description' => 'Description',
            'is_template' => 'Save as Template',
            'template_name' => 'Template Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Workplans]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkplans()
    {
        return $this->hasMany(Workplan::class, ['workplan_group_id' => 'id']);
    }
}
