<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Check_initialization_api extends CI_Controller {

	var $FNCTIN = null;

	function __construct()
	{
		parent::__construct();
		$this->load->helper('log');
		$this->load->helper('date');
		$this->load->helper('url');
		$this->load->library('xmlrpc');
		$this->load->library('Api_const');
		$this->load->library('Api_date');
		$this->load->library('Api_com_util');
		$this->load->library('Api_util');
		$this->load->model('apikey_information_m','',true);
		$this->load->model('application_version_control_m','',true);
		$this->load->model('operational_log_m','',true);
		$this->lang->load('error', $this->api_util->getErrLang());
		$this->FNCTIN = Api_const::A033;	//初期設定情報入力チェックAPI
	}

	public function index()
	{
		// initialize
		$jsonOut = "";
		$error_code = true;
		$error_description = '';

		try {
			// API初期処理
			$rqst = $this->api_util->apiInit($this->FNCTIN);
			if ($rqst['errCode'] !== true) {
				$error_code = $this->api_util->setErrorCode($rqst['errCode']);
				$error_description = $this->lang->line($error_code);
				$this->api_util->apiEnd($jsonOut, $error_code, $error_description);
				return ;
			}
			// 必須項目
			$ids = array(
					'prcssngType',
					'lgnId',
					'lgnPsswrd',
					'mmbrshpFlag',
					'fmlyName',
					'frstName',
					'dateBirth',
					'ntnltyCode',
					'sex',
					'emlAddrss',
					'nwslttr'
					);
			//prcssngTypeが3：初めての方の時phnNmbrは必須
			if ($rqst['prcssngType']==$this->config->config['prcssngType_first']){
				array_push($ids,'phnNmbr');
			}
			// API共通チェック
			$chk_cmmn = $this->api_util->chkApiCommon($rqst, $ids);
			if ($chk_cmmn['errCode'] !== true){
				$error_code = $chk_cmmn['errCode'];
				$error_description = $this->lang->line($error_code);
				$this->api_util->apiEnd($jsonOut, $error_code, $error_description);
				return ;
			}
			// BIサービス呼び出し
			$host = $this->config->config['bi_service_host'];
			$port = $this->config->config['bi_service_port'];
			$method = "com.toyokoinn.api.service.SmartphoneApplicationCustomerService.checkInitializationEntry";
			$this->xmlrpc->server($host, $port);
			$this->xmlrpc->method($method);
			$request = array(
					array($rqst['applctnVrsnNmbr'], 'string'),
					array($rqst['lngg'], 'string'),
					array($rqst['prcssngType'], 'string'),
					array($rqst['lgnId'], 'string'),
					array($rqst['lgnPsswrd'], 'string'),
					array($rqst['mmbrshpFlag'], 'string'),
					array($rqst['mmbrshpNmbr'], 'string'),
					array($rqst['fmlyName'], 'string'),
					array($rqst['frstName'], 'string'),
					array($rqst['dateBirth'], 'string'),
					array($rqst['sex'], 'string'),
					array($rqst['ntnltyCode'], 'string'),
					array($rqst['phnNmbr'], 'string'),
					array($rqst['emlAddrss'], 'string'),
					array($rqst['nwslttr'], 'string'),
					array($rqst['emlAddrss2'], 'string')
			);
			if (array_key_exists('emlAddrss2',$rqst)&& $rqst['emlAddrss2']!=''){
				$request+=array($rqst['emlAddrss2'], 'string');
			}
			$this->xmlrpc->request($request);
			// Call BService
			if (!$this->xmlrpc->send_request()) {
				log_error($this->FNCTIN, 'send_request_error : '.$this->xmlrpc->display_error());
				$error_code = Api_const::BAPI1001;
				$error_description = $this->lang->line($error_code);
			} else {
				$b_result = $this->xmlrpc->display_response();
				$error_code = $b_result['errrCode'];
				if ($error_code==Api_const::BCMN0000) {
					$jsonOut = array();
					if (array_key_exists('cntMmbrshp',$b_result)){
						$jsonOut += array('cntMmbrshp' => $b_result['cntMmbrshp'],);
					}
					if (array_key_exists('mmbrshpNmbrArry',$b_result)){
						$jsonOut += array('mmbrshpNmbrArry' => $b_result['mmbrshpNmbrArry'],);
					}
				}
				else {
					if (array_key_exists('errrMssg', $b_result)){
						$error_description =  $this->lang->line($error_code);
					}
				}
			}
		} catch (Exception $e) {
			log_error($this->FNCTIN, 'exception : '.$e->getMessage());
			$error_code = Api_const::BAPI1001;
			$error_description = $this->lang->line($error_code);
		}
		// API終了処理
		$this->api_util->apiEnd($jsonOut, $error_code, $error_description);
	}
}