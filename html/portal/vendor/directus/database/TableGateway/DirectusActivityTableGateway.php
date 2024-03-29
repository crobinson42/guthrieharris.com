<?php

namespace Directus\Database\TableGateway;

use Directus\Database\Query\Builder;
use Directus\Database\TableSchema;
use Directus\Permissions\Acl;
use Directus\Util\DateUtils;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

class DirectusActivityTableGateway extends RelationalTableGateway
{
    // Populates directus_activity.type
    const TYPE_ENTRY = 'ENTRY';
    const TYPE_FILES = 'FILES';
    const TYPE_SETTINGS = 'SETTINGS';
    const TYPE_UI = 'UI';
    const TYPE_LOGIN = 'LOGIN';
    const TYPE_MESSAGE = 'MESSAGE';

    // Populates directus_activity.action
    const ACTION_ADD = 'ADD';
    const ACTION_UPDATE = 'UPDATE';
    const ACTION_DELETE = 'DELETE';
    const ACTION_LOGIN = 'LOGIN';

    public static $_tableName = 'directus_activity';

    public static function makeLogTypeFromTableName($table)
    {
        switch ($table) {
            // @todo these first two are assumptions. are they correct?
            case 'directus_ui':
                return self::TYPE_UI;
            case 'directus_settings':
                return self::TYPE_SETTINGS;
            case 'directus_files':
                return self::TYPE_FILES;
            default:
                return self::TYPE_ENTRY;
        }
    }

    /**
     * DirectusActivityTableGateway constructor.
     *
     * @param AdapterInterface $adapter
     * @param Acl $acl
     */
    public function __construct(AdapterInterface $adapter, $acl)
    {
        parent::__construct(self::$_tableName, $adapter, $acl);
    }

    public function fetchFeed($params = null)
    {
        $params['order'] = ['id' => 'DESC'];
        $params = $this->applyDefaultEntriesSelectParams($params);
        $builder = new Builder($this->getAdapter());
        $builder->from($this->getTable());

        // @TODO: Only select the fields not on the currently authenticated user group's read field blacklist
        $columns = ['id', 'identifier', 'action', 'table_name', 'row_id', 'user', 'datetime', 'type', 'data'];
        $builder->columns($columns);

        $tableSchema = TableSchema::getTableSchema($this->table);
        $hasActiveColumn = $tableSchema->hasStatusColumn();

        $builder = $this->applyParamsToTableEntriesSelect($params, $builder, $tableSchema, $hasActiveColumn);

        $select = $builder->buildSelect();
        $select
            ->where
            ->nest
            ->isNull('parent_id')
            ->OR
            ->equalTo('type', 'FILES')
            ->unnest;

        $rowset = $this->selectWith($select);
        $rowset = $rowset->toArray();

        $countTotalWhere = new Where;
        $countTotalWhere
            ->isNull('parent_id')
            ->OR
            ->equalTo('type', 'FILES');

        return $this->loadMetadata($this->parseRecord($rowset));
    }

    public function fetchRevisions($row_id, $table_name)
    {
        $columns = ['id', 'action', 'user', 'datetime'];

        $sql = new Sql($this->adapter);
        $select = $sql->select()
            ->from($this->table)
            ->columns($columns)
            ->order('id DESC');
        $select
            ->where
            ->equalTo('row_id', $row_id)
            ->AND
            ->equalTo('table_name', $table_name);

        $result = $this->selectWith($select);
        $result = $result->toArray();

        return $this->loadMetadata($this->parseRecord($result));
    }

    public function recordLogin($userid)
    {
        $logData = [
            'type' => self::TYPE_LOGIN,
            'table_name' => 'directus_users',
            'action' => self::ACTION_LOGIN,
            'user' => $userid,
            'datetime' => DateUtils::now(),
            'parent_id' => null,
            'logged_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''
        ];

        $insert = new Insert($this->getTable());
        $insert
            ->values($logData);

        $this->insertWith($insert);
    }

    public function recordMessage($data, $userId)
    {
        if (isset($data['response_to']) && $data['response_to'] > 0) {
            $action = 'REPLY';
        } else {
            $action = 'ADD';
        }

        $logData = [
            'type' => self::TYPE_MESSAGE,
            'table_name' => 'directus_messages',
            'action' => $action,
            'user' => $userId,
            'datetime' => DateUtils::now(),
            'parent_id' => null,
            'data' => json_encode($data),
            'delta' => '[]',
            'identifier' => $data['subject'],
            'row_id' => $data['id'],
            'logged_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''
        ];

        $insert = new Insert($this->getTable());

        $insert
            ->values($logData);

        $this->insertWith($insert);
    }
}
