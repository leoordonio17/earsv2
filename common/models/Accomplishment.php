<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "accomplishment".
 *
 * @property int $id
 * @property int $workplan_id
 * @property string $start_date
 * @property string $end_date
 * @property string $accomplished_task
 * @property int $status_id
 * @property string|null $mode_of_delivery
 * @property bool $is_final_task
 * @property int|null $milestone_id
 * @property string|null $target_deadline
 * @property string|null $actual_submission_date
 * @property bool|null $within_target
 * @property string|null $reason_for_delay
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Workplan $workplan
 * @property Status $status
 * @property Milestone $milestone
 */
class Accomplishment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accomplishment';
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
            [['workplan_id', 'start_date', 'end_date', 'accomplished_task', 'status_id'], 'required'],
            [['workplan_id', 'status_id', 'milestone_id'], 'integer'],
            [['start_date', 'end_date', 'target_deadline', 'actual_submission_date'], 'safe'],
            [['accomplished_task', 'reason_for_delay'], 'string'],
            [['mode_of_delivery'], 'string', 'max' => 20],
            [['mode_of_delivery'], 'in', 'range' => ['Onsite', 'WFH', 'Hybrid']],
            [['is_final_task', 'within_target'], 'boolean'],
            // Conditional validation for final task fields
            [['milestone_id', 'target_deadline', 'actual_submission_date', 'within_target'], 'required', 'when' => function($model) {
                return $model->is_final_task == true;
            }, 'whenClient' => "function (attribute, value) {
                return $('#accomplishment-is_final_task').is(':checked');
            }"],
            [['reason_for_delay'], 'required', 'when' => function($model) {
                return $model->is_final_task == true && $model->within_target == false;
            }, 'whenClient' => "function (attribute, value) {
                return $('#accomplishment-is_final_task').is(':checked') && $('#accomplishment-within_target-0').is(':checked');
            }"],
            [['end_date'], 'compare', 'compareAttribute' => 'start_date', 'operator' => '>=', 'message' => 'End date must not be earlier than start date.'],
            [['workplan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Workplan::class, 'targetAttribute' => ['workplan_id' => 'id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['status_id' => 'id']],
            [['milestone_id'], 'exist', 'skipOnError' => true, 'targetClass' => Milestone::class, 'targetAttribute' => ['milestone_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'workplan_id' => 'Workplan',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'accomplished_task' => 'Accomplished Task/Activity',
            'status_id' => 'Status',
            'mode_of_delivery' => 'Mode of Delivery',
            'is_final_task' => 'Final Task for Deliverable/Milestone?',
            'milestone_id' => 'Milestone',
            'target_deadline' => 'Target Deadline for Milestone',
            'actual_submission_date' => 'Actual Date of Submission',
            'within_target' => 'Within Target?',
            'reason_for_delay' => 'Reason for Delay',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Workplan]].
     */
    public function getWorkplan()
    {
        return $this->hasOne(Workplan::class, ['id' => 'workplan_id']);
    }

    /**
     * Gets query for [[Status]].
     */
    public function getStatus()
    {
        return $this->hasOne(Status::class, ['id' => 'status_id']);
    }

    /**
     * Gets query for [[Milestone]].
     */
    public function getMilestone()
    {
        return $this->hasOne(Milestone::class, ['id' => 'milestone_id']);
    }

    /**
     * Get user's workplans for dropdown
     */
    public static function getUserWorkplans($userId)
    {
        $workplans = Workplan::find()
            ->where(['user_id' => $userId])
            ->andWhere(['or', ['is_template' => 0], ['is_template' => null]])
            ->orderBy(['start_date' => SORT_DESC])
            ->all();
        
        $list = [];
        foreach ($workplans as $workplan) {
            $dateRange = Yii::$app->formatter->asDate($workplan->start_date, 'php:M d') . ' - ' . Yii::$app->formatter->asDate($workplan->end_date, 'php:M d, Y');
            $list[$workplan->id] = $workplan->project_name . ' (' . $dateRange . ')';
        }
        
        return $list;
    }
}
