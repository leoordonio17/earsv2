<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "workplan".
 *
 * @property int $id
 * @property int $user_id
 * @property string $project_id
 * @property string $project_name
 * @property int $task_type_id
 * @property int $task_category_id
 * @property int $project_stage_id
 * @property string $start_date
 * @property string $end_date
 * @property string $workplan
 * @property int|null $is_template
 * @property string|null $template_name
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Accomplishment[] $accomplishments
 * @property ProjectStage $projectStage
 * @property TaskCategory $taskCategory
 * @property TaskType $taskType
 * @property User $user
 */
class Workplan extends \yii\db\ActiveRecord
{
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
    public static function tableName()
    {
        return 'workplan';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'project_id', 'project_name', 'task_type_id', 'task_category_id', 'project_stage_id', 'start_date', 'end_date', 'workplan'], 'required'],
            [['user_id', 'task_type_id', 'task_category_id', 'project_stage_id'], 'integer'],
            [['start_date', 'end_date'], 'safe'],
            [['workplan'], 'string'],
            [['project_id'], 'string', 'max' => 50],
            [['project_name'], 'string', 'max' => 255],
            [['end_date'], 'compare', 'compareAttribute' => 'start_date', 'operator' => '>=', 'message' => 'End date must not be earlier than start date.'],
            [['project_stage_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProjectStage::class, 'targetAttribute' => ['project_stage_id' => 'id']],
            [['task_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaskCategory::class, 'targetAttribute' => ['task_category_id' => 'id']],
            [['task_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => TaskType::class, 'targetAttribute' => ['task_type_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
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
            'project_id' => 'Project ID',
            'project_name' => 'Project',
            'task_type_id' => 'Task Type',
            'task_category_id' => 'Task Category',
            'project_stage_id' => 'Project Stage',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'workplan' => 'Workplan Description',
            'is_template' => 'Save as Template',
            'template_name' => 'Template Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Accomplishments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccomplishments()
    {
        return $this->hasMany(Accomplishment::class, ['workplan_id' => 'id']);
    }

    /**
     * Gets query for [[WorkplanGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkplanGroup()
    {
        return $this->hasOne(WorkplanGroup::class, ['id' => 'workplan_group_id']);
    }

    /**
     * Gets query for [[ProjectStage]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProjectStage()
    {
        return $this->hasOne(ProjectStage::class, ['id' => 'project_stage_id']);
    }

    /**
     * Gets query for [[TaskCategory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTaskCategory()
    {
        return $this->hasOne(TaskCategory::class, ['id' => 'task_category_id']);
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
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Get user's assigned projects
     */
    public static function getUserProjects($userId)
    {
        $assignments = ProjectAssignment::find()
            ->where(['user_id' => $userId])
            ->orderBy(['project_name' => SORT_ASC])
            ->all();
        
        // Add "Not Project Related" option at the beginning
        $projects = ['NOT_PROJECT_RELATED' => '📋 Not Project Related'];
        
        foreach ($assignments as $assignment) {
            $projects[$assignment->project_id] = $assignment->project_name;
        }
        
        return $projects;
    }



    /**
     * Get task categories based on task type
     */
    public static function getTaskCategoriesByType($taskTypeId)
    {
        return TaskCategory::find()
            ->select(['name', 'id'])
            ->where(['task_type_id' => $taskTypeId, 'is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }
}
