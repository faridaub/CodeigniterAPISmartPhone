<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 -------------------------------------------------------------------------------------------------------------------
* @ 開発者		: Anamul Haq Farid
* @ 日付			: 2014年08月11日
* @ 更新日		: 2014年08月18日
* @ バッチ処理名	: 空室差分情報バッチ機能
* @ 内容			: スマートフォン向けBサービス側の空室情報検索を実行し、空室差分情報を取得してAPIサーバの空室情報テーブルに反映
-------------------------------------------------------------------------------------------------------------------
*/

class Vacancy_diff extends CI_Controller {
	//-------------------------------------------------------------------------------------------------------------------
	//@ コンストラクターメソッド
	//-------------------------------------------------------------------------------------------------------------------
	public function __construct() {
		parent::__construct ();
		$this->load->library ("xmlrpc");
		$this->load->model("Vacancydiff_model");
		$this->initialSettup();
	}
 	
	//-------------------------------------------------------------------------------------------------------------------
	//@ 空室情報更新バッチ（全件）
	//-------------------------------------------------------------------------------------------------------------------
	public function index(){
		$this->Vacancydiff_model->run();		
	}
	
	//-------------------------------------------------------------------------------------------------------------------
	// @  プログラム起動修理
	//-------------------------------------------------------------------------------------------------------------------
	public function initialSettup(){
		ini_set('memory_limit', '2024M');
		set_time_limit(0);
	}
}
