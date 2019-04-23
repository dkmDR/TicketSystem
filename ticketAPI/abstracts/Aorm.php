<?php

namespace abstracts;

use lib\Preference;
use ReflectionClass;
use stdClass;
use lib\Config;
use Exception;
use ReflectionException;
use RuntimeException;
use lib\vendor\validator\ModelValidator;

/**
 * Object Relation Mapping
 *
 * PHP version >= 5.4
 *
 * LICENSE: This source file is subject to the MIT license
 * that is available through the world-wide-web at the following URI:
 * https://opensource.org/licenses/MIT.
 *
 * @category   Abstract
 * @package    abstracts
 * @author     Miguel Peralta <mcalderon0329@gmail.com>
 * @version    2.2
 * @license    https://opensource.org/licenses/MIT  MIT license
 * @since      File available since Release 2.1
 */
abstract class Aorm
{
    /**
     * @var Aorm|null current model class
     */
    private $class = null;

    /**
     * @var string current table
     */
    private $table = "";

    /**
     * @var string primary key by table
     */
    private $primaryKey = "";

    /**
     * @var string primary key type
     */
    private $primaryKeyType = "";

    /**
     * @var bool if primary key is auto increment
     */
    private $isAutoIncrement = false;

    /**
     * @var array columns model defined
     */
    private $columns = array();

    /**
     * @var bool to know if it's necessary to make the reset
     */
    private $singletonColumn = array();

    /**
     * @var string current sql
     */
    private $sql = "";

    /**
     * @var null
     */
    private $dbo = null;

    /**
     * @var null
     */
    private $resourceSql = null;

    /**
     * @var array one to many objects found
     */
    private $oneToManyObjects = array();

    /**
     * @var array many to one objects found
     */
    private $manyToOneObjects = array();

    /**
     * @var string one to many object key found
     */
    private $oneToManyKey = "";

    /**
     * @var string one to many reference key found
     */
    private $oneToManyReferenceKey = "";

    /**
     * @var string many to one object key found
     */
    private $manyToOneKey = "";

    /**
     * @var string many to one reference key found
     */
    private $manyToOneReferenceKey = "";

    /**
     * @var array to save or update multiple data
     */
    private $arrayListToHandle = array();

    protected function __construct(Aorm $class, stdClass $properties = null)
    {
        if (!Config::$_USE_DB) {
            return false;
        }

        $this->class = $class;
        $this->classAnnotation();
        $this->getDbo($properties);
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param array $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * @return array
     */
    public function getArrayListToHandle()
    {
        return $this->arrayListToHandle;
    }

    /**
     * @param array $arrayListToHandle
     */
    public function setArrayListToHandle($arrayListToHandle)
    {
        $this->arrayListToHandle = $arrayListToHandle;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->dbo->getLink();
    }

    /**
     * @param stdClass $object
     * @param boolean $areColumns if you going to get data define as 'true'
     */
    public function setObjectColumns(stdClass $object, $areColumns = false)
    {
        $columns = array();
        if (!empty($object)) {
            $this->singletonColumn = $this->columns;
            foreach ($object as $key => $val) {
                if (!$areColumns) {
                    $type = gettype($val);
                    array_push($columns, array("name" => $key, "type" => $type));
                    $this->setColumns($columns);
                    $setter = $this->gettingSetter($key);
                    $this->class->$setter($val);
                } else {
                    array_push($columns, array("name" => $val, "type" => "string"));
                    $this->setColumns($columns);
                }

            }

        }
    }

    /**
     * reset for class attributes
     */
    public function resetColumns()
    {
        if (count($this->singletonColumn))
            $this->setColumns($this->singletonColumn);
    }

    /**
     * @param $properties
     * @throws Exception
     * @return void
     */
    private function getDbo($properties)
    {

        $dbo = null;

        $providerName = ($properties != null && isset($properties->provider) && !empty($properties->provider)) ? $properties->provider : Config::$_DATABASE_;

        $provider = 'lib\database\\' . $providerName;

        if (!class_exists($provider)) {
            throw new RuntimeException('provider database not found');
        }

        $dbo = new $provider(FALSE);

        if ($properties != null) {
            $dbo->setProperties($properties);
        }

        //open connection
        $dbo->connect();

        //set dbo
        $this->dbo = $dbo;

    }

    /**
     * read class annotation
     */
    private function classAnnotation()
    {
        try {

            $rClass = new ReflectionClass($this->class);
            $docs = $rClass->getDocComment();
            $position = strpos($docs, "@Table");
            if ($position !== FALSE) {
                $entityInfo = "";
                $entityTable = "";
                for ($b = $position; $b < strlen($docs); $b++) {
                    /*Last Character*/
                    if (!isset($docs[$b]) || $docs[$b] == "*") {
                        break;
                    }
                    /***************/
                    $entityInfo .= $docs[$b];
                }

                /*break info*/
                $entityInfo = str_replace("@Table", "", $entityInfo);
                $entityInfo = str_replace("[", "", $entityInfo);
                $entityInfo = str_replace("]", "", $entityInfo);
                $splitEntity = explode(",", $entityInfo);

                for ($splitIndex = 0; $splitIndex < count($splitEntity); $splitIndex++) {
                    if (strpos($splitEntity[$splitIndex], "name") !== FALSE) {
                        $splitValue = explode("=", $splitEntity[$splitIndex]);
                        $trimVal = trim($splitValue[1]);
                        if (!empty($trimVal)) {
                            $entityTable = $trimVal;
                        }
                    }
                }

                if (empty($entityTable)) {
                    throw new RuntimeException("Entity table could not be found");
                }

                $this->table = $entityTable;

                $this->propertiesAnnotation($rClass);
            }
        } catch (ReflectionException $rexc) {
            throw new RuntimeException($rexc->getMessage());
        }
    }

    /**
     * read property annotation
     * @param ReflectionClass $rClass
     */
    private function propertiesAnnotation(ReflectionClass $rClass)
    {
        $properties = $rClass->getProperties();
        if (count($properties)) {
            for ($i = 0; $i < count($properties); $i++) {
                $docs = $properties[$i]->getDocComment();
                $propertyName = $properties[$i]->name;
                $propertyType = "string";
                $propertyAlias = "";
                $propertyValid = "";
                $propertyValidAlias = "";

                $position = strpos($docs, "@Column");

                if ($position !== FALSE) {
                    $propertyInfo = "";
                    for ($b = $position; $b < strlen($docs); $b++) {
                        /*Last Character*/
                        if ( !isset($docs[$b]) || $docs[$b] == "*") {
                            break;
                        }
                        /***************/
                        $propertyInfo .= $docs[$b];
                    }

                    /*break property info*/
                    $propertyInfo = str_replace("@Column", "", $propertyInfo);
                    $propertyInfo = str_replace("[", "", $propertyInfo);
                    $propertyInfo = str_replace("]", "", $propertyInfo);
                    $splitProperty = explode(",", $propertyInfo);

                    for ($splitIndex = 0; $splitIndex < count($splitProperty); $splitIndex++) {
                        if (strpos($splitProperty[$splitIndex], "name") !== FALSE) {
                            $splitValue = explode("=", $splitProperty[$splitIndex]);
                            $trimVal = trim($splitValue[1]);
                            if (!empty($trimVal))
                                $propertyName = $trimVal;
                        }
                        if (strpos($splitProperty[$splitIndex], "type") !== FALSE && empty($requestType)) {
                            $splitValue = explode("=", $splitProperty[$splitIndex]);
                            $trimVal = trim($splitValue[1]);
                            if (!empty($trimVal))
                                $propertyType = $trimVal;
                        }
                        if (strpos($splitProperty[$splitIndex], "alias") !== FALSE) {
                            $splitValue = explode("=", $splitProperty[$splitIndex]);
                            $trimVal = trim($splitValue[1]);
                            if (!empty($trimVal))
                                $propertyAlias = $trimVal;
                        }
                        if (strpos($splitProperty[$splitIndex], "valid") !== FALSE) {
                            $splitValue = explode("=", $splitProperty[$splitIndex]);
                            $trimVal = trim($splitValue[1]);
                            if (!empty($trimVal))
                                $propertyValid = $trimVal;
                        }
                        if (strpos($splitProperty[$splitIndex], "keyMessage") !== FALSE) {
                            $splitValue = explode("=", $splitProperty[$splitIndex]);
                            $trimVal = trim($splitValue[1]);
                            if (!empty($trimVal))
                                $propertyValidAlias = $trimVal;
                        }

                    }

                    array_push($this->columns, array("name" => $propertyName, "type" => $propertyType, "alias" => $propertyAlias, "valid" => $propertyValid, "keyMessage"=>$propertyValidAlias));
                }

                if (strpos($docs, "@PrimaryKey") !== FALSE) {
                    $this->primaryKey = $propertyName;
                    $this->primaryKeyType = $propertyType;
                }

                if (strpos($docs, "@AutoIncrement") !== FALSE) {
                    $this->isAutoIncrement = true;
                }

                $this->primaryKey = trim($this->primaryKey);
                if (empty($this->primaryKey)) {
                    throw new RuntimeException("primary key undefined");
                }

                if (Preference::$DO_RELATION) {
                    $this->oneToMany($properties[$i]->name, $docs);

                    $this->manyToOne($properties[$i]->name, $docs);
                }
            }

        } else {
            throw new RuntimeException("properties could not be found");
        }
    }

    /**
     * execute all queries
     * @param $sql
     * @return $this
     */
    public function query($sql)
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * @return Aorm
     */
    public function get()
    {
        $sql = "select " . $this->handleColumn() . " from " . $this->table;
        return $this->query($sql);
    }

    /**
     * @param $table
     * @param array $properties field => compare field
     * @param string $OPERATOR operator ('=','!=', '>', '<', etc...)(optional)
     * @return $this
     */
    public function inner($table, array $properties, $OPERATOR = '=')
    {

        $this->sql .= " INNER JOIN $table ON ";

        foreach ($properties as $key => $value) {
            $this->sql .= $key . " $OPERATOR " . $value;
        }

        return $this;

    }

    /**
     * @param $table
     * @param array $properties field => compare field
     * @param string $OPERATOR operator ('=','!=', '>', '<', etc...)(optional)
     * @return $this
     */
    public function left($table, array $properties, $OPERATOR = '=')
    {

        $this->sql .= " LEFT JOIN $table ON ";

        foreach ($properties as $key => $value) {
            $this->sql .= $key . " $OPERATOR " . $value;
        }

        return $this;

    }

    /**
     * @param $table
     * @param array $properties field => compare field
     * @param string $OPERATOR operator ('=','!=', '>', '<', etc...)(optional)
     * @return $this
     */
    public function right($table, array $properties, $OPERATOR = '=')
    {

        $this->sql .= " RIGHT JOIN $table ON ";

        foreach ($properties as $key => $value) {
            $this->sql .= $key . " $OPERATOR " . $value;
        }

        return $this;

    }

    /**
     * @param $column
     * @param $operator
     * @param $value
     * @param string $linked
     * @return Aorm
     */
    public function condition($column, $operator, $value, $linked = '')
    {
        if (strpos($this->sql, "where") === FALSE) {
            $this->sql .= " where ";
        }
        $this->sql .= $column . ' ' . $operator . ' ' . $this->compareColumnResult($column, $value) . ' ' . $linked . ' ';
        return $this;
    }

    /**
     * get all data from table
     * @param boolean $cascade
     * @param string $type could be ( object or array )
     * @return array|null
     */
    public function getAll($cascade = true, $type = "object")
    {
        $oneToManyObjects = $this->oneToManyObjects;
        $manyToOneObjects = $this->manyToOneObjects;
        $sql = "select " . $this->handleColumn() . " from " . $this->table;
        $result = null;
        switch ($type) {
            case "object":
                $result = $this->query($sql)->getObjectList();
                if (count($result)) {
                    if (count($oneToManyObjects) && $cascade) {
                        foreach ($result as $index => $val) {
                            $result[$index]->oneToManyDetail = array();
                            $oneToManyKey = $this->oneToManyReferenceKey;
                            foreach ($oneToManyObjects as $inx => $value) {
                                $resource = $value->findBy($val->$oneToManyKey, $this->oneToManyKey);
                                $result[$index]->oneToManyDetail = array_merge($resource, $result[$index]->oneToManyDetail);
                            }
                        }
                    }

                    if (count($manyToOneObjects) && $cascade) {
                        foreach ($result as $index => $val) {
                            $result[$index]->manyToOneDetail = array();
                            $manyToOneKey = $this->manyToOneReferenceKey;
                            if (isset($val->$manyToOneKey) && !empty($val->$manyToOneKey))
                                foreach ($manyToOneObjects as $inx => $value) {
                                    $parentKey = $this->manyToOneKey;
                                    $setterMethod = $this->gettingSetter($parentKey, $value);
                                    $value->$setterMethod($val->$manyToOneKey);
                                    $getterMethod = $this->gettingGetter($parentKey, $value);
                                    $resource = $value->findBy($value->$getterMethod(), $parentKey);
                                    $result[$index]->manyToOneDetail = array_merge($resource, $result[$index]->manyToOneDetail);
                                }
                        }
                    }
                }
                break;
            case "array":
                $result = $this->query($sql)->getArrayList();
                if (count($result)) {
                    if (count($oneToManyObjects) && $cascade) {
                        foreach ($result as $index => $val) {
                            $result[$index]["oneToManyDetail"] = array();
                            $oneToManyKey = $this->oneToManyReferenceKey;
                            foreach ($oneToManyObjects as $inx => $value) {
                                $resource = $value->findBy($val[$oneToManyKey], $this->oneToManyKey, "array");
                                $result[$index]["oneToManyDetail"] = array_merge($resource, $result[$index]["oneToManyDetail"]);
                            }
                        }
                    }

                    if (count($manyToOneObjects) && $cascade) {
                        foreach ($result as $index => $val) {
                            $result[$index]["manyToOneDetail"] = array();
                            $manyToOneKey = $this->manyToOneReferenceKey;
                            if (isset($val[$manyToOneKey]) && !empty($val[$manyToOneKey]))
                                foreach ($manyToOneObjects as $inx => $value) {
                                    $parentKey = $this->manyToOneKey;
                                    $setterMethod = $this->gettingSetter($parentKey, $value);
                                    $value->$setterMethod($val[$manyToOneKey]);
                                    $getterMethod = $this->gettingGetter($parentKey, $value);
                                    $resource = $value->findBy($value->$getterMethod(), $parentKey, "array");
                                    $result[$index]["manyToOneDetail"] = array_merge($resource, $result[$index]["manyToOneDetail"]);
                                }
                        }
                    }
                }
                break;
        }
        return $result;
    }

    /**
     * find record by primary value
     * @param $value
     * @param boolean $cascade
     * @param string $typeResult
     * @return array|null|stdClass
     */
    public function find($value, $cascade = true, $typeResult = "object")
    {
        $column = $this->primaryKey;
        $oneToManyObjects = $this->oneToManyObjects;
        $manyToOneObjects = $this->manyToOneObjects;
        $sql = "select " . $this->handleColumn() . " from " . $this->table . " where $column=" . $this->compareColumnResult($column, $value);
        $result = null;
        switch ($typeResult) {
            case "object":
                $result = $this->query($sql)->getObject();
                if (!empty($result)) {
                    if (count($oneToManyObjects) && $cascade) {
                        $result->oneToManyDetail = array();
                        $oneToManyKey = $this->oneToManyReferenceKey;
                        foreach ($oneToManyObjects as $inx => $value) {
                            $resource = $value->findBy($result->$oneToManyKey, $this->oneToManyKey);
                            $result->oneToManyDetail = array_merge($resource, $result->oneToManyDetail);
                        }
                    }

                    if (count($manyToOneObjects) && $cascade) {

                        $result->manyToOneDetail = array();
                        $manyToOneKey = $this->manyToOneReferenceKey;
                        if (isset($result->$manyToOneKey) && !empty($result->$manyToOneKey))
                            foreach ($manyToOneObjects as $inx => $value) {
                                $parentKey = $this->manyToOneKey;
                                $setterMethod = $this->gettingSetter($parentKey, $value);
                                $value->$setterMethod($result->$manyToOneKey);
                                $getterMethod = $this->gettingGetter($parentKey, $value);
                                $resource = $value->findBy($value->$getterMethod(), $parentKey);
                                $result->manyToOneDetail = array_merge($resource, $result->manyToOneDetail);
                            }

                    }
                }
                break;
            case "array":
                $result = $this->query($sql)->getArray();
                if (count($result)) {
                    if (count($oneToManyObjects) && $cascade) {
                        $result["oneToManyDetail"] = array();
                        $oneToManyKey = $this->oneToManyReferenceKey;
                        foreach ($oneToManyObjects as $inx => $value) {
                            $resource = $value->findBy($result[$oneToManyKey], $this->oneToManyKey);
                            $result["oneToManyDetail"] = array_merge($resource, $result["oneToManyDetail"]);
                        }
                    }

                    if (count($manyToOneObjects) && $cascade) {
                        $result["manyToOneDetail"] = array();
                        $manyToOneKey = $this->manyToOneReferenceKey;
                        if (isset($result[$manyToOneKey]) && !empty($result[$manyToOneKey]))
                            foreach ($manyToOneObjects as $inx => $value) {
                                $parentKey = $this->manyToOneKey;
                                $setterMethod = $this->gettingSetter($parentKey, $value);
                                $value->$setterMethod($result[$manyToOneKey]);
                                $getterMethod = $this->gettingGetter($parentKey, $value);
                                $resource = $value->findBy($value->$getterMethod(), $parentKey);
                                $result["manyToOneDetail"] = array_merge($resource, $result["manyToOneDetail"]);
                            }
                    }
                }
                break;
        }
        return $result;
    }

    /**
     * @param $value
     * @param $column
     * @param string $typeResult
     * @return array|null
     */
    public function findBy($value, $column, $typeResult = "object")
    {
        $sql = "select " . $this->handleColumn() . " from " . $this->table . " where $column=" . $this->compareColumnResult($column, $value);
        $result = null;
        switch ($typeResult) {
            case "object":
                $result = $this->query($sql)->getObjectList();
                break;
            case "array":
                $result = $this->query($sql)->getArrayList();
                break;
        }
        return $result;
    }

    /**
     * get data with all column
     * @param $value
     * @param $column
     * @param string $typeResult
     * @return array|null
     */
    private function findWithAllColumn($value, $column, $typeResult = "object")
    {
        $sql = "select * from " . $this->table . " where $column=" . $this->compareColumnResult($column, $value);
        $result = null;
        switch ($typeResult) {
            case "object":
                $result = $this->query($sql)->getInternalObjectList();
                break;
            case "array":
                $result = $this->query($sql)->getInternalArrayList();
                break;
        }
        return $result;
    }

    /**
     * @param boolean $cascade
     * @return int|array
     * @throws RuntimeException
     * @deprecated deprecated since version 2.3
     */
    public function save($cascade = false)
    {

        $object_insert = new stdClass();
        $columns = $this->columns;

        /*Instance Validator*/
            $mv = new ModelValidator($columns, $this->class);
        /***/

        for ($i = 0; $i < count($columns); $i++) {
            $object = (object)$columns[$i];
            $columnKey = $object->name;
            $columnName = $columnKey;
            $columnName = ucwords(str_replace("_", " ", $columnName));
            $columnName = str_replace(" ", "", $columnName);
            $getter = "get" . $columnName;
            if (!method_exists($this->class, $getter)) {
                throw new RuntimeException(" getter method not found " . $getter);
            }

            if ($this->primaryKey == $columnKey && $this->isAutoIncrement) {
                continue;
            }

            $value = $this->class->$getter();
            if (isset($value))
                $object_insert->$columnKey = $this->dbo->Escape($value);
        }

        /*Exec Validator*/
            $mv->validate();
        /***/

        $inserted = $this->dbo->insertObject($object_insert, $this->table, $this->primaryKey);
        /*reset columns*/
        $this->resetColumns();

        $oneToManyObjects = $this->oneToManyObjects;

        if (count($oneToManyObjects) && $cascade) {
            $detailInserted = array();
            $detail = null;
            foreach ($oneToManyObjects as $inx => $value) {

                $arrayListHandle = $value->getArrayListToHandle();
                if (count($arrayListHandle)) {
                    $oneToManyKeyToInsert = $this->oneToManyKey;
                    $detail = array();
                    foreach ($arrayListHandle as $indexKey => $valueKey) {
                        $valueKey->$oneToManyKeyToInsert = $inserted;
                        $value->setObjectColumns($valueKey);
                        $d = $value->save($cascade);
                        array_push($detail, $d);
                    }
                } else {
                    $setterParent = $this->gettingSetter($this->oneToManyKey, $value);
                    $value->$setterParent($inserted);
                    $detail = $value->save($cascade);
                }

                $tableName = trim($value->getTable());
                if (!isset($detailInserted[$tableName]) || empty($detailInserted[$tableName])) {
                    $detailInserted[$tableName] = array();
                }
                array_push($detailInserted[$tableName], $detail);
            }
            return array("head" => $inserted, "detail" => $detailInserted);
        }

        return $inserted;
    }

    /**
     * save multiple data
     * @param bool $cascade
     * @return array|null
     */
    public function saveAll($cascade = false)
    {
        $arrayListHandle = $this->getArrayListToHandle();
        if (count($arrayListHandle)) {
            $detail = array();
            foreach ($arrayListHandle as $indexKey => $valueKey) {
                $this->setObjectColumns($valueKey);
                $d = $this->save($cascade);
                array_push($detail, $d);
            }

            return $detail;
        }

        return null;
    }

    /**
     * @param bool $cascade
     * @return array|null
     */
    public function updateAll($cascade = false)
    {
        $arrayListHandle = $this->getArrayListToHandle();
        if (count($arrayListHandle)) {
            $detail = array();
            foreach ($arrayListHandle as $indexKey => $valueKey) {
                $this->setObjectColumns($valueKey);
                $d = $this->update('', $cascade);
                array_push($detail, $d);
            }

            return $detail;
        }

        return null;
    }

    /**
     * @param string $condition
     * @param boolean $cascade
     * @return int|array
     * @throws RuntimeException
     * @deprecated deprecated since version 2.3
     */
    public function update($condition = '', $cascade = false)
    {

        $primaryKeyList = array();
        $primaryKey = $this->primaryKey;

        if (empty($condition)) {
            $columnPrimaryName = $primaryKey;
            $columnPrimaryName = ucwords(str_replace("_", " ", $columnPrimaryName));
            $columnPrimaryName = str_replace(" ", "", $columnPrimaryName);
            $getter = "get" . $columnPrimaryName;
            if (!method_exists($this->class, $getter)) {
                throw new RuntimeException(" getter method not found " . $getter);
            }
            $value = $this->class->$getter();
            if (empty($value)) {
                throw new RuntimeException("Primary key value cannot be null @" . $this->primaryKey);
            }
            $conditionSentences = $primaryKey . ' = ' . $this->compareColumnResult($primaryKey, $value);
            array_push($primaryKeyList, $value);
        } else {
            $conditionSentences = $condition;
            $searchSql = "select " . $primaryKey . " from " . $this->table . " where " . $condition;
            $searchResource = $this->query($searchSql)->getObjectList();
            foreach ($searchResource as $ikey => $vkey) {
                array_push($primaryKeyList, $vkey->$primaryKey);
            }
        }

        $object_update = new stdClass();

        $columns = $this->columns;

        /*Instance Validator*/
            $mv = new ModelValidator($columns, $this->class);
        /***/

        for ($i = 0; $i < count($columns); $i++) {
            $object = (object)$columns[$i];
            $columnKey = $object->name;
            $columnName = $columnKey;
            $columnName = ucwords(str_replace("_", " ", $columnName));
            $columnName = str_replace(" ", "", $columnName);
            $getter = "get" . $columnName;

            if (!method_exists($this->class, $getter)) {
                throw new RuntimeException(" getter method not found " . $getter);
            }

            if ($this->primaryKey == $columnKey) {
                continue;
            }

            $value = $this->class->$getter();

            if (isset($value))
                $object_update->$columnKey = $this->dbo->Escape($value);
        }

        /*Exec Validator*/
            $mv->validate();
        /***/

        $updated = $this->dbo->updateObject($object_update, $this->table, $conditionSentences);
        /*reset columns*/
        $this->resetColumns();

        $oneToManyObjects = $this->oneToManyObjects;
        if (count($oneToManyObjects) && count($primaryKeyList) && $updated > 0 && $cascade) {
            $detailUpdated = array();
            foreach ($oneToManyObjects as $inx => $value) {

                for ($i = 0; $i < count($primaryKeyList); $i++) {
                    $parentKeyId = $primaryKeyList[$i];
                    $childrenResource = $value->findWithAllColumn($parentKeyId, $this->oneToManyKey);

                    if (count($childrenResource)) {
                        foreach ($childrenResource as $key => $keyValue) {
                            $childPrimaryKey = $value->getPrimaryKey();
                            $childColumnPrimaryName = $childPrimaryKey;
                            $childColumnPrimaryName = ucwords(str_replace("_", " ", $childColumnPrimaryName));
                            $childColumnPrimaryName = str_replace(" ", "", $childColumnPrimaryName);
                            $childGetter = "set" . $childColumnPrimaryName;
                            if (!method_exists($value, $childGetter)) {
                                throw new RuntimeException(" setter method not found " . $childGetter);
                            }
                            $value->$childGetter($keyValue->$childPrimaryKey);
                            $detail = $value->update('', $cascade);
                            $tableName = trim($value->getTable());
                            if (!isset($detailUpdated[$tableName]) || empty($detailUpdated[$tableName])) {
                                $detailUpdated[$tableName] = array();
                            }
                            array_push($detailUpdated[$tableName], $detail);
                        }
                    }
                }
            }
            return array("head" => $updated, "detail" => $detailUpdated);
        }

        return $updated;

    }

    /**
     * @param string $column
     * @param boolean $cascade
     * @return int|array
     * @throws RuntimeException
     */
    public function updateBy($column, $cascade = false)
    {
        $columnName = $column;
        $columnName = ucwords(str_replace("_", " ", $columnName));
        $columnName = str_replace(" ", "", $columnName);
        $getter = "get" . $columnName;
        if (!method_exists($this->class, $getter)) {
            throw new RuntimeException(" getter method not found " . $getter);
        }
        $value = $this->class->$getter();
        if (empty($value)) {
            throw new RuntimeException("Primary key value cannot be null @" . $this->primaryKey);
        }
        $condition = $column . ' = ' . $this->compareColumnResult($column, $value);
        return $this->update($condition, $cascade);
    }

    /**
     * @param string $condition
     * @param boolean $cascade
     * @throws RuntimeException
     * @return int|array
     */
    public function delete($condition = '', $cascade = false)
    {

        $sql = "DELETE FROM " . $this->table . " WHERE ";
        $primaryKeyList = array();
        $primaryKey = $this->primaryKey;

        if (empty($condition)) {
            $columnPrimaryName = $primaryKey;
            $columnPrimaryName = ucwords(str_replace("_", " ", $columnPrimaryName));
            $columnPrimaryName = str_replace(" ", "", $columnPrimaryName);
            $getter = "get" . $columnPrimaryName;
            if (!method_exists($this->class, $getter)) {
                throw new RuntimeException(" getter method not found " . $getter);
            }
            $value = $this->class->$getter();
            if (empty($value)) {
                throw new RuntimeException("Primary key value cannot be null @" . $this->primaryKey);
            }
            $addCondition = $primaryKey . ' = ' . $this->compareColumnResult($primaryKey, $value);
            array_push($primaryKeyList, $value);
        } else {
            $addCondition = $condition;
            $searchSql = "select " . $primaryKey . " from " . $this->table . " where " . $condition;
            $searchResource = $this->query($searchSql)->getObjectList();
            foreach ($searchResource as $ikey => $vkey) {
                array_push($primaryKeyList, $vkey->$primaryKey);
            }
        }

        $sql .= $addCondition;

        $resouce = $this->dbo->query($sql);

        $deleted = $this->dbo->rowAffect($resouce);

        $oneToManyObjects = $this->oneToManyObjects;
        if (count($oneToManyObjects) && count($primaryKeyList) && $deleted > 0 && $cascade) {
            $detailDelete = array();
            foreach ($oneToManyObjects as $inx => $value) {
                for ($i = 0; $i < count($primaryKeyList); $i++) {
                    $parentKeyId = $primaryKeyList[$i];
                    $childrenResource = $value->findBy($parentKeyId, $this->oneToManyKey);
                    if (count($childrenResource)) {
                        foreach ($childrenResource as $key => $keyValue) {
                            $childPrimaryKey = $value->getPrimaryKey();
                            $childColumnPrimaryName = $childPrimaryKey;
                            $childColumnPrimaryName = ucwords(str_replace("_", " ", $childColumnPrimaryName));
                            $childColumnPrimaryName = str_replace(" ", "", $childColumnPrimaryName);
                            $childGetter = "set" . $childColumnPrimaryName;
                            if (!method_exists($value, $childGetter)) {
                                throw new RuntimeException(" setter method not found " . $childGetter);
                            }
                            $value->$childGetter($keyValue->$childPrimaryKey);
                            $detail = $value->delete('', $cascade);
                            $tableName = trim($value->getTable());
                            if (!isset($detailDelete[$tableName]) || empty($detailDelete[$tableName])) {
                                $detailDelete[$tableName] = array();
                            }
                            array_push($detailDelete[$tableName], $detail);
                        }
                    }
                }
            }
            return array("head" => $deleted, "detail" => $detailDelete);
        }

        return $deleted;
    }

    /**
     * Delete by primary key value
     * @param mixed $value primary key value
     * @param boolean $cascade
     * @return int|array
     */
    public function destroy($value, $cascade = false)
    {
        return $this->delete($this->primaryKey . ' = ' . $value, $cascade);
    }

    /**
     * This method will be use for save or update a model, it'll depends if you define the primary key value
     * @param bool $cascade
     * @return array|int
     * @since 2.3
     */
    public function process( $cascade = false ) {

        if ( !empty($this->primaryKey) ) {
            $method = $this->gettingGetter($this->primaryKey);
            $primaryKeyValue = $this->class->$method();

            if ( empty($primaryKeyValue) ) {
                return $this->save($cascade);
            } else {
                return $this->update('', $cascade);
            }

        } else {
            throw new RuntimeException("@Primary Key is not defined");
        }
    }

    /**
     * begin transaction in database
     * @return void
     */
    public function begin()
    {
        $this->dbo->beginTransaction();
    }

    /**
     * commit transaction in database
     * @return void
     */
    public function commit()
    {
        $this->dbo->commitTransaction();
    }

    /**
     * rollback transaction in database
     * @param string $_EXCEPTION_MSG message on throw exception
     * @param bool $_THROW_EXCEPTION throw exception
     */
    public function rollback($_EXCEPTION_MSG = 'Rollback executing...', $_THROW_EXCEPTION = TRUE)
    {
        $this->dbo->rollbackTransaction($_EXCEPTION_MSG, $_THROW_EXCEPTION);
    }

    /**
     * group by group
     * @param string $fields
     * @return $this
     */
    public function groupBy($fields)
    {
        $this->sql .= " group by " . $fields;
        return $this;
    }

    /**
     * order by query
     * @param string $fields
     * @param string $sort type could be 'asc' or 'desc'
     * @return $this
     */
    public function orderBy($fields, $sort = 'asc')
    {
        $this->sql .= " order by " . $fields . " " . $sort;
        return $this;
    }

    /**
     * Returns the escaped string
     * @param mixed $string Required. The string to be escaped. Characters encoded are NUL (ASCII 0), \n, \r, \, ', ", and Control-Z.
     * @return mixed
     */
    public function escape($string)
    {
        return $this->dbo->Escape($string);
    }

    /**
     * execute set query, always you must call query method before this
     * @return $this
     */
    public function execute()
    {
        $this->resourceSql = $this->dbo->query($this->sql);
        return $this;
    }

    /**
     * return affected rows
     * @return int
     */
    public function getRowAffected()
    {
        return $this->dbo->rowAffect($this->resourceSql);
    }

    /**
     * Object list by resource result from the database
     * @return array
     */
    public function getObjectList()
    {
        /*reset columns*/
        $this->resetColumns();
        return $this->dbo->getObjectList($this->sql);
    }

    /**
     * Object list by resource result from the database without reset the fields
     * @return mixed
     */
    private function getInternalObjectList()
    {
        return $this->dbo->getObjectList($this->sql);
    }

    /**
     * array list by resource result from the database
     * @return array
     */
    public function getArrayList()
    {
        /*reset columns*/
        $this->resetColumns();
        return $this->dbo->getArrayList($this->sql);
    }

    /**
     * array list by resource result from the database without reset field
     * @return mixed
     */
    public function getInternalArrayList()
    {
        return $this->dbo->getArrayList($this->sql);
    }

    /**
     * type one result consult
     * @return stdClass database resource object
     */
    public function getObject()
    {
        /*reset columns*/
        $this->resetColumns();
        return $this->dbo->getRowObjectList($this->sql);
    }

    /**
     * type one result consult
     * @return array
     */
    public function getArray()
    {
        /*reset columns*/
        $this->resetColumns();
        return $this->dbo->getRowArrayList($this->sql);
    }

    /**
     * sql sentences
     * @return string
     */
    public function getSqlSentences()
    {
        return $this->sql;
    }

    /**
     * get column list separate with comma
     * @return string
     */
    private function handleColumn()
    {
        $columnList = "";
        $columns = $this->columns;
        if (count($columns)) {
            for ($i = 0; $i < count($columns); $i++) {
                $object = (object)$columns[$i];
                $name = (!empty($object->alias)) ? $object->name . " as " . $object->alias : $object->name;
                $columnList .= $name . ",";
            }
            $columnList = trim($columnList, ",");
        }

        return $columnList;
    }

    /**
     * get compare result to column by type
     * @param string $columnName
     * @param mixed $value
     * @return string
     */
    private function compareColumnResult($columnName, $value)
    {
        $compareResult = "";
        $columns = $this->columns;
        if (count($columns)) {
            $foundColumn = false;
            for ($i = 0; $i < count($columns); $i++) {
                $object = (object)$columns[$i];
                if ($object->name == $columnName) {
                    switch (strtolower(trim($object->type))) {
                        case "integer":
                            $compareResult = $value;
                            break;
                        case "numeric":
                            $compareResult = $value;
                            break;
                        case "double":
                            $compareResult = $value;
                            break;
                        case "boolean":
                            $compareResult = $value;
                            break;
                        case "bool":
                            $compareResult = $value;
                            break;
                        default:
                            $compareResult = "'" . $value . "'";
                            break;
                    }
                    $foundColumn = true;
                    break;
                }
            }

            if (!$foundColumn) {
                $compareResult = $value;
            }

        }
        return $compareResult;
    }

    /**
     * match from this entity to another
     * @param string $propertyName
     * @param string $docs
     */
    private function oneToMany($propertyName, $docs)
    {

        $position = strpos($docs, "@OneToMany");

        if ($position !== FALSE) {
            $entityName = "";
            //relation column
            $targetName = $this->primaryKey;
            $targetReferenceName = "";
            $propertyInfo = "";
            for ($b = $position; $b < strlen($docs); $b++) {
                /*Last Character*/
                if (empty($docs[$b]) || $docs[$b] == "*") {
                    break;
                }
                /***************/
                $propertyInfo .= $docs[$b];
            }

            /*break property info*/
            $propertyInfo = str_replace("@OneToMany", "", $propertyInfo);
            $propertyInfo = str_replace("[", "", $propertyInfo);
            $propertyInfo = str_replace("]", "", $propertyInfo);
            $splitProperty = explode(",", $propertyInfo);

            for ($splitIndex = 0; $splitIndex < count($splitProperty); $splitIndex++) {
                if (strpos($splitProperty[$splitIndex], "Entity") !== FALSE) {
                    $splitValue = explode("=", $splitProperty[$splitIndex]);
                    $trimVal = trim($splitValue[1]);
                    if (!empty($trimVal))
                        $entityName = $trimVal;
                }
                if (strpos($splitProperty[$splitIndex], "target") !== FALSE && empty($requestType)) {
                    $splitValue = explode("=", $splitProperty[$splitIndex]);
                    $trimVal = trim($splitValue[1]);
                    if (!empty($trimVal))
                        $targetName = $trimVal;
                }
                if (strpos($splitProperty[$splitIndex], "targetReference") !== FALSE && empty($requestType)) {
                    $splitValue = explode("=", $splitProperty[$splitIndex]);
                    $trimVal = trim($splitValue[1]);
                    if (!empty($trimVal))
                        $targetReferenceName = $trimVal;
                }
            }

            if (!empty($entityName)) {
                //do the magic
                $splitContent = explode("/", $entityName);
                if (count($splitContent)) {
                    $module = (!empty($splitContent[0])) ? $splitContent[0] : "";
                    $className = (!empty($splitContent[1])) ? $splitContent[1] : "";
                    $nsClass = $module . "\\" . "Models" . "\\" . $className . "Model";
                    $setterRelation = $this->gettingSetter($propertyName, $this->class);

                    if (method_exists($this->class, $setterRelation)) {
                        $properties = null;
                        //getter properties
                        $getterProperties = "getDbProperties";
                        if (method_exists($this->class, $getterProperties)) {
                            $properties = $this->class->$getterProperties();
                        }
                        //relation instance
                        $instance = new $nsClass($properties);

                        /*verify columns*/
                        $columnsToVerify = $this->columns;
                        $isThere = false;
                        for ($c = 0; $c < count($columnsToVerify); $c++) {
                            if ($columnsToVerify[$c]["name"] == $targetReferenceName) {
                                $isThere = true;
                                break;
                            }
                        }

                        if (!$isThere && property_exists($this->class, $targetReferenceName)) {
                            $isThere = true;
                        }

                        if ($isThere && property_exists($instance, $targetName)) {
                            //setter relation instance
                            $this->class->$setterRelation($instance);
                            array_push($this->oneToManyObjects, $instance);
                            $this->oneToManyKey = $targetName;
                            $this->oneToManyReferenceKey = $targetReferenceName;
                        }
                    }

                }
            }

        }

    }

    /**
     * match entity from another
     * @param $propertyName
     * @param $docs
     */
    private function manyToOne($propertyName, $docs)
    {

        $position = strpos($docs, "@ManyToOne");

        if ($position !== FALSE) {
            Preference::$DO_RELATION = false;

            $entityName = "";
            //relation column
            $targetName = "";
            $targetReferenceName = "";
            $propertyInfo = "";
            for ($b = $position; $b < strlen($docs); $b++) {
                /*Last Character*/
                if (empty($docs[$b]) || $docs[$b] == "*") {
                    break;
                }
                /***************/
                $propertyInfo .= $docs[$b];
            }

            /*break property info*/
            $propertyInfo = str_replace("@ManyToOne", "", $propertyInfo);
            $propertyInfo = str_replace("[", "", $propertyInfo);
            $propertyInfo = str_replace("]", "", $propertyInfo);
            $splitProperty = explode(",", $propertyInfo);

            for ($splitIndex = 0; $splitIndex < count($splitProperty); $splitIndex++) {
                if (strpos($splitProperty[$splitIndex], "Entity") !== FALSE) {
                    $splitValue = explode("=", $splitProperty[$splitIndex]);
                    $trimVal = trim($splitValue[1]);
                    if (!empty($trimVal))
                        $entityName = $trimVal;
                }
                if (strpos($splitProperty[$splitIndex], "target") !== FALSE && empty($requestType)) {
                    $splitValue = explode("=", $splitProperty[$splitIndex]);
                    $trimVal = trim($splitValue[1]);
                    if (!empty($trimVal))
                        $targetName = $trimVal;
                }
                if (strpos($splitProperty[$splitIndex], "targetReference") !== FALSE && empty($requestType)) {
                    $splitValue = explode("=", $splitProperty[$splitIndex]);
                    $trimVal = trim($splitValue[1]);
                    if (!empty($trimVal))
                        $targetReferenceName = $trimVal;
                }
            }

            if (!empty($entityName)) {
                //do the magic
                $splitContent = explode("/", $entityName);
                if (count($splitContent)) {
                    $module = (!empty($splitContent[0])) ? $splitContent[0] : "";
                    $className = (!empty($splitContent[1])) ? $splitContent[1] : "";
                    $nsClass = $module . "\\" . "Models" . "\\" . $className . "Model";
                    $setterRelation = $this->gettingSetter($propertyName, $this->class);
                    if (method_exists($this->class, $setterRelation)) {
                        $properties = null;
                        //getter properties
                        $getterProperties = "getDbProperties";
                        if (method_exists($this->class, $getterProperties)) {
                            $properties = $this->class->$getterProperties();
                        }

                        //relation instance
                        $instance = new $nsClass($properties);

                        /*verify columns*/
                        $columnsToVerify = $this->columns;
                        $isThere = false;
                        for ($c = 0; $c < count($columnsToVerify); $c++) {
                            if ($columnsToVerify[$c]["name"] == $targetReferenceName) {
                                $isThere = true;
                                break;
                            }
                        }

                        if (!$isThere && property_exists($this->class, $targetReferenceName)) {
                            $isThere = true;
                        }

                        if ($isThere && property_exists($instance, $targetName)) {
                            //setter relation instance
                            $this->class->$setterRelation($instance);
                            array_push($this->manyToOneObjects, $instance);
                            $this->manyToOneKey = $targetName;
                            $this->manyToOneReferenceKey = $targetReferenceName;
                        }
                    }

                }
            }

        }

    }

    /**
     * verify setter method by property
     * @param string $key column name
     * @param Aorm $class
     * @return string
     * @throws RuntimeException
     */
    public function gettingSetter($key, Aorm $class = null)
    {
        $class = ($class !== null) ? $class : $this->class;
        $columnName = $key;
        $columnName = ucwords(str_replace("_", " ", $columnName));
        $columnName = str_replace(" ", "", $columnName);
        $setter = "set" . $columnName;
        if (!method_exists($class, $setter)) {
            throw new RuntimeException(" setter method not found " . $setter);
        }

        return $setter;
    }

    /**
     * verify getter method by property
     * @param $key
     * @param Aorm|null $class
     * @return string
     * @throws RuntimeException
     */
    public function gettingGetter($key, Aorm $class = null)
    {
        $class = ($class !== null) ? $class : $this->class;
        $columnName = $key;
        $columnName = ucwords(str_replace("_", " ", $columnName));
        $columnName = str_replace(" ", "", $columnName);
        $getter = "get" . $columnName;
        if (!method_exists($class, $getter)) {
            throw new RuntimeException(" getter method not found " . $getter);
        }

        return $getter;
    }

    /**
     * add object to array list to save or update
     * @param stdClass $object
     */
    public function addToList(stdClass $object)
    {

        $arrayList = $this->getArrayListToHandle();

        array_push($arrayList, $object);

        $this->setArrayListToHandle($arrayList);

    }

    /**
     * Json string by object model
     * @return string
     */
    public function toString()
    {

        $columns = $this->columns;
        $string = "{";
        for ($i = 0; $i < count($columns); $i++) {
            $object = (object)$columns[$i];
            $columnKey = $object->name;
            $columnName = $columnKey;
            $columnName = ucwords(str_replace("_", " ", $columnName));
            $columnName = str_replace(" ", "", $columnName);
            $getter = "get" . $columnName;
            if (!method_exists($this->class, $getter)) {
                throw new RuntimeException(" getter method not found " . $getter);
            }

            $string .= $columnKey . " : " . $this->class->$getter() . "\n\n";
        }

        $string .= " } ";

        return $string;
    }

    /**
     * set object for save or update
     * @param stdClass $object field + value of database table
     * @param array $excludeKeys
     * @throws RuntimeException
     * @return void
     */
    public function setObjectModelValue( stdClass $object, $excludeKeys = array() ) {

        if ( empty($object) ) {
            throw new RuntimeException("Object could not be null");
        }

        foreach ( $object as $key => $val ) {
            if ( count( $excludeKeys ) > 0 && ( in_array( $key, $excludeKeys ) ) ) {
                continue;
            }
            $setterMethod = $this->gettingSetter($key, $this->class);
            $this->class->$setterMethod($val);
        }

    }

    /**
     * set serialize form for save or update
     * @param array $form
     * @param array $excludeKeys
     * @throws RuntimeException
     * @return void
     */
    public function setSerializeFormModelValue( array $form, $excludeKeys = array() ) {

        if ( count($form) < 1 ) {
            throw new RuntimeException("Form could not be null");
        }

        foreach ( $form as $index => $val ) {

            if ( !isset($val["name"]) ) {
                continue;
            }

            if ( !isset($val["value"]) ) {
                continue;
            }

            $name = $val["name"];
            $value = $val["value"];

            if ( count( $excludeKeys ) > 0 && ( in_array( $name, $excludeKeys ) ) ) {
                continue;
            }

            $setterMethod = $this->gettingSetter($name, $this->class);
            $this->class->$setterMethod($value);
        }
    }

    /**
     * get Object value
     * @param stdClass $object
     * @param $key
     * @param bool $decodeJson return a JSON value
     * @return mixed|null
     */
    public function getObjectModelValueByKey( stdClass $object, $key, $decodeJson = false ) {

        if ( empty($object) ) {
            throw new RuntimeException("Object could not be null");
        }

        $response = null;

        foreach ( $object as $keyObject => $val ) {
            if ( $keyObject == $key ) {
                $response = ($decodeJson) ? json_decode($val) : $val;
                break;
            }
        }

        return $response;

    }

    /**
     * get serialize form value
     * @param array $form
     * @param $key
     * @param bool $decodeJson return a JSON value
     * @return mixed|null
     */
    public function getSerializeFormModelValueByKey( array $form, $key, $decodeJson = false ) {

        if ( count($form) < 1 ) {
            throw new RuntimeException("Form could not be null");
        }

        $response = null;
        foreach ( $form as $index => $val ) {

            if ( !isset($val["name"]) ) {
                continue;
            }

            if ( !isset($val["value"]) ) {
                continue;
            }

            $name = $val["name"];
            $value = $val["value"];

            if ( $name == $key ) {
                $response = ($decodeJson) ? json_decode($value) : $value;
                break;
            }

        }

        return $response;

    }

}