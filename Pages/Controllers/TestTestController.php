<?php
/**
 * Date: 16.08.2021
 * Time: 21:18
 */
namespace controllers;

use soffit\ActiveQuery;
use models\Handler;

class TestTestController extends GeneralController
{

    public string $title = 'ХЮФ :: тестовая';


    /**
     * @throws \Exception
     */
    public function beforeAction()
    {

        if ( !_DEV_MODE_ )
            $this->redirect('/main/');

        $request = $this->request;

        if ( $request->isAjax() )
        {
            exit;
        }
    }


    /**
     * @throws \Exception
     */
    public function action()
    {
        $hello = "Preview Test Area!";

        $thisID = 2145;
        $number_3d = '0008000';
        /*
        $handler = new Handler();
        $res = $handler->setNumber_3d();
        debug($res,'res', 1);
        */

        $aq = new ActiveQuery();
        $stock = $aq->registerTable('description');
        $repQuery = $aq->description->count("c")->asOne('c')->where('id','=',30)->exe();

        debug($repQuery,'repQuery', dump:true);
        debug(is_int('46'),'try str', 1, dump:true);
        /*
        $stock = $aq->registerTable('stock','st');
        $images = $aq->registerTable(['images'=>'img']);
        $aq->link(['id'=>$stock], '=', ['pos_id'=>$images]);
        $sum = function()
        {
            $fNames = ['a'=>'model_weight','b'=>'status'];
            return ['fieldNames'=>$fNames, 'function'=>"SUM(%a% + %b%)"];
        };
        $imgConcat = function()
        {
            $fNames = ['a'=>'img_name','b'=>'pos_id'];
            return ['fieldNames'=>$fNames, 'function'=>"CONCAT(%a%, '-', %b%)"];
        };
        $res = $stock
            ->select(['mID'=>'id','model_type','number_3d'])
            ->select(['model_weight','sumMW'=>$sum])
            ->join($images,['pos_id','imgName'=>$imgConcat,'main','sketch'])
            ->andON($images,'sketch', '=', 1)
            ->joinOr($images,'main', '=', 1)
            ->where('number_3d','=',$number_3d)
            ->and('id','<>',$thisID)
            ->asArray()
            ->exe();

        $sql = " SELECT st.id, st.model_type, st.number_3d, img.pos_id, img.img_name, img.main, img.sketch
				FROM stock st 
					LEFT JOIN images img ON ( st.id = img.pos_id ) AND img.sketch=1
				WHERE st.number_3d='{$number_3d}' 
				AND st.id<>'{$thisID}' ";
        $old_style  = $aq->findAsArray( $sql );


        $countStock = function ()
        {
            return ['function'=>"COUNT(1)"];
        };
        $res2 = $stock
            ->select(['countSt'=>$countStock])
            ->where('model_type','=','Кольцо')
            ->asOne('countSt')
            ->exe();

        $statuses = $aq->registerTable('statuses');
        $count3 = $statuses->count('c')->where(['pos_id','=',2515])->asOne('c')->exe();
        */

        //$testform1 = $stock->findAll()->where(['id','>',70],['email','like','dant'])->with('files')->orderby('name')->limit(2)->go();
        //$testform1 = $stock->findAll()->limit(1)->go();
        //$testform1 = $stock->findOne()->where(['id','<',72])->with('files')->go();

        $compacted = compact(['hello','res', 'res2', 'build', 'where','old_style', 'count3']);
        return $this->render('test', $compacted);
    }



}