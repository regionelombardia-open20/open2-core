<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\migrations
 * @category   CategoryName
 */
use yii\db\Migration;

/**
 * Class m210202_093332_alter_counters
 */
class m210216_093332_alter_models_classname extends Migration
{
    const TABLE = '{{%models_classname}}';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE, 'table', $this->string()->after('classname'));

        $this->update(self::TABLE, ['table' => 'news'], ['classname' => 'open20\amos\news\models\News']);
        $this->update(self::TABLE, ['table' => 'event'], ['classname' => 'open20\amos\events\models\Event']);
        $this->update(self::TABLE, ['table' => 'documenti'], ['classname' => 'open20\amos\documenti\models\Documenti']);
        $this->update(self::TABLE, ['table' => 'discussioni'], ['classname' => 'open20\amos\discussioni\models\DiscussioniTopic']);
        $this->update(self::TABLE, ['table' => 'een_partenership_proposal'], ['classname' => 'open20\amos\een\models\EenPartnershipProposal']);
        $this->update(self::TABLE, ['table' => 'partnership_profiles'], ['classname' => 'open20\amos\partnershipprofiles\models\PartnershipProfiles']);
        $this->update(self::TABLE, ['table' => 'showcase_project'], ['classname' => 'open20\amos\showcaseprojects\models\ShowcaseProject']);
        $this->update(self::TABLE, ['table' => 'initiative'], ['classname' => 'open20\amos\showcaseprojects\models\Initiative']);
        $this->update(self::TABLE, ['table' => 'sondaggi'], ['classname' => 'open20\amos\sondaggi\models\Sondaggi']);
        $this->update(self::TABLE, ['table' => 'result'], ['classname' => 'amos\results\models\Result']);
        $this->update(self::TABLE, ['table' => 'challenge_team'], ['classname' => 'amos\challenge\models\ChallengeTeam']);
        $this->update(self::TABLE, ['table' => 'community'], ['classname' => 'open20\amos\community\models\Community']);
        $this->update(self::TABLE, ['table' => 'user_profile'], ['classname' => 'open20\amos\admin\models\UserProfile']);
        $this->update(self::TABLE, ['table' => 'profilo'], ['classname' => 'open20\amos\organizzazioni\models\Profilo']);
        $this->update(self::TABLE, ['table' => 'organizations'], ['classname' => 'openinnovation\organizations\models\Organizations']);
        $this->update(self::TABLE, ['table' => 'user'], ['classname' => 'open20\amos\community\models\Community']);
        $this->update(self::TABLE, ['table' => 'community'], ['classname' => 'open20\amos\core\user\User']);
        $this->update(self::TABLE, ['table' => 'podcast_episode'], ['classname' => 'amos\podcast\models\PodcastEpisode']);
        $this->update(self::TABLE, ['table' => 'podcast'], ['classname' => 'amos\podcast\models\Podcast']);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE, 'table');

    }
}