<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

/**
 * This is the model class for table "progress_report".
 *
 * @property int $id
 * @property int $user_id
 * @property string $report_date Month and year only
 * @property string $project_id Project ID from API
 * @property string $project_name Project name from API
 * @property string|null $project_data JSON data of project info from API
 * @property string|null $milestone_id Milestone ID from API
 * @property string|null $milestone_name Milestone name from API
 * @property string|null $deliverables_data JSON data of deliverables from API
 * @property string $status Completed, On-going, Delayed
 * @property int $has_extension Yes=1, No=0
 * @property string|null $extension_date Date proposed for extension
 * @property string|null $extension_justification Justification of extension
 * @property string|null $documents JSON array of uploaded document paths
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 */
class ProgressReport extends \yii\db\ActiveRecord
{
    const STATUS_COMPLETED = 'Completed';
    const STATUS_ONGOING = 'On-going';
    const STATUS_DELAYED = 'Delayed';

    const EXTENSION_PENDING = 'pending';
    const EXTENSION_APPROVED = 'approved';
    const EXTENSION_REJECTED = 'rejected';

    public $uploadedFiles;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'progress_report';
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
            [['user_id', 'report_date', 'project_id', 'project_name', 'status'], 'required'],
            [['user_id', 'has_extension', 'extension_processed_by', 'extension_processed_at'], 'integer'],
            [['report_date', 'extension_date', 'extension_approved_date'], 'safe'],
            [['project_data', 'deliverables_data', 'extension_justification', 'extension_rejection_reason', 'documents'], 'string'],
            [['project_id', 'milestone_id'], 'string', 'max' => 50],
            [['project_name', 'milestone_name'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 20],
            [['extension_status'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => [self::STATUS_COMPLETED, self::STATUS_ONGOING, self::STATUS_DELAYED]],
            [['extension_status'], 'in', 'range' => [self::EXTENSION_PENDING, self::EXTENSION_APPROVED, self::EXTENSION_REJECTED]],
            [['has_extension'], 'boolean'],
            [['extension_date', 'extension_justification'], 'required', 'when' => function($model) {
                return $model->has_extension == 1;
            }, 'whenClient' => "function (attribute, value) {
                return $('#progressreport-has_extension').val() == '1';
            }"],
            [['uploadedFiles'], 'file', 'maxFiles' => 10, 'skipOnEmpty' => true],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['extension_processed_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['extension_processed_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'report_date' => 'Report Date',
            'project_id' => 'Project',
            'project_name' => 'Project Name',
            'project_data' => 'Project Data',
            'milestone_id' => 'Milestone',
            'milestone_name' => 'Milestone Name',
            'deliverables_data' => 'Deliverables',
            'status' => 'Status',
            'has_extension' => 'Extension Required',
            'extension_date' => 'Proposed Extension Date',
            'extension_justification' => 'Justification for Extension',            'extension_status' => 'Extension Status',
            'extension_approved_date' => 'Approved Extension Date',
            'extension_rejection_reason' => 'Rejection Reason',
            'extension_processed_by' => 'Processed By',
            'extension_processed_at' => 'Processed At',            'documents' => 'Attached Documents',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'uploadedFiles' => 'Attach Documents',
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
     * Get project data as array
     */
    public function getProjectDataArray()
    {
        return $this->project_data ? Json::decode($this->project_data) : [];
    }

    /**
     * Get deliverables data as array
     */
    public function getDeliverablesArray()
    {
        return $this->deliverables_data ? Json::decode($this->deliverables_data) : [];
    }

    /**
     * Get documents as array
     */
    public function getDocumentsArray()
    {
        return $this->documents ? Json::decode($this->documents) : [];
    }

    /**
     * Set project data from array
     */
    public function setProjectDataArray($data)
    {
        $this->project_data = Json::encode($data);
    }

    /**
     * Set deliverables data from array
     */
    public function setDeliverablesArray($data)
    {
        $this->deliverables_data = Json::encode($data);
    }

    /**
     * Set documents from array
     */
    public function setDocumentsArray($data)
    {
        $this->documents = Json::encode($data);
    }

    /**
     * Get status options
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_ONGOING => 'On-going',
            self::STATUS_DELAYED => 'Delayed',
        ];
    }

    /**
     * Get status color
     */
    public function getStatusColor()
    {
        switch ($this->status) {
            case self::STATUS_COMPLETED:
                return '#4caf50';
            case self::STATUS_ONGOING:
                return '#2196f3';
            case self::STATUS_DELAYED:
                return '#f44336';
            default:
                return '#9e9e9e';
        }
    }

    /**
     * Get extension status options
     */
    public static function getExtensionStatusOptions()
    {
        return [
            self::EXTENSION_PENDING => 'Pending',
            self::EXTENSION_APPROVED => 'Approved',
            self::EXTENSION_REJECTED => 'Rejected',
        ];
    }

    /**
     * Get extension status badge
     */
    public function getExtensionStatusBadge()
    {
        switch ($this->extension_status) {
            case self::EXTENSION_APPROVED:
                return '<span style="background: #4caf50; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">✓ Approved</span>';
            case self::EXTENSION_REJECTED:
                return '<span style="background: #f44336; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">✗ Rejected</span>';
            case self::EXTENSION_PENDING:
            default:
                return '<span style="background: #ff9800; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">⏳ Pending</span>';
        }
    }

    /**
     * Get processor relation
     */
    public function getProcessor()
    {
        return $this->hasOne(User::class, ['id' => 'extension_processed_by']);
    }
}
