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
     * Fetch projects from API
     * @param bool $excludeAssigned Whether to exclude already assigned projects
     */
    public static function fetchProjectsFromAPI($excludeAssigned = false)
    {
        $apiKey = '2adad145b144f6d20e0904ab4bd40164cb7f49477e7867cb79b02e5e989a27aa';
        
        // Determine the correct API URL based on environment
        $hostInfo = Yii::$app->request->hostInfo;
        if (strpos($hostInfo, 'localhost') !== false) {
            // Local development - use pmisv2 backend API
            $url = 'http://localhost/pmisv2/backend/web/api/projects?api_key=' . $apiKey;
        } else {
            // Production - use projects.pids.gov.ph
            $url = 'https://projects.pids.gov.ph/api/projects?api_key=' . $apiKey;
        }

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // Log for debugging
            if ($curlError) {
                Yii::error('CURL Error fetching projects: ' . $curlError);
            }
            
            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Check if response has expected structure
                    if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
                        $projects = [];
                        foreach ($data['data'] as $project) {
                            // PMIS API uses 'project_title' not 'name'
                            if (isset($project['id']) && isset($project['project_title'])) {
                                $projects[$project['id']] = $project['project_title'];
                            } else if (isset($project['id']) && isset($project['name'])) {
                                $projects[$project['id']] = $project['name'];
                            }
                        }
                        if (!empty($projects)) {
                            // Exclude assigned projects if requested
                            if ($excludeAssigned) {
                                $assignedIds = self::getAssignedProjectIds();
                                foreach ($assignedIds as $assignedId) {
                                    unset($projects[$assignedId]);
                                }
                            }
                            // Sort projects alphabetically by title
                            asort($projects);
                            return $projects;
                        }
                    } else if (is_array($data)) {
                        // Try alternative structure - array of projects directly
                        $projects = [];
                        foreach ($data as $project) {
                            if (isset($project['id']) && isset($project['project_title'])) {
                                $projects[$project['id']] = $project['project_title'];
                            } else if (isset($project['id']) && isset($project['name'])) {
                                $projects[$project['id']] = $project['name'];
                            } else if (isset($project['project_id']) && isset($project['project_name'])) {
                                $projects[$project['project_id']] = $project['project_name'];
                            }
                        }
                        if (!empty($projects)) {
                            // Exclude assigned projects if requested
                            if ($excludeAssigned) {
                                $assignedIds = self::getAssignedProjectIds();
                                foreach ($assignedIds as $assignedId) {
                                    unset($projects[$assignedId]);
                                }
                            }
                            // Sort projects alphabetically by title
                            asort($projects);
                            return $projects;
                        }
                    }
                } else {
                    Yii::error('JSON decode error: ' . json_last_error_msg() . ' | Response: ' . substr($response, 0, 500));
                }
            } else {
                Yii::error('API returned HTTP code: ' . $httpCode . ' | URL: ' . $url . ' | Error: ' . $curlError);
            }
            
            // Return empty array if API fails
            return [];
        } catch (\Exception $e) {
            Yii::error('Failed to fetch projects from API: ' . $e->getMessage());
            return [];
        }
    }
}
