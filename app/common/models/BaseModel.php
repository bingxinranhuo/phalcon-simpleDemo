<?php

namespace app\common\models;
/**
 * Copyright (C) qiaodata.com 2018 All rights reserved
 * @author luojianglai@qiaodata.com
 * @date 2018/5/18 18:35
 */
class BaseModel extends \Phalcon\Mvc\Model
{
    /**
     * 主键
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 添加单个
     * @author hanguorui <hanguorui@qiaodata.com>
     * @date 2018-04-24 10:37
     * @return int 数据主键ID
     */
    public function addOne($data)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        foreach ($data as $column => $value) {
            $this->$column = $value;
        }

        if ($this->create()) {
            return $this->id;
        } else {
            return false;
        }
    }

    /**
     * 批量添加数据
     * @author hanguorui <hanguorui@qiaodata.com>
     * @date 2018-04-24
     * @param array $dataList 数据列表
     * @return int | bool 返回最后ID，执行失败返回false。
     */
    public function addMany($dataList)
    {
        if (empty($dataList)) {
            return false;
        }

        //拼接SQL
        $columns = array_keys($dataList[0]);
        $colStr = "";
        foreach ($columns as $k => $v) {
            $colStr .= "`" . $v . "`,";
        }
        $colStr = trim($colStr, ",");
        $sql = "INSERT INTO " . $this->getSource() . ' (' . $colStr . ') VALUES ';
        foreach ($dataList as $row) {
            $sql .= '(';
            foreach ($columns as $col) {
                $sql .= "'{$row[$col]}',";
            }
            $sql = rtrim($sql, ',');
            $sql .= '),';
        }
        $sql = rtrim($sql, ',');
        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql);
        if ($result) {
            return $db->lastInsertId();
        } else {
            return false;
        }
    }

    /**
     * 添加单条数据，当重复时忽略。
     * INSERT IGNORE INSERT (COLUMN1, COLUMN2, COLUMN3, ...) VALUES (1, 2, 3, ...)
     * @author hanguorui <hanguorui@qiaodata.com>
     * @date 2018-04-24
     * @param array $data 要添加的数据
     * @return int | bool 受影响行数，如果失败返回false。
     */
    public function insertOneIgnoreDuplicate($data)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        //拼接SQL
        $columns = array_keys($data);
        $sql = 'INSERT IGNORE INTO ' . $this->getSource() . ' (' . implode(',', $columns) . ') VALUES (';
        foreach ($columns as $col) {
            $sql .= ':' . $col . ',';
        }
        $sql = rtrim($sql, ',');
        $sql .= ')';

        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql, $data);
        if ($result) {
            return $db->affectedRows();
        } else {
            return false;
        }
    }

    /**
     * 单条添加数据，重复时更新
     * @author hanguorui <hanguorui@qiaodata.com>
     * @date 2018-04-24
     * @param array $data 数据列表
     * @param array $upkey 更新字段
     * @return int | bool 受影响行数，如果失败返回false。
     */
    public function insertOneDuplicate($data, $upkey)
    {
        if (empty($data)) {
            return false;
        }

        //拼接sql
        $upstr = '';
        foreach ($upkey as $key) {
            $upstr .= $key . "=VALUES (" . $key . "),";
        }
        $upstr = substr($upstr, 0, -1);

        $columns = array_keys($data);
        $sql = "INSERT INTO " . $this->getSource() . ' (' . implode(',', $columns) . ') VALUES (';
        foreach ($data as $col) {
            $sql .= "'{$col}'" . ',';
        }
        $sql = rtrim($sql, ',');
        $sql .= ')';

        $sql .= " ON DUPLICATE KEY UPDATE " . $upstr;
        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql);
        if ($result) {
            return $db->affectedRows();
        } else {
            return false;
        }
    }

    /**
     * 批量添加数据，重复时忽略
     * @author hanguorui <hanguorui@qiaodata.com>
     * @date 2018-04-24
     * @param array $dataList 数据列表
     * @return int | bool 受影响行数，如果失败返回false。
     */
    public function insertManyIgnoreDuplicate($dataList)
    {
        if (empty($dataList)) {
            return false;
        }

        //拼接sql
        $columns = array_keys($dataList[0]);
        $sql = "INSERT IGNORE INTO " . $this->getSource() . ' (' . implode(',', $columns) . ') VALUES ';
        foreach ($dataList as $row) {
            $sql .= '(';
            foreach ($columns as $col) {
                $sql .= "'{$row[$col]}',";
            }
            $sql = rtrim($sql, ',');
            $sql .= '),';
        }
        $sql = rtrim($sql, ',');
        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql);
        if ($result) {
            return $db->affectedRows();
        } else {
            return false;
        }
    }

    /**
     * 批量添加数据，重复时更新
     * @author hanguorui <hanguorui@qiaodata.com>
     * @date 2018-04-24
     * @param array $dataList 数据列表
     * @param array $upkey 更新字段
     * @return int | bool 受影响行数，如果失败返回false。
     */
    public function insertManyDuplicate($dataList, $upkey)
    {
        if (empty($dataList)) {
            return false;
        }
        //拼接sql
        $upstr = '';
        foreach ($upkey as $key) {
            $upstr .= $key . " = VALUES (" . $key . "),";
        }
        $upstr = substr($upstr, 0, -1);
        $columns = array_keys($dataList[0]);
        $sql = "INSERT  INTO " . $this->getSource() . ' (' . implode(',', $columns) . ') VALUES ';
        foreach ($dataList as $row) {
            $sql .= '(';
            foreach ($columns as $col) {
                $sql .= "'{$row[$col]}',";
            }
            $sql = rtrim($sql, ',');
            $sql .= '),';
        }
        $sql = rtrim($sql, ',');

        $sql .= " ON DUPLICATE KEY UPDATE " . $upstr;
        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql);
        if ($result) {
            return $db->affectedRows();
        } else {
            return false;
        }
    }

    /**
     * 根据主键ID更新数据
     * @author hanguorui <hanguorui@qiaodata.com>
     * @date 2018-04-24
     * @param int $pk 主键ID
     * @param array $newData 新数据
     * @return bool
     */
    public function updateByPrimaryKey($pk, $newData)
    {
        $pk = intval($pk);
        if (0 >= $pk || empty($newData)) {
            return false;
        }

        //拼接sql
        $sql = "UPDATE " . $this->getSource() . ' SET ';
        foreach ($newData as $column => $value) {
            $sql .= '`' . $column . '`=' . "'{$value}',";
        }
        $sql = rtrim($sql, ',');
        $sql .= " WHERE {$this->primaryKey}={$pk}";
        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql);
        if ($result) {
            return $db->affectedRows();
        } else {
            return false;
        }
    }

    /**
     * 根据条件更新数据
     * @param string $codition 条件
     * @param array $newData 新数据
     * @return bool
     * @author 徐朝兵 <xuchaobing@qiaodata.com>
     * @date 2018-10-12
     */
    public function updateByConditionKey($codition, $newData)
    {
        $codition = trim($codition);
        if (empty($codition) || empty($newData)) {
            return false;
        }

        //拼接sql
        $sql = "UPDATE " . $this->getSource() . ' SET ';
        foreach ($newData as $column => $value) {
            $sql .= '`' . $column . '`=' . "'{$value}',";
        }
        $sql = rtrim($sql, ',');
        $sql .= " WHERE {$codition}";
        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql);
        if ($result) {
            return $db->affectedRows();
        } else {
            return false;
        }
    }

    /**
     * Notes:更新数值
     * User: liuguangyuan@qiaodata.com
     * Date: 2018/9/21
     * Time: 9:56
     * @param $pk
     * @param $newData
     * @return bool
     */
    public function updateNumber($pk, $newData)
    {
        $pk = intval($pk);
        if (0 >= $pk || empty($newData)) {
            return false;
        }

        //拼接sql
        $sql = "UPDATE " . $this->getSource() . ' SET ';
        foreach ($newData as $column => $value) {
            if (strpos($value, "+") || strpos($value, "-")) {
                $sql .= '`' . $column . '`=' . "{$value},";
            } else {
                $sql .= '`' . $column . '`=' . "'{$value}',";
            }
        }
        $sql = rtrim($sql, ',');
        $sql .= " WHERE {$this->primaryKey}={$pk}";
        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql);
        if ($result) {
            return $db->affectedRows();
        } else {
            return false;
        }
    }

    /**
     * 根据主键ID更新数据
     * @author hanguorui <hanguorui@qiaodata.com>
     * @date 2018-04-24
     * @param int $pk 主键ID
     * @param array $newData 新数据
     * @return bool
     */
    public function updateMultiRecords($conditions, $newData)
    {
        if (!$conditions) {
            return false;
        }
        //拼接sql
        $sql = "UPDATE " . $this->getSource() . ' SET ';
        foreach ($newData as $column => $value) {
            $sql .= '`' . $column . '` =' . "'{$value}',";
        }
        $sql = rtrim($sql, ',');
        $sql .= " WHERE $conditions";
        //执行sql
        $db = $this->getDi()->getShared('db');
        $result = $db->execute($sql);
        if ($result) {
            return $db->affectedRows();
        } else {
            return false;
        }
    }

    /**
     * 仅更新数据变更字段，而非所有字段更新
     * $this->mode->id = $id;
     * $this->mode->iupdate(['column' => 'value])
     * @param array|null $data
     * @param null $whiteList
     * @author luojianglai@qiaodata.com
     * @date 2018/6/5 10:11
     */
    public function iupdate(array $data = null, $whiteList = null)
    {
        if (count($data) > 0) {
            $attributes = $this->getModelsMetaData()->getAttributes($this);
            $this->skipAttributesOnUpdate(array_diff($attributes, array_keys($data)));
        }
        return parent::update($data, $whiteList);
    }

}
