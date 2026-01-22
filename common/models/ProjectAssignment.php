<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "project_assignment".
 *
 * @property int $id
 * @property string $project_id
 * @property string $project_name
 * @property int $user_id
 * @property int|null $is_active
 * @property int|null $sort_order
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 */
class ProjectAssignment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project_assignment';
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
            [['project_id', 'project_name', 'user_id'], 'required'],
            [['user_id'], 'integer'],
            [['project_id'], 'string', 'max' => 50],
            [['project_name'], 'string', 'max' => 255],
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
            'project_id' => 'Project ID',
            'project_name' => 'Project',
            'user_id' => 'Researcher',
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
     * Get active project assignments
     */
    public static function getActiveAssignments()
    {
        return self::find()
            ->with('user')
            ->orderBy(['project_name' => SORT_ASC])
            ->all();
    }

    /**
     * Get researchers (personnel) for dropdown
     */
    public static function getResearcherDropdownList()
    {
        return User::find()
            ->select(['full_name', 'id'])
            ->where(['role' => User::ROLE_PERSONNEL, 'status' => User::STATUS_ACTIVE])
            ->orderBy(['full_name' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }

    /**
     * Get list of project IDs that already have assignments
     */
    public static function getAssignedProjectIds()
    {
        return self::find()
            ->select('project_id')
            ->distinct()
            ->column();
    }

    /**
     * Fetch projects from PMIS database directly
     * @param bool $excludeAssigned Whether to exclude already assigned projects
     */
    public static function fetchProjectsFromAPI($excludeAssigned = false)
    {
        try {
            // Query directly from pmis database
            $query = Yii::$app->dbPmis->createCommand(
                'SELECT id, project_title FROM projects WHERE status = 10 ORDER BY project_title ASC'
            );
            
            $results = $query->queryAll();
            
            if (!empty($results)) {
                $projects = [];
                foreach ($results as $project) {
                    if (isset($project['id']) && isset($project['project_title'])) {
                        $projects[$project['id']] = $project['project_title'];
                    }
                }
                
                // Exclude assigned projects if requested
                if ($excludeAssigned && !empty($projects)) {
                    $assignedIds = self::getAssignedProjectIds();
                    foreach ($assignedIds as $assignedId) {
                        unset($projects[$assignedId]);
                    }
                }
                
                return $projects;
            }
            
            // Return empty array if no results
            return [];
        } catch (\Exception $e) {
            Yii::error('Failed to fetch projects from database: ' . $e->getMessage());
            return [];
        }
    }
}
