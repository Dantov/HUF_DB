<?php
namespace controllers;

use models\{General,PushNotice,User,Handler};
use soffit\Controller;
use libs\classes\AppCodes;


class GlobalsController extends Controller
{




    /**
     * @throws \Exception
     */
    public function action()
    {
        $request = $this->request;
        $session = $this->session;

        if ( $request->isAjax() )
        {
            try
            {
                if ( $searchInNum = (int)$request->post('searchInNum') )
                    $this->searchIn($searchInNum);

                if ( (int)$request->post('PushNotice') === 1 )
                    $this->actionPushNotice();

                if ( (int)$request->post('isPDF') === 1 )
                {
                    $handler = new Handler();
                    $arr['success'] = 0;
                    $pdfName = $request->post('pdfName');
                    if ( $pdfName ) $arr['success'] = $handler->deletePDF($pdfName);
                    exit( json_encode($arr) );
                }

                if ( (int)$request->get('getRepairNotices') === 1 )
                    exit( json_encode( $this->actionRepairNotices() ) );
				
				if ( (int)$request->get('getNew3DNotices') === 1 )
                    exit( json_encode( $this->actionNew3DNotices() ) );

            } catch (\TypeError | \Exception $e)
            {
                if ( _DEV_MODE_ )
                {
                    exit( json_encode([
                        'error'=>[
                            'message'=>$e->getMessage(),
                            'code'=>$e->getCode(),
                            'file'=>$e->getFile(),
                            'line'=>$e->getLine(),
                            'trace'=>$e->getTrace(),
                            'previous'=>$e->getPrevious(),
                        ]
                    ]) );
                } else {
                    exit( json_encode([
                        'error'=>[
                            'message'=>AppCodes::getMessage(AppCodes::SERVER_ERROR)['message'],
                            'code'=>$e->getCode(),
                        ],
                    ]) );
                }
            }

            exit;
        }

        // ******* SEARCH ******** //
        if ( $this->getQueryParam('search') === 'resetSearch' )
        {
            $this->resetSearch();
        }
        if ( !empty($request->post('searchFor')) || !empty($request->get('searchFor')) || $session->getKey('re_search') )
        {
            $session->dellKey('foundRow');
            $session->dellKey('countAmount');
            $searchFor = $request->post('searchFor')??$request->get('searchFor');

            if ($searchFor)
                $session->setKey('searchFor', $searchFor);
            //$search = new Search($session);
            //$this->foundRows = $search->search($searchFor);
            //$search->search($searchFor);
            //$session->setKey('searchFor',$searchFor);

            $this->redirect('/main/');
        } elseif ( $request->isPost() && empty($request->post('searchFor')) )
        {
            //$this->redirect('/globals/?search=resetSearch'); //'/main/?search=resetSearch'
            $this->resetSearch();
        }
    }



    protected function resetSearch() : void
    {
        $session = $this->session;
        $session->dellKey('searchFor');
        $session->dellKey('foundRow');
        $session->dellKey('countAmount');

        $selectionMode = $session->getKey('selectionMode');
        $selectionMode['models'] = [];
        $session->setKey('selectionMode',$selectionMode);

        $assist = $this->session->getKey('assist');
        $assist['collectionName'] = 'Все Коллекции';
        $this->session->setKey('assist', $assist);

        $session->setKey('re_search', false);
        $this->redirect('/main/');
    }


    
    /**
     * Смена режима поиска в нав. баре вверху
     * @param $searchInNum number
     */
    protected function searchIn($searchInNum) 
    {
        $session = $this->session;
        $assist = $session->getKey('assist');
        
        $resp = "";
        if ( $searchInNum === 1 ) {
                $assist['searchIn'] = 1;
                $resp = "В Базе ";
        }
        if ( $searchInNum === 2 ) {
                $assist['searchIn'] = 2;
                $resp = "В Коллекции ";
        }
        $session->setKey('assist', $assist);
        echo $resp;
    }





    /**
     * @throws \Exception
     */
    protected function actionPushNotice()
    {
        $request = $this->request;

        $pn = new PushNotice();

        if ( $noticeID = (int)$request->post('closeNotice') )
        {
            $arr['done'] = $pn->addIPtoNotice( $noticeID );
            echo json_encode($arr);
            exit;
        }

        if ( (int)$request->post('closeAllPN') )
        {
            $notIDs = $request->post('closeById');
            $arr['done'] = false;
            if ( is_array($notIDs) || !empty($notIDs) )
            {
                $arr['done'] = $pn->addIPtoALLNotices($notIDs);
            }
            echo json_encode($arr);
            exit;
        }

        if ( (int)$request->post('checkNotice') )
        {
            $arr = $pn->checkPushNotice();
            echo json_encode($arr);
            exit;
        }
    }



    /**
     * @throws \Exception
     */
    protected function actionRepairNotices()
    {
		if ( User::getAccess() == 8 )
			return (new PushNotice())->getRepairNoticesData('for_pdo');
		
        return (new PushNotice())->getRepairNoticesData();
    }


	
	/**
     * @throws \Exception
     */
    protected function actionNew3DNotices()
    {
        // для Богдана
        if ( User::getAccess() == 10 )
            return (new PushNotice())->getNew3DNoticesData(true);

        return (new PushNotice())->getNew3DNoticesData();
    }
    
}
