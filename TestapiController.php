<?php
namespace Product;

use Phalcon\Mvc\Controller;
use Dtb\Container;

class testapiController extends Controller {
	private static $_config = array(
		'bjdtbtest001' => '/../../datebaoservice/config/test.php',
		'hz-b-admin' => array(
				'host' => 'rds7433q2l6ok838g8x6.mysql.rds.aliyuncs.com',
	            'database' => 'dtbserver',
	            'username' => 'dtbdata',
	            'password' => 'secret',
	            'charset' => 'utf8',
	            'collation' => 'utf8_unicode_ci',
	        ),
	    );	
	private $db;
	private static $premium = array(
			'bjdtbtest001' =>
				array(
					'host' => '123.56.7.10',
		            'database' => 'jssclub',
		            'username' => 'test',
		            'password' => 'dtbdjeKmd_test11',
		            'charset' => 'utf8',
		            'collation' => 'utf8_unicode_ci',
		        ),
		    'hz-b-admin' =>
		        array(
					'host' => 'rm-bp105o3l96876g529.mysql.rds.aliyuncs.com',
		            'database' => 'jssclub',
		            'username' => 'jssclub',
		            'password' => 'D3uw69cK',
		            'charset' => 'utf8',
		            'collation' => 'utf8_unicode_ci',
		        ), 
		);
	private $_data =  array(
			'period' => 'range',
			'idSite' => '1',
			'date'   => 'last30',
			'format' => 'json',
			'showRawMetrics' => '1',
			'module' => 'API',
			'method' => 'Live.getLastVisitsDetails',
			'filter_limit' => '100',
			'showColumns' => 'latitude,longitude,city',
			'token_auth' => '058ebe276fe30126cf8f26da4898ac3b',
		);
	//地图默认值
	private static $_fill_data = array(
		array(
			'name' => 'SheHeZi',
			'value' => array('86.041865', '44.308259', 5),
		),
		array(
			'name' => 'HaMi',
			'value' => array('93.529373', '42.344467', 5),
		),
		array(
			'name' => 'LaSa',
			'value' => array('91.111891', '29.662557', 5),
		),
		array(
			'name' => 'XiNing',
			'value' => array('101.767921', '36.640739', 5),
		),
		array(
			'name' => 'YuShu',
			'value' => array('96.672119', '32.922694', 5),
		),
		array(
			'name' => 'TianShui',
			'value' => array('105.736932', '34.584319', 5),
		),
		array(
			'name' => 'JiuQuan',
			'value' => array('98.508415', '39.741474', 5),
		),
		array(
			'name' => 'QingYang',
			'value' => array('107.644227', '35.726801', 5),
		),
		array(
			'name' => 'ETuSheng',
			'value' => array('76.174896', '39.722079', 5),
		),
		array(
			'name' => 'KeLaMaYi',
			'value' => array('84.88118', '45.594331', 5),
		),
		array(
			'name' => 'HuHuoHaoTe',
			'value' => array('111.660351', '40.828319', 5),
		),
		array(
			'name' => 'BaoTou',
			'value' => array('109.846239', '40.647119', 5),
		),
		array(
			'name' => 'WuHai',
			'value' => array('106.831999', '39.683177', 5),
		),	
		array(
			'name' => 'QiQiHaEr',
			'value' => array('123.987289', '47.3477', 5),
		),
		array(
			'name' => 'GuangXi',
			'value' => array('108.334063', '22.821171', 5),
		),
	);
	//来源数据表
	private $_pie_chat1_data = array();
	//业务线流量数据
	private $_pie_chat2_data = array();
	private static $_pie_chat1_format = array(
		'广告活动' => '自然流量',
		'直接链接' => '付费推广',
		'网站' => '网站',
		'搜索引擎' => '搜索引擎',
	);
	//明星产品 TOP10
	private $_product_top10 = array('name' => array(), 'data' => array());
	//业务保费
	private $_payprice = array('name' => array(), 'data' => array());
	private static $_arrFormat = array(
				'premium' => '晶算师',
				'pcpayprice' => '商城-PC',
				'h5payprice' => '商城-H5',
				'apppayprice' => '商城-APP',
				'alipay' => '阿里-支付宝',
				'tianmao' => '阿里-天猫',
				'bdpayprice' => 'BD合作',
			);
	//被保人年龄-性别
	private $_policyInsured = array();
	const URL = 'https://analytics.datebao.com/index.php';

	const CACHERKEY = 'mapDataCache';
	const HELIUKEY = 'heliuDataCache';
	
	public function echartsShowAction() {
		$echarts_map = json_encode($this->getMapInfo());
		$this->getRefer();
		$this->getYesterdayData();
		$this->inssureData();
		$this->productTop();	
		$this->policyInsured();
		$this->smarty->assign('data', $echarts_map);
		$this->smarty->assign('pie_chat1', $this->_pie_chat1_data);
		$this->smarty->assign('pie_chat2', $this->_pie_chat2_data);
		$this->smarty->assign('product_top10', $this->_product_top10);
		$this->smarty->assign('payprice', $this->_payprice);
		$this->smarty->assign('policy_insured', $this->_policyInsured);
		$this->smarty->display('test/banner.tpl');
	}
	// Echarts map ajax request
	public function getAjaxMapAction() {
		if(isset($_GET['ajax']) && $_GET['ajax'] == 1){
			$result = $this->getMapInfo();
		}else{
			$result = array('code' => 1, 'message' => '系统错误！');
		}
		echo json_encode($result);
	}
	// Echarts map
	public function getMapInfo() {
		$memcache = Container::getMemcache();
		$arr = $memcache->get(self::CACHERKEY);
		if(empty($arr)){
			$arr = $this->getCurlData(time());
			$arr = json_encode($arr);
			$memcache->set(self::CACHERKEY, $arr, 86400);
		}
		$arr = json_decode($arr);
		$arr = array_merge(self::$_fill_data,$arr);
		return $arr;
	}
	//Echarts themeRiver ajax
	public function getAjaxThemeRiverAction() {
		$this->inssureData();
		if(isset($_GET['ajax']) && $_GET['ajax'] == 1){
			$page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
			$pageSize = !empty($_GET['pageSize']) ? intval($_GET['pageSize']) : 3;
			$data = json_decode($this->_payprice['data'],true);
			$name = json_decode($this->_payprice['name'],true);
			$data_count = count($data);
			$name_count = count($name);
			$real_count = $data_count/$name_count;
			//$pageNum = $real_count%$pageSize ? ceil($real_count/$pageSize) : $real_count/$pageSize;
			//每次向前走一条，共走pageNum次
			$pageNum = $real_count-$pageSize;
			if($page > $pageNum){
				$page = 1;
			}
			//$offset = ($page-1) * $pageSize;
			$offset = $page-1;
			//重新组合数据
			$new_data = array();
			for($i = 0;$i < $data_count; $i += $real_count){
				$temp_data = array_slice($data, $offset+$i, $pageSize);
				$new_data = array_merge($new_data,$temp_data);
			}
			$result = array('code' => 0, 'page' => $page+1, 'data' => $new_data);
		}else{
			$result = array('code' => 1, 'message' => '系统错误！');
		}
		echo json_encode($result);
	}
	//map data curl request
	public function getCurlData($time){
		$this->_data['minTimestamp'] = '"'.$time.'"';
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, self::URL);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');	
		curl_setopt($curl, CURLOPT_POSTFIELDS, $this->_data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$data = curl_exec($curl);
		curl_close($curl);
		$data = json_decode($data);

		$arr = $this->modifyFormat($data);
		return $arr;
	}
	//map data modifyFormat
	public function modifyFormat($arr){
		$data = array();
		foreach($arr as $k=>$v){
			$data[$k]['city'] = $v->city;
			$data[$k]['latitude'] = $v->latitude;
			$data[$k]['longitude'] = $v->longitude;
		}

		$city = array_column($data, 'city');
		$city = array_filter($city);
		$value_arr = array_count_values($city);

		$return_arr = $city = array();
		$i = 0;
		foreach($data as $key => $value){
			if(in_array($value['city'],$city) || is_null($value['city'])){
				continue;
			}
			$city[] = $value['city'];
			$return_arr[$i]['name'] = $value['city'];
			$return_arr[$i]['value'][0] = $value['longitude'];
			$return_arr[$i]['value'][1] = $value['latitude'];
			$return_arr[$i]['value'][2] = $value_arr[$value['city']] * 5;
			$i++;
		}
		return $return_arr;
	}

	//访问来源数据报表
	public function getRefer() {
        $token_auth = '058ebe276fe30126cf8f26da4898ac3b';

        // we call the REST API and request the 100 first keywords for the last month for the idsite=7
        $url = "http://analytics.datebao.com/index.php";
        $url .= "?module=API&method=Referrers.getReferrerType";
        $url .= "&idSite=1&period=day&date=yesterday";
        $url .= "&format=JSON";
        $url .= "&token_auth=$token_auth";
       
        $fetched = file_get_contents($url);
        $query = json_decode($fetched, true);

        $visits = array('label' => array(), 'visit' => array());
        foreach ($query as $data) {
            $visit = array(
                'name' => self::$_pie_chat1_format[$data['label']],
                'value' => $data['nb_uniq_visitors'],
            );
            array_push($visits['label'], self::$_pie_chat1_format[$data['label']]);
            array_push($visits['visit'], $visit);
        }
        
        $this->_pie_chat1_data = $visits;
    }

    //从piwik中取出UV数据
    public function  getYesterdayData() {
        //token
        $token_auth = '058ebe276fe30126cf8f26da4898ac3b';
        //piwik-晶算师项目
        $jssall = "http://analytics.datebao.com/index.php";
        $jssall .= "?module=API&method=API.get";
        $jssall .= "&idSite=2&period=day&date=yesterday";
        $jssall .= "&format=JSON";
        $jssall .= "&token_auth=$token_auth";
        $fetched_all = file_get_contents($jssall);
        $query_jssall = json_decode($fetched_all);

        //piwik-大特保项目
        $dtball = "http://analytics.datebao.com/index.php";
        $dtball .= "?module=API&method=API.get";
        $dtball .= "&idSite=1&period=day&date=yesterday";
        $dtball .= "&format=JSON";
        $dtball .= "&token_auth=$token_auth";
        $fetched_dtb = file_get_contents($dtball);
        $query_dtball = json_decode($fetched_dtb);


        //piwik-大特保项目
        $url = "http://analytics.datebao.com/index.php";
        $url .= "?module=API&method=API.get";
        $url .= "&idSite=1&period=day&date=yesterday";
        $url .= "&format=JSON";
        $url .= "&token_auth=$token_auth";
        $url .= "&segment=";
        //piwik-商城-PC端
        $url_pc = $url . "deviceType==Desktop";
        $fetched_pc = file_get_contents($url_pc);
        $query_pc = json_decode($fetched_pc);


        //晶算师-UV数据
        $yesterday_user['yesterdayjssuv'] = $query_jssall->nb_uniq_visitors;
        //大特保项目-UV数据
        $yesterday_user['yesterdaydtbuv'] = $query_dtball->nb_uniq_visitors;
        //总计UV
        $yesterday_user['yesterdayuvtotal'] = $yesterday_user['yesterdaydtbuv']+$yesterday_user['yesterdayjssuv'];
        //商城-PC-UV数据
        $yesterday_user['yesterdaypcuv'] = $query_pc->nb_uniq_visitors;

        //商城APP和移动网页和微信总和
        $url_wap = $url . "deviceType!=Desktop";
        $fetched_wap = file_get_contents($url_wap);
        $query_wap = json_decode($fetched_wap);


        //商城APP和移动网页和微信总和UV
        $yesterday_user['yesterdaywapuv'] = $query_wap->nb_uniq_visitors;

        //商城IOS
        $urls = "http://analytics.datebao.com/index.php";
        $urls .= "?module=API&method=API.get";
        $urls .= "&format=JSON";
        $urls .= "&idSite=1&period=day&date=yesterday";
        
        $urls .= "&filter_limit=false&format_metrics=1&expanded=1";
        $urls .= "&token_auth=$token_auth";
        $urls .= "&segment=";
        $url_ios = $urls . "referrerName==from_ios";
        $fetched_ios = file_get_contents($url_ios);
        $query_ios = json_decode($fetched_ios);

        //商城IOS-UV
        $yesterday_user['yesterdayiosuv'] = $query_ios->nb_uniq_visitors;

        //商城ANDROID
        $url_android = $urls . "referrerName==from_android";
        $fetched_android = file_get_contents($url_android);
        $query_android = json_decode($fetched_android);

        //商城ANDROID-UV
        $yesterday_user['yesterdayandroiduv'] = $query_android->nb_uniq_visitors;
        //商城APP-UV
        $yesterday_user['yesterdayappuv'] = $yesterday_user['yesterdayiosuv']+$yesterday_user['yesterdayandroiduv'];
        //商城-H5(移动页面和微信)-UV
        $yesterday_user['yesterdayh5uv'] = $yesterday_user['yesterdaywapuv']-$yesterday_user['yesterdayappuv'];
        //商城UV合计
        $yesterday_user['yesterdaymalluv'] = $yesterday_user['yesterdaypcuv']+$yesterday_user['yesterdaywapuv'];

        $percentage_jss = number_format($yesterday_user['yesterdayjssuv']/$yesterday_user['yesterdayuvtotal']*100,2);
        $percentage_pc = number_format($yesterday_user['yesterdaypcuv']/$yesterday_user['yesterdayuvtotal']*100,2);
        $percentage_ios = number_format($yesterday_user['yesterdayiosuv']/$yesterday_user['yesterdayuvtotal']*100,2);
        $percentage_android = number_format($yesterday_user['yesterdayandroiduv']/$yesterday_user['yesterdayuvtotal']*100,2);
        $percentage_h5 = number_format($yesterday_user['yesterdayh5uv']/$yesterday_user['yesterdayuvtotal']*100,2);
        $percentage_other = 100-$percentage_jss-$percentage_pc-$percentage_ios-$percentage_android-$percentage_h5;

        $data = array(
        	array('name' => '晶算师', 'value' => $percentage_jss),
        	array('name' => 'PC', 'value' => $percentage_pc),
        	array('name' => 'IOS', 'value' => $percentage_ios),
        	array('name' => 'ANDROID', 'value' => $percentage_android),
        	array('name' => 'H5', 'value' => $percentage_h5),
        	array('name' => '其它', 'value' => $percentage_other),
        );
        $this->_pie_chat2_data = array(
        	'list' => json_encode(array('晶算师','PC','IOS','ANDROID','H5','其它')),
        	'data' => json_encode($data),
        );

    }

    //连接数据库
   	public function connectMysql() {
   		try{
   			$hostname = gethostname();
   			if($hostname == 'homestead' || $hostname == 'bj_dtbdev'){
    			$hostname = 'bjdtbtest001';
    		}
   			$config = self::$_config[$hostname];
   			if(!is_array($config)){
   				if(!file_exists($_SERVER['DOCUMENT_ROOT'].$config)){
		   			Container::getLogger()->error('配置文件不存在：'.$_SERVER['DOCUMENT_ROOT'].$config);
		   		}
	   			$file = require($_SERVER['DOCUMENT_ROOT'].$config);
		   		$config = $file['mysql']['default'];
   			}
	   		$dsn = 'mysql:dbname='.$config['database'].';host='.$config['host'].';charset='.$config['charset'];
	        $this->db = new \PDO($dsn, $config['username'], $config['password']);

   		}catch (\Exception $e){
   			Container::getLogger()->error('连接数据库错误：'.$e);
   		}
   	}
   	//明星产品TOP10
   	public function productTop() {
   		try{
   			$this->connectMysql();
	   		$sql = 'SELECT
	 					m.`inner_product_name`, sum(p.`pay_price`+p.use_balance) as price
					FROM
	 					`policy_order` p
					JOIN product_main m ON m.`id` = p.`product_main_id`
					WHERE
	 					 p.insurance_policy_id IS NOT NULL
						 AND p.insurance_policy_status <> 4
						 AND DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= p.policy_time
						 GROUP BY
	                     m.`inner_product_name`
						 ORDER BY
	       				 sum(p.`pay_price`+p.use_balance) desc LIMIT 10';

	       $result = $this->db->query($sql)->fetchAll();
	       $name = array_column($result, 'inner_product_name');
	       $name = array_map(function($a){
	       		return mb_substr($a,0,6,'utf-8').'****';
	       },$name);
	       $this->_product_top10['name'] = $name;
	       foreach($result as $k => $v){
	       		$this->_product_top10['data'][$k] = $v['price'] * 5;
	       }
	       $this->_product_top10['name'] = json_encode($this->_product_top10['name']);
	       $this->_product_top10['data'] = json_encode($this->_product_top10['data']);

   		} catch(\Exception $e){
   			Container::getLogger()->error('明星产品TOP10 error: '.$e);
   		}
   	}	

   	//各业务线保费数据   
    public function inssureData() {
    	try{
    		$memcache = Container::getMemcache();
			$arr = $memcache->get(self::HELIUKEY);
			$clear_cache = false;
			if(date('H') == 00 && date('i') == 59 && date('s') == 59){
				$clear_cache = true;
			}
			if(date('H') == 01 && date('i') == 00 && date('s') <= 30){
				$clear_cache = true;
			}
			if(!empty($arr) && $clear_cache === false){
				$arr = json_decode($arr,true);
				$this->_payprice['name'] = json_encode($arr['name']);
				$this->_payprice['data'] = json_encode($arr['data']);
				return;
			}
    		$this->connectMysql();
    		$hostname = gethostname();
    		if($hostname == 'homestead' || $hostname == 'bj_dtbdev'){
    			$hostname = 'bjdtbtest001';
    		}
    		$config = self::$premium[$hostname];
    		$dsn = 'mysql:dbname='.$config['database'].';host='.$config['host'].';charset='.$config['charset'];
        	$pdo = new \PDO($dsn, $config['username'], $config['password']);

    		$payprice_premium_sql = "SELECT sum(opp.premium) as premium,substr(opp.policy_time,1,10) as policy_time  FROM  order_policy opp LEFT JOIN `order` poo ON poo.id= opp.order_id  WHERE opp.policy_status!= 4 AND poo.orderno is not null and DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= opp.policy_time GROUP BY substr(opp.policy_time,1,10)";

    		$payprice_pc_sql = "SELECT sum(p.pay_price+ p.use_balance) as pcpayprice, substr(p.policy_time,1,10) as policy_time FROM policy_order p where p.insurance_policy_id is not null and p.insurance_policy_status<> 4 and p.policy_way= 1 and DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= p.policy_time GROUP BY substr(p.policy_time,1,10)";

    		$payprice_h5_sql = "SELECT sum(p.pay_price+ p.use_balance) as h5payprice,substr(p.policy_time,1,10) as policy_time FROM policy_order p where p.insurance_policy_id is not null and p.insurance_policy_status<> 4 and p.policy_way in (2,6,7) and DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= p.policy_time GROUP BY substr(p.policy_time,1,10)";

    		$payprice_app_sql = "SELECT sum(p.pay_price+ p.use_balance) as apppayprice,substr(p.policy_time,1,10) as policy_time FROM policy_order p where p.insurance_policy_id is not null and p.insurance_policy_status<> 4 and p.policy_way in (3,4,5) and DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= p.policy_time GROUP BY substr(p.policy_time,1,10)";

    		$payprice_alipay_sql = "SELECT sum(p.pay_price+p.use_balance) as alipay,substr(p.policy_time,1,10) as policy_time  FROM policy_order p where p.insurance_policy_id is not null and p.insurance_policy_status<> 4 and p.developer_id = 47 and DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= p.policy_time GROUP BY substr(p.policy_time,1,10)";

    		$payprice_tianmao_sql = "SELECT sum(p.pay_price+p.use_balance) as tianmao,substr(p.policy_time,1,10) as policy_time FROM policy_order p where p.insurance_policy_id is not null and p.insurance_policy_status<> 4 and p.developer_id = 22 and DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= p.policy_time GROUP BY substr(p.policy_time,1,10)";

    		$payprice_bd_sql = "SELECT sum(p.pay_price+p.use_balance) as bdpayprice,substr(p.policy_time,1,10) as policy_time FROM policy_order p where p.insurance_policy_id is not null and p.insurance_policy_status<> 4 and p.developer_id in (16,26,48,33,50,1001) and DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= p.policy_time GROUP BY substr(p.policy_time,1,10)";

    		$result['premium'] = $pdo->query($payprice_premium_sql)->fetchAll();
    		$result['pcpayprice'] = $this->db->query($payprice_pc_sql)->fetchAll();
    		$result['h5payprice'] = $this->db->query($payprice_h5_sql)->fetchAll();
    		$result['apppayprice'] = $this->db->query($payprice_app_sql)->fetchAll();
    		$result['alipay'] = $this->db->query($payprice_alipay_sql)->fetchAll();
    		$result['tianmao'] = $this->db->query($payprice_tianmao_sql)->fetchAll();
    		$result['bdpayprice'] = $this->db->query($payprice_bd_sql)->fetchAll();
    		
    		$policy_time = array();

    		foreach($result as $k => $v){
    			if(empty($v)){
    				unset($result[$k]);
    				continue;
    			}
    			$data['name'][] = self::$_arrFormat[$k];
    			$policy_time = array_merge_recursive($policy_time,array_column($v, 'policy_time'));
    		}
    		$policy_time = array_filter($policy_time);
    		$policy_time = array_unique($policy_time);

    		$data['data'] = array();
    		$i = 0;
    		foreach($result as $key => $value){
    			$t = $i;
    			foreach($value as $k => $v){
    				if($v[$key] == null){
    					continue;
    				}
    				$data['data'][$i] = array($v['policy_time'],$v[$key],self::$_arrFormat[$key]);
    				$i++;
    			}
    			$temp_arr = array_column($value, 'policy_time');
    			$diff_time = array_diff($policy_time,$temp_arr);
    			$diff_time = array_values($diff_time);
    			if(!empty($diff_time)){
    				for($j = 0; $j < count($diff_time); $j++){
    					$data['data'][$i] = array($diff_time[$j],'0',self::$_arrFormat[$key]);
    					$i++;
    				}
    			}
    			$temp_data = array_slice($data['data'], $t, $i-$t);
    			$date = array();
    			foreach($temp_data as $v){
    				$date[] = $v[0];
    			}
    			array_multisort($date, SORT_ASC, $temp_data);
    			array_splice($data['data'], $t, $i-$t, $temp_data);
    		}
    		$this->_payprice['name'] = json_encode($data['name']);
    		$this->_payprice['data'] = json_encode($data['data']);
    		$memcache->set(self::HELIUKEY, json_encode($data), 86400);

    	} catch (\Exception $e){
    		Container::getLogger()->error('inssureData error: '.$e);
    	}
    }
    //被保人年龄性别
    public function policyInsured() {
    	try{
    		$this->connectMysql();
    		$strtotime = time()-3600*24*30;
    		$start_date = date('Y-m-d H:i:s', $strtotime);
    		$end_date = date('Y-m-d H:i:s');
	    	$m_sql = "select age,count('age') as count from policy_insured_person where sex='M' and age is not null and deleted_at is null and updated_at between '".$start_date."' and '".$end_date."' group by age";
	    	$f_sql = "select age,sex,count('age') as count from policy_insured_person where sex='F' and age is not null and deleted_at is null and updated_at between '".$start_date."' and '".$end_date."' group by age";
	    	
	    	$result['M'] = $this->db->query($m_sql)->fetchAll();
	    	$result['F'] = $this->db->query($f_sql)->fetchAll();

	    	//计算x-Axis 间隔值  start
	    	$m_age_arr = array_column($result['M'], 'age');
	    	$f_age_arr = array_column($result['F'], 'age');
	    	$a = max($m_age_arr);
	    	$b = max($f_age_arr);
	    	$max = max($a,$b);
	    	$step = ceil($max/10);
	    	$len = $max+(10-$max%10);
	    	$x_step = array();
	    	for($i = 0; $i <= $len; $i += $step){
	    		$x_step[] = $i;
	    	}
	    	//计算x-Axis 间隔值 end

	    	//计算气泡的缩放比例 start
	    	$m_count_arr = array_column($result['M'], 'count');
	    	$f_count_arr = array_column($result['F'], 'count');
	    	$m_max = max($m_count_arr);
	    	$f_max = max($f_count_arr);
	    	$m_ratio = ceil($m_max/15);
	    	$f_ratio = ceil($f_max/15);
	    	//计算气泡的缩放比例  end

	    	$data = array();
	    	//思路  给纵轴设置$step = 5个点，每个点随机取数
	    	$step = 5;
	    	foreach($result as $key => $value) {
	    		$j = 0;
	    		$count = count($value);
	    		$each_dot_show = (int)($count/$step);
	    		if($key == 'M'){
	    			$ratio = $m_ratio;
	    		}elseif($key == 'F') {
	    			$ratio = $f_ratio;
	    		}
	    		for($t = 0; $t < $each_dot_show; $t++){
	    			for($i = 0; $i < $step; $i++){
		    			$rand_keys = array_rand($result[$key]);	    			
		    			$data[$key][$j] = array($value[$rand_keys]['age']/10, $i, ceil($value[$rand_keys]['count']/$ratio));
		    			unset($result[$key][$rand_keys]);
		    			$j++;
		    		}
	    		}

	    		if($count % $step){
	    			for($t = 0; $t < $count % $step; $t++){
	    				$rand_keys = array_rand($result[$key]); 			
		    			$data[$key][$j] = array($value[$rand_keys]['age']/10, $t, ceil($value[$rand_keys]['count']/$ratio));
		    			unset($result[$key][$rand_keys]);
		    			$j++;
	    			}
	    			
	    		}
	    	}

	    	$this->_policyInsured['x_step'] = json_encode($x_step);
	    	$this->_policyInsured['M'] = json_encode($data['M']);
	    	$this->_policyInsured['F'] = json_encode($data['F']);
	    	$this->_policyInsured['m_ratio'] = $m_ratio;
	    	$this->_policyInsured['f_ratio'] = $f_ratio;
    	}catch (\Exception $e){
    		Container::getLogger()->error('policyInsured error: '.$e);
    	}
    }   
}