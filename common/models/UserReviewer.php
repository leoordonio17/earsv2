<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "user_reviewers".
 *
 * @property int $id
 * @property int $user_id
 * @property int $reviewer_id
 * @property string|null $reviewer_designation
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 * @property User $reviewer
 */
class UserReviewer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_reviewers';
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
            [['user_id', 'reviewer_id'], 'required'],
            [['user_id', 'reviewer_id'], 'integer'],
            [['reviewer_designation'], 'string', 'max' => 255],
            [['user_id', 'reviewer_id'], 'unique', 'targetAttribute' => ['user_id', 'reviewer_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['reviewer_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['reviewer_id' => 'id']],
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
            'reviewer_id' => 'Reviewer ID',
            'reviewer_designation' => 'Reviewer Designation',
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
     * Gets query for [[Reviewer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReviewer()
    {
        return $this->hasOne(User::class, ['id' => 'reviewer_id']);
    }
}
