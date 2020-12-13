<?php
namespace Views\_SaveModel\Controllers;

use Views\vendor\core\Crypt;
use Views\vendor\core\Files;
use Views\vendor\libs\classes\AppCodes;

use Views\_Globals\Controllers\GeneralController;
use Views\_Globals\Models\{ PushNotice,SelectionsModel,User };
use Views\_SaveModel\Models\{
    Handler, HandlerPrices, Condition, HandlerRepairs, SaveModelProgressCounter
};


/**
 * Date: 02.12.2020
 * Time: 21:05
 *
 * Обрабатывает данные из формы сохранения модели
 */
class SaveModelController extends GeneralController
{

    /**
     * @var SaveModelProgressCounter()
     */
    public $progress;

    /**
     * @var Handler()
     */
    public $h;

    /**
     * @var
     * form fields uses here, in other methods
     */
    public $stockID;
    public $number3d;
    public $modelType;
    public $date;

    public $paymentsRequisite = [];

    /**
     * @var bool
     * Отражает присутствие полученного статуса в списке статусов этой модели
     */
    public $isCurrentStatusPresent;

    /**
     * @var array
     */
    public $response = [];

    /**
     * @throws \Exception
     */
    public function beforeAction() : void
    {
        if ( !$this->request->isAjax() )
            $this->redirect('main/');

        try {
            $saveModel = $this->session->getKey('saveModel');
            if ($saveModel) {
                $saveModel = Crypt::strDecode($saveModel);
                $save = Crypt::strDecode( $this->request->post('save') );
                if ($saveModel !== $save)
                    exit(json_encode(['error' => AppCodes::getMessage(AppCodes::MODEL_OUTDATED)]));

                /** Method success here!! **/
                $files = Files::instance();
                $img_fP = $files->count('updateImages');
                $stl_fP = $files->count('fileSTL');
                $rhino_fP = $files->count('file3dm');
                $ai_fP = $files->count('fileAi');

                $this->progress = new SaveModelProgressCounter( $this->request->post('userName'),
                    $this->request->post('tabID'),
                    $img_fP + $stl_fP + $rhino_fP + $ai_fP + 5);

            } else {
                exit(json_encode(['error' => AppCodes::getMessage(AppCodes::MODEL_OUTDATED)]));
            }
        } catch ( \TypeError | \Error | \Exception $e ) {
            $this->serverError_ajax( $e );
        }
    }

    /**
     * @throws \Exception
     */
    public function action()
    {
        $progress = $this->progress;
        //============= CP ==============//
        $progress->count();

        chdir(_stockDIR_);
        $request = $this->request;

        $id = $this->stockID = (int)Crypt::strDecode($request->post('id'));
        $handler = $this->h = new Handler($id);
        try {
            $handler->connectToDB();
        } catch ( \Exception $e) {
            $this->serverError_ajax($e);
        }

        Condition::set( (int)$request->post('edit') );
        $isEdit = (int)$request->post('edit') === 2 ? true : false;

        $date = $this->date =  date("Y-m-d");
        if ( Condition::isNew() )
            $this->number3d = $handler->setNumber_3d();
        if ( Condition::isInclude() || Condition::isEdit() )
            $this->number3d = $handler->setNumber_3d( strip_tags(trim( $request->post('number_3d') )) );

        $model_type = $this->modelType = strip_tags( trim($request->post('model_type')) );
        $handler->setModel_typeEn($model_type);
        $handler->setModel_type($model_type);

        $handler->setIsEdit($isEdit);
        $handler->setDate($date);

        // проверяем поменялся ли номер 3Д
        if ( Condition::isEdit() ) $handler->checkModel();

        try {

            $textDataSave = $this->actionSaveData_Text();

            $pricesDataSave = $this->actionSaveData_Prices(new HandlerPrices($id));

            $repairsDataSave = $this->actionSaveData_Repairs();

            $filesDataSave = $this->actionSaveData_Files();

        } catch ( \TypeError | \Error | \Exception $e) {
            $this->serverError_ajax($e);
        }

    }

    public function afterAction()
    {
        parent::afterAction();

        $this->session->dellKey('saveModel');

        $this->actionResponse();
    }


    /**
     * @throws \Exception
     */
    protected function actionSaveData_Text()
    {
        $request = $this->request;
        $handler = $this->h;

        // берем все остальное
        $vendor_code  = strip_tags( trim($request->post('vendor_code')) );
        $handler->setVendor_code($vendor_code);

        $author       = strip_tags(trim( $request->post('author') ));
        $modeller3d   = strip_tags(trim( $request->post('modeller3d') ));
        $jewelerName  = strip_tags(trim( $request->post('jewelerName') ));
        $size_range   = strip_tags(trim( $request->post('size_range') ));
        $model_weight = strip_tags(trim( $request->post('model_weight') ));
        $print_cost   = strip_tags(trim( $request->post('print_cost')) );
        $model_cost   = strip_tags(trim( $request->post('model_cost')) );
        $collection   = $handler->setCollections( $request->post('collection') );
        $description  = strip_tags(trim( $request->post('description')));
        $str_labels   = $handler->makeLabels( $request->post('labels') );
        $creator_name = User::getFIO();
        $status       = (int)$request->post('status'); // ID статуса

        $this->paymentsRequisite['vendor_code'] = $vendor_code;
        $this->paymentsRequisite['status'] = $status;
        $this->paymentsRequisite['author'] = $author;
        $this->paymentsRequisite['modeller3d'] = $modeller3d;
        $this->paymentsRequisite['jewelerName'] = $jewelerName;

        $insertData = "";
        if ( !empty($this->number3d) && User::permission('number_3d') )
            $insertData .= "number_3d='$this->number3d',";

        if ( User::permission('vendor_code') )
            $insertData .= "vendor_code='$vendor_code',";

        if ( !empty($collection) && User::permission('collections') )
            $insertData .= "collections='$collection',";

        if ( !empty($author) && User::permission('author') )
            $insertData .= "author='$author',";

        if ( !empty($modeller3d) && User::permission('modeller3d') )
            $insertData .= "modeller3D='$modeller3d',";

        if ( User::permission('jewelerName') )
            $insertData .= "jewelerName='$jewelerName',";

        if ( !empty($this->modelType) && User::permission('model_type') )
            $insertData .= "model_type='$this->modelType',";

        if ( User::permission('size_range') )
            $insertData .= "size_range='$size_range',";

        if ( !empty($print_cost) && User::permission('print_cost') )
            $insertData .= "print_cost='$print_cost',";

        if ( !empty($model_cost) && User::permission('model_cost') )
            $insertData .= "model_cost='$model_cost',";

        if ( !empty($model_weight) && User::permission('model_weight') )
            $insertData .= "model_weight='$model_weight',";

        if ( User::permission('description') )
            $insertData .= "description='".trim($description)."',";

        if ( User::permission('labels') )
            $insertData .= "labels='$str_labels',";

        $insertData = trim($insertData,',');

        // добавляем во все комплекты артикул, если он есть
        $handler->addVCtoComplects($vendor_code);

        /** сохраняем новую модель **/
        $updateModelData = false;
        if ( Condition::isNew() || Condition::isInclude() )
        {
            $id = $this->stockID = $handler->addNewModel($this->number3d, $this->modelType); // возвращает id новой модели при успехе
            if ( !$id )
                throw new \Exception('Error in addNewModel(). No ID is coming!',198);

            // если забли поставить статус при доб. новой модели
            if ( $status === 0 ) $status = 35;

            $insertData .= ",status='$status',
                status_date='$this->date',
                creator_name='$creator_name',
                date='$this->date'
            ";

            //04,07,19 - вносим статус в таблицу statuses
            $statusT = [
                'pos_id'      => $id,
                'status'      => $status,
                'creator_name'=> $creator_name,
                'UPdate'      => date("Y-m-d H:i:s"),//$this->date
            ];
            $handler->addStatusesTable($statusT);

            $updateModelData = $handler->updateDataModel($insertData);
        }

        /** Редактируем **/
        if ( Condition::isEdit() )
        {
            // Проверяем поменялся ли номер 3Д, чтобы переместить файлы модели в др. папку
            $handler->checkModel();

            if ( $status && User::permission('statuses') )
                $this->isCurrentStatusPresent = $handler->isStatusPresent($status);

            $handler->updateDataModel($insertData);

            // добавим создателя, если его не было
            $handler->updateCreater($creator_name);

            // обновляем статус
            if ( $status && User::permission('statuses') )
                $handler->updateStatus($status, $creator_name);
        }

        // Описания
        if ( User::permission('description') )
            $this->response['notes'] = $handler->addNotes( $request->post('notes') );

        //============= CP ==============//
        $this->progress->count();

        $this->dataSave_Materials();
        $this->dataSave_Gems();
        $this->dataSave_VendorCodeLinks();

        return $updateModelData;
    }

    /**
     * МАТЕРИАЛЫ
     * @throws \Exception
     */
    protected function dataSave_Materials() : bool
    {
        if ( !User::permission('material') ) return false;

        $request = $this->request;
        $mats = $request->post('mats');
        if ( empty($mats) ) return false;


        $materialRows = $this->h->makeBatchInsertRow($mats, $this->stockID, 'metal_covering');
        if ( !$materialRows ) return false;

        //debug($materialRows,'makeBatchInsertRow',1,1);

        $this->response['materials']['insertUpdate'] = $this->h->insertUpdateRows($materialRows['insertUpdate'], 'metal_covering');
        $this->response['materials']['delete'] = $this->h->removeRows($materialRows['remove'], 'metal_covering');

        //============= CP ==============//
        $this->progress->count();

        return true;
    }

    /**
     * КАМНИ
     * @throws \Exception
     */
    protected function dataSave_Gems()
    {
        if ( !User::permission('gems') ) return false;

        $request = $this->request;
        $gems = $request->post('gems');
        if ( empty($gems) ) return false;

        $gemsRows = $this->h->makeBatchInsertRow( $gems, $this->stockID, 'gems');
        if ( !$gemsRows ) return false;

        $this->response['gems']['insertUpdate'] = $this->h->insertUpdateRows($gemsRows['insertUpdate'], 'gems');
        $this->response['gems']['delete'] = $this->h->removeRows($gemsRows['remove'], 'gems');


        //============= CP ==============//
        $this->progress->count();
        return true;
    }

    /**
     * ДОП. АТРИКУЛЫ
     * @throws \Exception
     */
    protected function dataSave_VendorCodeLinks()
    {
        if ( !User::permission('vc_links') ) return false;

        $request = $this->request;
        $vcl = $request->post('vc_links');
        if ( empty($vcl) ) return false;

        $vclRows = $this->h->makeBatchInsertRow( $vcl, $this->stockID, 'vc_links');
        if ( !$vclRows ) return false;

        $this->response['vc_links']['insertUpdate'] = $this->h->insertUpdateRows($vclRows['insertUpdate'], 'vc_links');
        $this->response['vc_links']['delete'] = $this->h->removeRows($vclRows['remove'], 'vc_links');

        //============= CP ==============//
        $this->progress->count();
        return true;
    }

    /**
     * ФАЙЛЫ
     * @throws \Exception
     */
    protected function actionSaveData_Files()
    {
        if ( !User::permission('files') )
            return;

        $request = $this->request;
        $files = Files::instance();

        if ( User::permission('images') )
        {
            $imgRows = [];
            $images = $request->post('image');

            if ( !empty($images['imgFor']) )
            {
                // Обновляем статусы существующих картинок
                $imgRows = $this->h->makeBatchImgInsertRow($images);
                $this->h->insertUpdateRows($imgRows['updateImages'], 'images');
            }

            if ( $files->count('UploadImages') || Condition::isInclude() )
            {
                if( !file_exists($this->number3d) )
                    mkdir($this->number3d, 0777, true);

                $path = $this->number3d.'/'.$this->stockID.'/images/';
                if( !file_exists($path) )
                    mkdir($path, 0777, true);

                $i = 0;
                $newImages = $imgRows['newImages'];
                if ( !empty($newImages[0]['img_name']) && $newImages[0]['sketch'] == 1 )
                {
                    $this->h->addIncludedSketch($newImages);
                    $i++;
                }

                $uploadImages = $files->makeHUA('UploadImages');
                foreach ( $uploadImages as $uploadImage )
                {
                    if ( $fileName = $this->h->uploadImageFile($uploadImage) )
                    {
                        $newImages[$i]['img_name'] = $fileName;
                    }
                    $i++;
                    //============= CP ==============//
                    $this->progress->count();
                }

                // вносим данные новых картинок
                $insertImages = $this->h->insertUpdateRows($newImages, 'images');
                if ( $insertImages === -1 )
                    throw new \Exception('Error in insertUpdateRows',1);
            }
        }


        if ( User::permission('stl') )
        {
            if ( $files->has('fileSTL') )
            {
                if( !file_exists($this->number3d) )
                    mkdir($this->number3d, 0777, true);

                $path = $this->number3d.'/'.$this->stockID.'/stl/';
                if( !file_exists($path) )
                    mkdir($path, 0777, true);

                $zipData = $this->h->openZip($path);

                $stlFileNames = [];
                $uploadStl = $files->makeHUA('fileSTL');
                foreach ( $uploadStl as $stl )
                {
                    $zipData['stl'] = $stl;
                    if ( $stlFileNames[] = $this->h->uploadStlFile( $zipData, $path ) )
                        $this->progress->count();
                }
                // closing Zip
                $this->h->insertStlData( $stlFileNames, $zipData );
            }

            if ( $files->has('file3dm') )
            {
                if( !file_exists($this->number3d) )
                    mkdir($this->number3d, 0777, true);

                $path = $this->number3d.'/'.$this->stockID.'/3dm/';
                if( !file_exists($path) )
                    mkdir($path, 0777, true);

                $this->h->add3dm( $files->get('file3dm') );

                $this->progress->count();
            }
        }

        if ( User::permission('ai') )
        {
            if ( $files->has('fileAi') )
            {
                if( !file_exists($this->number3d) )
                    mkdir($this->number3d, 0777, true);

                $path = $this->number3d.'/'.$this->stockID.'/ai/';
                if( !file_exists($path) )
                    mkdir($path, 0777, true);

                $this->h->addAi( $files->get('fileAi') );
                $this->progress->count();
            }
        }

    }


    /**
     * @param HandlerPrices $payments
     * @throws \Exception
     */
    public function actionSaveData_Prices( HandlerPrices $payments )
    {

        //if ( !isset($isEdit) ) $isEdit = 1; // редактирование
//        if ( !isset($modelID) )
//            if ( trueIsset($this->stockID) ) $modelID = $this->stockID;

        //$payments = new HandlerPrices($this->stockID);

        $status = $this->paymentsRequisite['status'];
        $author = $this->paymentsRequisite['author'];
        $modeller3d = $this->paymentsRequisite['modeller3d'];
        $jewelerName = $this->paymentsRequisite['jewelerName'];

        /** Добавим стоимость дизайна только для новой модели **/
        if (User::permission('MA_design'))
        {
            if ( Condition::isNew() )
                if ( $status === 35 )
                    if ( !$this->isCurrentStatusPresent )
                        if ( $payments->addDesignPrices('sketch', $author) === -1 )
                            $this->response['MA_design'] = AppCodes::getMessage(AppCodes::NOTHING_DONE)['message'];
        }

        /** зачислили дизайнеру, за утвержденный дизайн **/
        if ( User::permission('paymentManager') && User::permission('artCouncil') )
        {
            if ( $status === 89 )
                if ( !$this->isCurrentStatusPresent && $payments->isStatusPresent(35) )
                    if ($payments->addDesignPrices('designOK') === -1)
                        $this->response['MA_design'] = "not adding price";
        }

        if ( User::permission('MA_modeller3D') )
        {
            if ( Condition::isEdit() )
            {
                /** добавим Дизайнеру за сопровождение **/
                if ( $status === 8 ) // В работе 3D
                    if ( !$this->isCurrentStatusPresent && $payments->isStatusPresent(89) && $payments->isStatusPresent(35) )
                        if ($payments->addDesignPrices('escort3D') === -1)
                            $this->response['MA_modeller3D'] = "not adding price";
            }
            // инициируем вставку оценок моделироания только ели есть MA_modeller3D
            // и имя FIO моделлера == FIO юзера
            if ( $this->request->post('ma3Dgs') && trueIsset($modeller3d) )
                $payments->addModeller3DPrices($this->request->post('ma3Dgs'), $modeller3d);
        }

        if (User::permission('MA_techCoord'))
        {
            if ( Condition::isEdit() ) {
                if ( $status === 1 ) // На проверке
                    if ( !$this->isCurrentStatusPresent )   // && $payments->isStatusPresent(47) 47 -'Готово 3D'
                        if ($payments->addTechPrices('onVerify') === -1)
                            $this->response['MA_techCoord'] = "not adding price";

                if ( $status === 2 ) // Проверено
                    if (!$this->isCurrentStatusPresent && $payments->isStatusPresent(101) && $payments->isStatusPresent(1) )
                        if ($payments->addTechPrices('signed') === -1)
                            $this->response['MA_techCoord'] = "not adding price";
            }
        }

        if (User::permission('MA_techJew')) { // Технолог Юв (Валик)
            if ( Condition::isEdit() ) {
                if ( $status === 101 ) // Подписано технологом
                    if ( !$this->isCurrentStatusPresent && $payments->isStatusPresent(89) && $payments->isStatusPresent(1)  )
                        if ($payments->addTechPrices('SignedTechJew') === -1)
                            $this->response['MA_techJew'] = AppCodes::getMessage(AppCodes::NOTHING_DONE)['message'];
            }
        }

        if (User::permission('MA_3dSupport'))
        {
            if ( Condition::isEdit() )
            {
                if ( $status === 3 ) // Поддержки Убрал $this->isCurrentStatusPresent. Может выставлять поддержки много раз
                    if ( $payments->isStatusPresent(2) )
                        if ($payments->addPrint3DPrices('supports') === -1)
                            $this->response['MA_3dSupport'] = "not adding price";
            }
        }

        if (User::permission('MA_3dPrinting'))
        {
            if ( Condition::isEdit() ) {
                // На будущее
//        if ( $handler->isStatusPresent(2) ) // Есть Подписано - зачисляем стоимость печати
//            $handler->addPrintingPrices( $_POST['printingPrices']??[] );

                if ( $status === 5 ) //Выращено
                    if (!$this->isCurrentStatusPresent && $payments->isStatusPresent(2) )
                        if ($payments->addPrint3DPrices('printed') === -1)
                            $this->response['MA_3dPrinting'] = "not adding price";
            }
        }

        if (User::permission('MA_modellerJew'))
        {
            if ( Condition::isEdit() ) {
                // инициируем вставку оценок модельера-доработчика
                if ( trueIsset($this->request->post('modellerJewPrice')) && trueIsset($jewelerName) )
                    $payments->addModJewPrices('add', $this->request->post('modellerJewPrice'), $jewelerName );
            }
        }

        //if (User::permission('MA_modellerJew')) Возможно по UserAccess 8 участок ПДО
        if ( $status === 41 ) //На сбыте
            if ( !$this->isCurrentStatusPresent && $payments->isStatusPresent(89) && $payments->isStatusPresent(101) )
                if ($payments->addModJewPrices('signalDone') === -1)
                    $this->response['MA_3dPrinting'] = AppCodes::getMessage(AppCodes::NOTHING_DONE)['message'];
    }


    /**
     * @throws \Exception
     */
    protected function actionSaveData_Repairs()
    {
        $repairsResponse = [];

        if ( !User::permission('repairs') )
            return $repairsResponse;

        $hR = new HandlerRepairs($this->stockID);
        $request = $this->request;
        $repairs = $request->post('repairs');

        if ( User::permission('repairs3D') )
            $repairsResponse['3d'] = $hR->addRepairs( $repairs['3d'] );

        if ( User::permission('repairsJew') )
            $repairsResponse['jew'] = $hR->addRepairs( $repairs['jew'] );

        if ( User::permission('repairsProd') )
            $repairsResponse['prod'] = $hR->addRepairs( $repairs['prod'] );

        $this->progress->count();

        return $repairsResponse;
    }


    /**
     * Sending response via Ajax
     * @throws \Exception
     */
    protected function actionResponse()
    {
        // флаг для репоиска
        if ( $this->session->getKey('searchFor') )
            $this->session->setKey('re_search',true);

        $lastMess = "Модель добавлена";
        if ( Condition::isEdit() ) $lastMess = "Данные изменены";

        $this->response['isEdit'] = Condition::isEdit();
        $this->response['number_3d'] = $this->number3d;
        $this->response['model_type'] = $this->modelType;
        $this->response['lastMess'] = $lastMess;
        $this->response['id'] = $this->stockID;

        $status = $this->paymentsRequisite['status'];
        $vendor_code = $this->paymentsRequisite['vendor_code'];

        $pn = new PushNotice();
        $addPushNoticeResp = $pn->addPushNotice($this->stockID,
            Condition::isEdit()?2:1, $this->number3d, $vendor_code, $this->modelType, $this->date, $status, User::getFIO() );

        if ( !$addPushNoticeResp )
            $this->response['errors']['pushNotice'] = 'Error adding push notice';

        if ( !empty($_SESSION['selectionMode']['models']) )
        {
            $selection = new SelectionsModel();
            $selection->getSelectedModels();
        }

        $this->progress->count();

        exit( json_encode($this->response) );
    }

}