<?php
namespace Views\_ModelView\Models;
use Views\_Globals\Models\General;
use Views\_Globals\Models\User;
use Views\_SaveModel\Models\ImageConverter;
use Views\vendor\core\HtmlHelper;


class ModelView extends General {

	
	private $id;
	public $number_3d;

	public  $row;
	public  $coll_id;
	public  $rep_Query;
	
	private $img;
	//private $img_Query;
	private $gems_Query;
	private $dopVc_Query;
	private $stl_Query;
	private $complected;
	private $ai_Query;

    /**
     * ModelView constructor.
     * @param bool $id
     * @throws \Exception
     */
    public function __construct($id = false )
    {
        parent::__construct();

        $this->id = (int)$id;

        $this->connectToDB();
        $this->dataQuery();
    }

    /**
     * @throws \Exception
     */
    public function dataQuery()
    {


		$this->row  = $this->findOne( " SELECT * FROM stock    WHERE     id='$this->id' ");
		$this->img  = $this->findAsArray( " SELECT * FROM images   WHERE pos_id='$this->id' ");


        $this->gems_Query  = mysqli_query($this->connection, " SELECT * FROM gems      WHERE pos_id='$this->id' ");
        $this->dopVc_Query = mysqli_query($this->connection, " SELECT * FROM vc_links  WHERE pos_id='$this->id' ");
        $this->stl_Query   = mysqli_query($this->connection, " SELECT * FROM stl_files WHERE pos_id='$this->id' ");
        $this->ai_Query    = mysqli_query($this->connection, " SELECT * FROM ai_files  WHERE pos_id='$this->id' ");
        $this->rep_Query   = mysqli_query($this->connection, " SELECT * FROM repairs   WHERE pos_id='$this->id' ");
		
		$this->number_3d = $this->row['number_3d'];

		$sql = " SELECT st.id, st.model_type, st.number_3d, img.pos_id, img.img_name, img.main, img.sketch
				FROM stock st 
					LEFT JOIN images img ON ( st.id = img.pos_id )
				WHERE st.number_3d='{$this->number_3d}' 
				AND st.id<>'{$this->id}' ";                        //AND img.main=1
		$this->complected = $this->findAsArray( $sql );
	}

    /**
     * @return array
     * @throws \Exception
     */
    public function getCollections()
    {
        $collections = explode(';',$this->row['collections']);
        $collectionStr = '';
        foreach ( $collections as $collection ) $collectionStr .= "'".$collection."',";
        $collectionStr = "(". trim($collectionStr,",") . ")";

        return $this->findAsArray(" SELECT id,name FROM service_data WHERE name IN $collectionStr AND tab='collections' ");
    }
	
	public function getStl()
    {
		if ( $this->stl_Query->num_rows > 0 ) {
			$stl_file = mysqli_fetch_assoc($this->stl_Query);
            return $stl_file;
		}
        return false;
	}

	public function getAi()
    {
		if ( $this->ai_Query->num_rows > 0 )
		{
			$ai_file = mysqli_fetch_assoc($this->ai_Query);
			return $ai_file;
		}
		return false;
	}

    /**
     * @return array|bool
     * @throws \Exception
     */
    public function get3dm()
    {
        return $this->findOne( " SELECT * FROM rhino_files WHERE pos_id='$this->id' ");
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function usedInModels()
    {
        $vc = "";
        if ( !empty( $this->row['vendor_code'] ) ) $vc = "OR vc_3dnum LIKE '%{$this->row['vendor_code']}%'";

        $sql = " SELECT s.id, s.number_3d, s.vendor_code, s.model_type FROM stock as s WHERE s.id IN
                  ( SELECT pos_id FROM vc_links WHERE vc_3dnum LIKE '%{$this->number_3d}%' $vc ) 
                  AND s.id <> {$this->row['id']}";
        return $this->findAsArray( $sql );
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDescriptions()
    {
        $sql = "SELECT d.num, d.text, DATE_FORMAT(d.date, '%d.%m.%Y') as date, d.pos_id, u.fio as userName
                FROM description as d
                  LEFT JOIN users as u
                    ON (d.userID = u.id ) 
                WHERE d.pos_id = $this->id";
        return $this->findAsArray( $sql );
    }

    /**
     * @param bool $forPdf
     * @return array|string
     * @throws \Exception
     */
	public function getComplectes($forPdf=false)
    {
    	if ( empty($this->complected) ) return [];
        if ($forPdf) return $this->complected;

        return $this->sortComplectedData($this->complected,['id','number_3d','model_type']);
	}

    /**
     * @return array
     * @throws \Exception
     */
    public function getImages()
    {
		$images = [];

        foreach ( $this->img as &$img ) $images[$img['id']] = $img; // чтоб работали клики по мал. картинкам

        foreach ( $images as &$image )
        {
            $path = $this->number_3d.'/'.$this->id.'/images/';
            $fileImg = $image['img_name'];

            if ( !file_exists(_stockDIR_.$path.$fileImg) )
            {
                $image['imgPath'] = _stockDIR_HTTP_."default.jpg";
            } else {
                // Файл Есть!
                $image['imgPath'] = _stockDIR_HTTP_.$path.$fileImg;

                // Проверим превьюшку
                $image['imgPrevPath'] = '';
                if ( $prevImgName = $this->checkSetPreviewImg($path, $fileImg) )
                {
                    $image['imgPrevPath'] = _stockDIR_HTTP_.$path.$prevImgName;
                } elseif ( ImageConverter::makePrev( $path, $fileImg ) ) {
                    // Превью создана!
                    $image['imgPrevPath'] = _stockDIR_HTTP_ . $path . ImageConverter::getLastImgPrevName();
                }
            }

        }

		return $images;
	}

    /**
     * проверим наличие статусов в картинках
     * что бы выбрать, какую отобразить главной
     * @param array $images
     * @return array
     */
	public function choseMainImage( array &$images ) : array
    {
        $mainImg = [];
        $mainImgID = '';
        $setMainImg = function(&$image)
        {
            $mainImg['src'] = $image['imgPath'];
            $mainImg['id'] = $image['id'];
            $image['active'] = 1;

            return $mainImg;
        };

        foreach ( $images as &$image )
        {
            if ( trueIsset($image['main']) )
            {
                $mainImgID = $image['id'];
                break;
            }
            if ( trueIsset($image['sketch']) )
            {
                $mainImgID = $image['id'];
            }
        }

        //везьмем первую, если ничего не выбрали
        if ( !$mainImgID )
        {
            $mainImg = $setMainImg($images[array_key_first($images)]);
        } else {
            $mainImg = $setMainImg($images[$mainImgID]);
        }

        return $mainImg;
    }

    /**
     * @return array|bool
     * @throws \Exception
     */
    public function getModelMaterials()
	{
		$addEdit = new \Views\_AddEdit\Models\AddEdit($this->id);

        $addEdit->connectDBLite();
        $mats = $addEdit->getMaterials($this->row);
        //$addEdit->closeDB();
        return $mats;
	}


	public function getGems() {
		$result = array();
		$c = 0;
		while( $row_gems = mysqli_fetch_assoc($this->gems_Query) ) {
			if ( !empty($row_gems['gems_sizes']) ) {
				$sizeGem = is_numeric($row_gems['gems_sizes']) ? "Ø".$row_gems['gems_sizes']." мм" : $row_gems['gems_sizes']." мм";	
			}
			if ( !empty($row_gems['value']) ) $valueGem = $row_gems['value']." шт";
			$result[$c]['gem_num'] = $c+1;
			$result[$c]['gem_size'] = $sizeGem;
			$result[$c]['gem_value'] = $valueGem;
			$result[$c]['gem_cut'] = $row_gems['gems_cut'];
			$result[$c]['gem_name'] = $row_gems['gems_names'];
			$result[$c]['gem_color'] = $row_gems['gems_color'];
			$c++;
		}
		return $result;
	}

    /**
     * @param $id
     * @param $vc_3dNum
     * @return string
     * @throws \Exception
     */
    protected function links($id, $vc_3dNum)
    {
        $sql = "SELECT st.id, st.number_3d, img.pos_id, img.img_name, img.main, img.sketch
				FROM stock st 
					LEFT JOIN images img ON ( img.pos_id = $id )
				WHERE st.id='$id' ";
        $linkQuery = $this->findAsArray( $sql );

        $fileImg = $this->sortComplectedData($linkQuery,['id','number_3d'])[$id]['img_name'];

        $html = new HtmlHelper();
        return $html->tag("a")
                    ->setAttr(['imgtoshow'=>$fileImg, 'href'=>HtmlHelper::URL('/',['id'=>$id])]) //_rootDIR_HTTP_ .'model-view/?id='.$id
                    ->setTagText($vc_3dNum)->create();

        //return '<a imgtoshow="'.$fileImg.'" href="'. _rootDIR_HTTP_ .'model-view/?id='.$id.'">'.$vc_3dnum.'</a>';
    }

    /**
     * @param $vc_3dnum
     * @param $vc_name
     * @return null|string
     * @throws \Exception
     */
    protected function vc_3dnumExpl($vc_3dnum, $vc_name)
    {
        $arr = explode('/',$vc_3dnum);
        $quer = mysqli_query($this->connection, " SELECT id,number_3d,vendor_code FROM stock WHERE model_type='$vc_name' ");

        $link  = null;

        if ( $quer->num_rows > 0 ) {


            while( $row_vc = mysqli_fetch_assoc($quer) ) {

                if ( !empty($row_vc['vendor_code']) )
                {
                    if ( trim($arr[0]) == $row_vc['vendor_code'] ) {
                        $link = $this->links($row_vc['id'], $vc_3dnum);
                        break;
                    }
                }

                if ( trim($arr[0]) == $row_vc['number_3d'] )
                {
                    $link = $this->links($row_vc['id'], $vc_3dnum);
                    break;
                }

                if ( isset($arr[1]) )
                {
                    if ( !empty($row_vc['vendor_code']) ) {
                        if ( trim($arr[1]) == $row_vc['vendor_code'] ) {
                            $link = $this->links($row_vc['id'], $vc_3dnum);
                            break;
                        }
                    }
                    if ( trim($arr[1]) == $row_vc['number_3d'] ) {
                        $link = $this->links( $row_vc['id'], $vc_3dnum);
                        break;
                    }
                }
            }
        }
        return $link;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getDopVC() {
		
		$result = array();
		$c = 0;
		while( $row_dop_vc = mysqli_fetch_assoc($this->dopVc_Query) ) {
			
			$linkVCnum = $this->vc_3dnumExpl($row_dop_vc['vc_3dnum'], $row_dop_vc['vc_names'] );
			$linkVCnum = $linkVCnum ? $linkVCnum : $row_dop_vc['vc_3dnum'];
			
			$result[$c]['vc_num'] = $c+1;
			$result[$c]['vc_names'] = $row_dop_vc['vc_names'];
			$result[$c]['vc_link'] = $linkVCnum;
			$result[$c]['vc_descript'] = $row_dop_vc['descript'];
			$c++;
		}
		return $result;
	}


	public function getRepairs()
    {
        $repairs = [];

        if ( $this->rep_Query )
            while($repRow = mysqli_fetch_assoc($this->rep_Query)) $repairs[] = $repRow;

        return $repairs;
    }

	public function getLabels($labelsStr=false)
    {
        return parent::getLabels($this->row['labels']);
    }

    /**
     * @param bool $id
     * @param string $status_name
     * @param string $status_date
     * @return array
     * @throws \Exception
     */
    public function getStatuses($id = false, $status_name = '', $status_date = '' )
    {
        $statuses = $this->getStatLabArr('status');
        $result = [];
        $stats_quer = mysqli_query($this->connection, " SELECT status,name,date FROM statuses WHERE pos_id='{$this->id}' ");

        if ( !mysqli_num_rows($stats_quer) )
        {
            $statusT = [];
            $statusT['pos_id'] = $this->id;
            $statusT['status'] = $this->row['status'];
            $statusT['creator_name'] = "";
            $statusT['UPdate'] = $this->row['status_date'];
            $this->addStatusesTable($statusT);
            foreach ( $statuses?:[] as $status )
            {
                if ( $statusT['status'] === $status['name_ru'] )
                {
                    $result[0]['class'] = $status['class'];
                    $result[0]['classMain'] = $status['name_en'];
                    $result[0]['glyphi'] = $status['glyphi'];
                    $result[0]['title'] = $status['title'];
                    $result[0]['status'] = $status['name_ru'];
                    $result[0]['name'] = $statusT['name'];
                    $result[0]['date'] = ($statusT['date'] == "0000-00-00") ? "" : date_create( $statusT['UPdate'] )->Format('d.m.Y')."&#160;";
                    break;
                }
            }

            //debug($result,'$result',1);
            return $result;
        }

        $c = 0;
        while( $statuses_row = mysqli_fetch_assoc($stats_quer) )
        {
            foreach ( $statuses as $status )
            {
                if ( $statuses_row['status'] === $status['id'] )
                {
                    $result[$c]['ststus_id'] = $status['id'];
                    $result[$c]['class'] = $status['class'];
                    $result[$c]['classMain'] = $status['name_en'];
                    $result[$c]['glyphi'] = $status['glyphi'];
                    $result[$c]['title'] = $status['title'];
                    $result[$c]['status'] = $status['name_ru'];
                    $result[$c]['name'] = $statuses_row['name'];
                    $result[$c]['date'] = ($statuses_row['date'] == "0000-00-00") ? "" : date_create( $statuses_row['date'] )->Format('d.m.Y')."&#160;";
                    $c++;
                    break;
                }
            }

        }
        return $result;
    }


    /**
     * смотрим отрисовывать ли нам кнопку едит
     * @throws \Exception
     */
    public function editBtnShow() : bool
    {
        if ( User::permission('editModel') )
        {
            return true;
        } elseif ( User::permission('editOwnModels') ) {
            $userRowFIO = User::getSurname();
            $authorFIO = $this->row['author'];
            $modellerFIO = $this->row['modeller3D'];
            $jewelerName = $this->row['jewelerName'];
            if ( mb_stristr($authorFIO, $userRowFIO) !== FALSE || mb_stristr($modellerFIO, $userRowFIO) !== FALSE || mb_stristr($jewelerName, $userRowFIO) !== FALSE )
                return true;
        }
        return false;
    }

    /**
     * пока не нужно
     */
    private function setPrevPage()
    {
        $thisPage = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if ( $thisPage !== $_SERVER["HTTP_REFERER"] ) {
            $_SESSION['prevPage'] = $_SERVER["HTTP_REFERER"];
        }
    }

}