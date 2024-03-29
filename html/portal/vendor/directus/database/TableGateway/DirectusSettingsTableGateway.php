<?php

namespace Directus\Database\TableGateway;

use Directus\Permissions\Acl;
use Directus\Permissions\Exception\UnauthorizedTableBigEditException;
use Directus\Util\ArrayUtils;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class DirectusSettingsTableGateway extends BaseTableGateway
{
    public static $_tableName = 'directus_settings';

    private $_defaults = [];

    public function __construct(AdapterInterface $adapter, Acl $acl)
    {
        parent::__construct(self::$_tableName, $adapter, $acl);

        $this->_defaults['global'] = [
            'cms_user_auto_sign_out' => 60,
            'project_name' => 'Directus',
            'project_url' => 'http://localhost/',
            'rows_per_page' => 200,
            'cms_thumbnail_url' => ''
        ];

        $this->_defaults['files'] = [
            'allowed_thumbnails' => '',
            'thumbnail_quality' => 100,
            'thumbnail_size' => 200,
            'file_naming' => 'file_id',
            'thumbnail_crop_enabled' => 1,
            'youtube_api_key' => ''
        ];
    }

    public function fetchAll($selectModifier = null)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()->from($this->table);
        $select->columns(['id', 'collection', 'name', 'value'])
            ->order('collection');
        // Fetch row
        $rowset = $this->selectWith($select);
        $rowset = $rowset->toArray();
        $result = [];
        foreach ($rowset as $row) {
            $collection = $row['collection'];
            if (!array_key_exists($collection, $result)) {
                $result[$collection] = [];
            }
            $result[$collection][$row['name']] = $row['value'];
        }
        $result = array_replace_recursive($this->_defaults, $result);
        return $result;
    }

    public function fetchCollection($collection, $requiredKeys = [])
    {
        $select = new Select($this->table);
        $select->where->equalTo('collection', $collection);
        $rowset = $this->selectWith($select)->toArray();
        $result = [];
        foreach ($rowset as $row) {
            $result[$row['name']] = $row['value'];
        }
        if (count(array_diff($requiredKeys, array_keys($result)))) {
            throw new \Exception('The following keys must be defined in the `' . $collection . '` settings collection: ' . implode(', ', $requiredKeys));
        }
        return $result;
    }

    public function fetchByCollectionAndName($collection, $name)
    {
        $select = new Select($this->table);
        $select->limit(1);
        $select
            ->where
            ->equalTo('collection', $collection)
            ->equalTo('name', $name);
        $rowset = $this->selectWith($select);
        $result = $rowset->current();
        if (false === $result) {
            throw new \Exception('Required `directus_setting` with collection `' . $collection . '` and name `' . $name . '` not found.');
        }
        return $result;
    }

    // Since ZF2 doesn't support “INSERT…ON DUPLICATE KEY UDPATE” we need some raw SQL
    public function setValues($collection, $data)
    {
        if ($collection !== 'files' && $collection !== 'global') {
            throw new \Exception('The settings collection ' . $collection . ' is not supported');
        }

        $canUserAdd = $this->acl->hasTablePrivilege($this->getTable(), 'add');
        $canUserEdit = $this->acl->hasTablePrivilege($this->getTable(), 'bigedit');
        if (!$canUserAdd || !$canUserEdit) {
            $aclErrorPrefix = $this->acl->getErrorMessagePrefix();
            throw new UnauthorizedTableBigEditException($aclErrorPrefix . 'Not enough permission to add/update on table `' . $this->getTable() . '` (BigEdit Permission Forbidden)');
        }

        $data = ArrayUtils::omit($data, 'id');
        foreach ($data as $key => $value) {
            $parameters[] = '(' .
                $this->adapter->platform->quoteValue($collection) . ',' .
                $this->adapter->platform->quoteValue($key) . ',' .
                $this->adapter->platform->quoteValue($value) .
                ')';
        }

        $sql = 'INSERT INTO directus_settings (`collection`, `name`, `value`) VALUES ' . implode(',', $parameters) . ' ' .
            'ON DUPLICATE KEY UPDATE `collection` = VALUES(collection), `name` = VALUES(name), `value` = VALUES(value)';

        $query = $this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);

    }

}
