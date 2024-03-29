<?php
/**
 * Created by User: BVA
 * Date: 25.08.2021
 * Time: 13:20
 */

namespace soffit;

use soffit\db\Table;
use libs\classes\Validator;

/**
 * По сути менеджер таблиц
 * Функционал взаимодействия с таблицами БД
 *
 * Примеры:
    $aq = new ActiveQuery();
    $stock = $aq->registerTable('stock','st');
    $images = $aq->registerTable(['images'=>'img']);
    $aq->link(['id'=>$stock], '=', ['pos_id'=>$images]);
 *
    $sum = function() {
        $fNames = ['a'=>'model_weight','b'=>'status'];
        return ['fieldNames'=>$fNames, 'function'=>"SUM(%a% + %b%)"];
    };
    $imgConcat = function() {
        $fNames = ['a'=>'img_name','b'=>'pos_id'];
        return ['fieldNames'=>$fNames, 'function'=>"CONCAT(%a%, '-', %b%)"];
    };
 *
    $res = $stock
    ->select(['mID'=>'id','model_type','number_3d'])
    ->select(['model_weight','sumMW'=>$sum])
    ->join($images,['pos_id','imgName'=>$imgConcat,'main','sketch'])
    ->andON($images,'sketch', '=', 1)
    ->joinOr($images,'main', '=', 1)
    ->where('number_3d','=',$number_3d)->and('id','<>',$thisID)
    ->asArray()
    ->exe();
 *
    $countStock = function () {
        return ['function'=>"COUNT(1)"];
    };
    $res2 = $stock
    ->select(['countSt'=>$countStock])
    ->where('model_type','=','Кольцо')
    ->asOne('countSt')
    ->exe();
 *
 * Class ActiveQuery
 * @package Views\vendor\core
 */
class ActiveQuery extends Model
{


    /**
     * список имен таблиц из бд
     * @var array
     */
    //private $tables = [];

    /**
     * Массив объектов таблиц Table
     * @var array
     */
    //private $TABLES = [];
    private static $TABLES = [];

    /**
     * @var Validator
     */
    private Validator $validator;


    /**
     * ActiveQuery constructor.
     *
     * Пример:
     * (new ActiveQuery('stl_files'))->stl_files->select(['*'])->where(['pos_id','=',$this->id])->asOne()->exe();
     *
     * @param null $tables
     * @param string $alias
     * @throws \Exception
     */
    public function __construct( $tables = null, string $alias='')
    {
        $this->validator = Registry::init()->validator;//new Validator();

        if ( $tables )
            $this->registerTable($tables,  $alias);
    }


    /**
     * Вернет объект таблицы
     * @param string $tableName
     * @return Table
     */
    public function __get( string $tableName ) : Table
    {
        if ( array_key_exists($tableName,self::$TABLES) )
        {
            return self::$TABLES[$tableName];
        } else {
            throw new \Error("No table registered under name: " . $tableName, 500 );
        }
    }


    private function createTableAlias()
    {
        
        $newAlias = randomStringChars(2).randomStringChars(5,'en','symbols');
        foreach ( self::$TABLES as $alias => $tName )
        {
            if ( $alias == $newAlias )
                $newAlias = $this->createTableAlias();
        }
        return $newAlias;
    }

    /**
     * @param array|string $tables 
     * @param string $alias
     * @return Table
     * @throws \Exception
     */
    public function registerTable( $tables, string $alias='' ) : Table
    {
        if ( !empty($tables) && is_string($tables) )
        {
            if ( !$this->validator->validateTableName($tables) )
                throw new \Error("Table name '" . $tables . "' not valid.", 500 );

            /** Check if table already exists */
            $tnUpper = mb_strtoupper($tables);
            if ( array_key_exists($tnUpper, self::$TABLES) )
                return self::$TABLES[$tnUpper];

            $TName = new Table( $tables );
            if ( $alias )
            {
                $TName->alias = $alias;
            } else {
                // create alias
                $TName->alias = $this->createTableAlias();
            }
            self::$TABLES[$TName->alias] = $tables;

            $tables = mb_strtoupper($tables);
            return self::$TABLES[$tables] = $TName;
        }

        if ( !empty($tables) && is_array($tables) )
        {
            
            foreach ( $tables as $expTName => $expTAlias )
            {
                // передано только имя табл. Теперь $expTAlias - имя табл.
                $tableName = $expTName;
                $alias = $expTAlias;
                if ( is_int($tableName) )
                {
                    $tableName = $expTAlias;
                    $alias = '';
                }
                if ( !$this->validator->validateTableName($tableName) )
                    throw new \Error("Table name '" . $tableName . "' not valid.", 500 );

                /** Check if table already exists */
                if ( array_key_exists(mb_strtoupper($tableName), self::$TABLES) )
                    continue;

                /** Create a new one here! */
                $TName = new Table( $tableName );
                if ( $alias )
                {
                    $TName->alias = $alias;
                } else {
                    // create alias
                    $TName->alias = $this->createTableAlias();
                }
                
                self::$TABLES[$TName->alias] = $tableName;

                $tableName = mb_strtoupper($tableName);
                self::$TABLES[$tableName] = $TName;
            }
            

            // вернет последнюю добавленную табл.
            return self::$TABLES[array_key_last(self::$TABLES)];
        }

        throw new \Error("Table name '" . $tables . "' not valid.", 500 );
    }

    /**
     * @param array $tables
     * @return array
     * @throws \Exception
     */
    public function getRegisteredTables( $tables = [] ) : array
    {
        $result = [];

        if ( is_string($tables) && !empty($tables) )
        {
            if ( in_array($tables,self::$TABLES) )
                return $result[$tables] = self::$TABLES[$tables]->showSchema();

            throw new \Error("No table registered under name: " . $tables, 500 );
        }

        if ( is_array($tables) && !empty($tables) )
        {

            foreach ( $tables as $tableName )
            {
                if ( in_array($tableName,self::$TABLES) )
                {
                    $result[$tableName] = self::$TABLES[$tableName]->showSchema();
                } else {
                    throw new \Error("No table registered under name: " . $tableName, 500 );
                }
            }
            return $result;
        }

        return self::$TABLES;
    }


    /**
     * Свяжем таблицы по полям (Необходим для JOIN)
     * Пример: $aq->link(['id'=>$stock], '=', ['pos_id'=>$images]);
     *
     * @param array $key_table_first ---- [ 'id'=>$stock ]
     * @param string $operator
     * @param array $key_table_second ---- [ 'pos_id'=>$images ]
     * @return array
     */
    public function link( array $key_table_first, string $operator ,array $key_table_second )
    {

        $nameKeyFirst = array_key_first($key_table_first); // имя ключа 1
        $tableFirst = $key_table_first[$nameKeyFirst]; // таблица 1

        $nameKeySecond = array_key_first($key_table_second); // имя ключа 2
        $tableSecond = $key_table_second[$nameKeySecond]; // таблица 2

        if ( !($tableFirst instanceof Table) || !($tableSecond instanceof Table) )
            throw new \Error("Wrong tables objects given in: " . __METHOD__, 500 );

        if ( !in_array( $tableFirst, self::$TABLES ) && !in_array( $tableSecond, self::$TABLES ) )
            throw new \Error("Some tables not register in : " . __CLASS__, 500 );

        $tNameFirst = mb_strtoupper($tableFirst->getName());
        $tNameSecond = mb_strtoupper($tableSecond->getName());

        if ( !in_array($nameKeyFirst,$tableFirst->showSchema()) )
            throw new \Error("Table '" . $tNameFirst ."' don't contain field '" . $nameKeyFirst . "'" . __CLASS__, 500 );

        if ( !in_array($nameKeySecond,$tableSecond->showSchema()) )
            throw new \Error("Table '" . $tNameSecond ."' don't contain field '" . $nameKeySecond . "'" . __CLASS__, 500 );

        $res[] = self::$TABLES[ $tNameFirst ]->link($tableSecond->getName(), $nameKeySecond, $operator, $nameKeyFirst );
        $res[] = self::$TABLES[ $tNameSecond ]->link($tableFirst->getName(), $nameKeyFirst, $operator, $nameKeySecond );

        return $res;
    }


}