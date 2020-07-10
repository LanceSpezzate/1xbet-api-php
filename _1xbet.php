<?php
class xbet{
	//config 1xbet
	public $API='';
	public $DAPI='https://1xbet.com/';
	public $LVAPI='https://1xbet.com/LiveFeed/'; // LIVE API
	public $LNAPI='https://1xbet.com/LineFeed/'; // LINE API
	public $line_or_live=false; //false=line ; true= live
	public $LANGUAGE=null; // init language
	
	
	// getSportsShortZip
	public function get_sport(){
		$ok_odds=json_decode(file_get_contents(__dir__."/need/ok_odds.json"));
		$param=array(
			//'sports'=>0,
			'lng'=>$this->LANGUAGE,
			//'tf'=>1000000,
			//'country'=>1
		);

		$sports=$this->HTTP_SGET($this->API.'GetSportsShortZip',$param);

		$sports=json_decode($sports);
		$jsport=array();

		if(!isset($sports->Value))
			return false;

		for($i=0;$i<=count($sports->Value)-1;$i++){
			if(isset($sports->Value[$i]->N) && in_array($sports->Value[$i]->I,$ok_odds->game_bet)){
				array_push($jsport,array(
					'index'=>$sports->Value[$i]->I,
					'lan_text'=>$sports->Value[$i]->N,
					'en_text'=>$sports->Value[$i]->E,
					'count'=>$sports->Value[$i]->C,
					'value'=>array()
				));
			}ELSE{

			}
		}

		return $jsport;

	}
	function get_sport_league($sportid){
		//default line param
		$method="GetChampsZip";
		$array_param=array(
			'sport'=>$sportid,
			'lng'=>$this->LANGUAGE,
			'tf'=>1000000,
			'tz'=>5,
			'country'=>1
		);
		//live param
		if($this->line_or_live==true){
			$method="Get1x2_Zip";
			$array_param=array(
				"getEmpty"=>"true",
				"count"=>"500",
				"lng"=>$this->LANGUAGE,
				"sports"=>$sportid
			);
		}

		$sport=$this->HTTP_SGET($this->API.$method,$array_param);

		$sport=json_decode($sport);

		if(!isset($sport->Value))
			return false;

		$jsport=array();

		if(!isset($sport->Value))
			return null;
		//line get data
		if($this->line_or_live==false){
			for($i=0;$i<=count($sport->Value)-1;$i++){
				if(isset($sport->Value[$i]->L)){
					array_push($jsport,array(
						'index'=>$sport->Value[$i]->LI,
						'lan_text'=>$sport->Value[$i]->L,
						'icon'=>$sport->Value[$i]->CI,
						'count'=>$sport->Value[$i]->GC,
						'sport'=>(int)$sportid,
						'value'=>array()
					));
				}ELSE{

				}
			}
		}else{
			$tmp_index=array();
			for($i=0;$i<=count($sport->Value)-1;$i++){
				if(isset($sport->Value[$i]->LI)){
					if(!isset($tmp_index[$sport->Value[$i]->LI])){
						array_push($jsport,array(
							'index'=>$sport->Value[$i]->LI,
							'lan_text'=>$sport->Value[$i]->L,
							'icon'=>$sport->Value[$i]->COI,
							'sport'=>(int)$sportid,
							'value'=>array()
						));
						$tmp_index[$sport->Value[$i]->LI]='';
					}
				}
			}
		}

		return $jsport;
	}
	public function get_best($sportid,$count){
		//https://1xbetbk8.com/LiveFeed/BestGamesExtZip?sports=1&count=1000&lng=fa&mode=4&country=75

		$param_array=array(
			"sports"=>$sportid,
			"count"=>$count,
			"lng"=>$this->LANGUAGE,
			"mode"=>"4",
			"country"=>"75"
		);

		$sport=$this->HTTP_SGET($this->API.'BestGamesExtZip',$param_array);
		//echo $sport;exit();
		$sport=json_decode($sport);

		if(!isset($sport->Value))
			return false;

		$jsport=array();

		for($i=0;$i<=count($sport->Value)-1;$i++){
			$game_array=array(
				@"cn"=>$sport->Value[$i]->CN,
				@"sportid"=>$sportid,
				@"league"=>$sport->Value[$i]->L,
				@"league_id"=>$sport->Value[$i]->LI,
				@"game_id"=>@$sport->Value[$i]->CI,
				@"game_i_id"=>@$sport->Value[$i]->I,
				@"time"=>$sport->Value[$i]->S,
				@"team1"=>$sport->Value[$i]->O1,
				@"team2"=>$sport->Value[$i]->O2,
				@"bet"=>$sport->Value[$i]->E
			);
			array_push($jsport,$game_array);
		}

		return $jsport;

	}

	public function get_champ($sportid,$leagueid){

		$param_array=array(
			"lng"=>$this->LANGUAGE,
			"champ"=>$leagueid,
			"tf"=>"3000000",
			"afterDays"=>"0",
			"tz"=>"0",
			"sport"=>$sportid,
			"country"=>"1"
		);

		$sport=$this->HTTP_SGET($this->API.'GetChampZip',$param_array);
		//echo $sport;exit();
		$sport=json_decode($sport);

		if(!isset($sport->Value))
			return false;

		$jsport=array(
			"sport"=>array(
				"league"=>$sport->Value->L,
				"id"=>$sportid,
				"leagueid"=>$leagueid,
				"sport"=>$sport->Value->SE
			),
			"champ"=>array()
		);

		for($i=0;$i<=count($sport->Value->G)-1;$i++){
			array_push($jsport['champ'],array(
				"index"=>$sport->Value->G[$i]->I,
				"teams_name"=>array(
					@$sport->Value->G[$i]->O1,
					@$sport->Value->G[$i]->O2
				),
				"start_time"=>@$sport->Value->G[$i]->S,
				"location"=>@$sport->value->G[$i]->MIO->LOC,
				"value"=>array()
			));
			if($this->line_or_live==true){
				@$jsport['champ'][$i]['value']['result']['caption']=$sport->Value->G[$i]->SC->CPS;
				@$jsport['champ'][$i]['value']['result']['s1']=(INT)$sport->Value->G[$i]->SC->FS->S1;
				@$jsport['champ'][$i]['value']['result']['s2']=(INT)$sport->Value->G[$i]->SC->FS->S2;
			}
		}

		return $jsport;
	}
	public function get_game($gameid){
		//setting fro get odds
		$ok_odds=json_decode(file_get_contents(__dir__."/need/ok_odds.json"));

		$param_array=array(
			"id"=>$gameid,
			"lng"=>$this->LANGUAGE,
			"cfview"=>0,
			"isSubGames"=>"true",
			"GroupEvents"=>"true",
			"countevents"=>2000
		);

		$game=$this->HTTP_SGET($this->API.'GetGameZip',$param_array);
//echo $game;exit();
		$game=json_decode($game);

		if(!isset($game->Value))
			return false;

		$jgame=array(
			"game"=>array(
				"index"=>$gameid,
				"league"=>"null", //footbal ...
				"sport"=>"null",
				"status"=>"null",
				"video"=>"null",
				"location"=>"null",
				"team"=>array(), //name icon ...
				"time"=>array("start"=>0,"timer"=>0), //start time and if timer ts-st
				"half"=>array() //half caption,count,result{s1,s2,s3}
			),
			"odds"=>array(),
			"odds_array"=>array()
		);


		//check league enabled
		if(!in_array($game->Value->SI,$ok_odds->game_bet)){
			return "league_not_support";
		}

		//set en league
		if(isset($game->Value->LE)){
			$jgame['game']['league']=$game->Value->LE;
		}elseif(isset($game->Value->L)){  //or set en league / LE not found
			$jgame['game']['league']=$game->Value->L;
		}

		//set sport name
		if(isset($game->Value->SN)){
			$jgame['game']['sport']=$game->Value->SN;
		}

		//set status
		if(isset($game->Value->MIO->TSt)){
			$jgame['game']['status']=$game->Value->MIO->TSt;
		}

		//set video
		if(isset($game->Value->VI)){
			$jgame['game']['video']=$game->Value->VI;
		}

		// set start time
		if(isset($game->Value->S)){
			$jgame['game']['time']['start']=$game->Value->S;
		}

		//set timer
		if(isset($game->Value->SC->TS)){
			$jgame['game']['time']['timer']=$game->Value->SC->TS;
		}
		
		//set location
		if(isset($game->Value->MIO->Loc)){
			$jgame['game']['location']=$game->Value->MIO->Loc;
		}
		
		//set result status SC->ST
		if(isset($game->Value->SC->ST)){
			$jgame['game']['st']=$game->Value->SC->ST;
		}
		//set result status SC->S
		if(isset($game->Value->SC->S)){
			$jgame['game']['sc']=$game->Value->SC->S;
		}
		
		//set team 1
		if(isset($game->Value->O1)){
			$team1=array(
				"name"=>$game->Value->O1,
				"icon"=>"null"
			);
			//->icon
			if(isset($game->Value->O1I))
				$team1['icon']=$game->Value->O1I.".png";

			//set
			array_push($jgame['game']['team'],$team1);
		}

		//set team 2
		if(isset($game->Value->O2)){
			$team2=array(
				"name"=>$game->Value->O2,
				"icon"=>"null"
			);
			//->icon
			if(isset($game->Value->O2I))
				$team2['icon']=$game->Value->O2I.".png";

			//set
			array_push($jgame['game']['team'],$team2);
		}

		//set half caption
		if(isset($game->Value->TN)){
			$jgame['game']['half']['caption']=$game->Value->TN;
		}

		//set half count
		if(isset($game->Value->SC->CP)){
			$jgame['game']['half']['count']=$game->Value->SC->CP;
		}

		//set half result
		if(isset($game->Value->SC->FS)){
			$result=array("team1"=>0,"team2"=>0);
			if(isset($game->Value->SC->FS->S1))
				$result['team1']=$game->Value->SC->FS->S1;
			if(isset($game->Value->SC->FS->S2))
				$result['team2']=$game->Value->SC->FS->S2;
			$jgame['game']['half']['total']=$result;
		}

		//set half half_result
		if(isset($game->Value->SC->PS)){
			$half=array();
			for($i=0;$i<=count($game->Value->SC->PS)-1;$i++){
				$tmp_half=array("team1"=>0,"team2"=>0);
				if(isset($game->Value->SC->PS[$i]->Value->S1))
					$tmp_half['team1']=$game->Value->SC->PS[$i]->Value->S1;
				if(isset($game->Value->SC->PS[$i]->Value->S2))
					$tmp_half['team2']=$game->Value->SC->PS[$i]->Value->S2;
				array_push($half,$tmp_half);
			}
			$jgame['game']['half']['result']=$half;
		}


		/*	SET ODDS FOR GAME  */

		//set odds
		if(isset($game->Value->E)){
			$odds=array();
			for($i=0;$i<=count($game->Value->E)-1;$i++){
				$tmp_odds=array("t"=> -1,"c"=> -1);
				//check and isset odd model
				if(isset($game->Value->E[$i]->T)){
					//check odd perm
					if(in_array($game->Value->E[$i]->T,$ok_odds->odds)){
						$tmp_odds['t']=$game->Value->E[$i]->T;
						if(isset($game->Value->E[$i]->C))
							$tmp_odds['c']=$game->Value->E[$i]->C;
						if(isset($game->Value->E[$i]->B))
							$tmp_odds['b']=$game->Value->E[$i]->B;
						if(isset($game->Value->E[$i]->P))
							$tmp_odds['p']=$game->Value->E[$i]->P;
						if(isset($game->Value->E[$i]->G))
							$tmp_odds['g']=$game->Value->E[$i]->G;
						//set to tmp
						//array_push($odds,$tmp_odds);
					}
				}
			}
			//set
			$jgame['odds']=$odds;
		}
		if(isset($game->Value->GE)){
			$odd_array=array();
			for($i=0;$i<=count($game->Value->GE)-1;$i++){
				$tmp_i=array();
				for($b=0;$b<=count($game->Value->GE[$i]->E)-1;$b++){
					$tmp_b=array();
					for($r=0;$r<=count($game->Value->GE[$i]->E[$b])-1;$r++){
						$tmp_r=array();
						if(in_array($game->Value->GE[$i]->E[$b][$r]->T,$ok_odds->odds)){
							$tmp_r['t']=$game->Value->GE[$i]->E[$b][$r]->T;
							if(isset($game->Value->GE[$i]->E[$b][$r]->C))
								$tmp_r['c']=$game->Value->GE[$i]->E[$b][$r]->C;
							if(isset($game->Value->GE[$i]->E[$b][$r]->B))
								$tmp_r['b']=$game->Value->GE[$i]->E[$b][$r]->B;
							if(isset($game->Value->GE[$i]->E[$b][$r]->P))
								$tmp_r['p']=$game->Value->GE[$i]->E[$b][$r]->P;
							if(isset($game->Value->GE[$i]->E[$b][$r]->G))
								$tmp_r['g']=$game->Value->GE[$i]->E[$b][$r]->G;
							array_push($tmp_b,$tmp_r);
						}
					}
					if(count($tmp_b)>0){array_push($tmp_i,$tmp_b);}
				}
				if(count($tmp_i)>0){array_push($odd_array,$tmp_i);}
			}
			$jgame['odds_array']=$odd_array;
		}

		//end and return
		return $jgame;
	}

	public function get_income($sportid){
		//https://1xirsp87.com/LiveFeed/Get1x2_VZip?sports=4&count=50&lng=en&antisports=198&mode=4&country=75&partner=36&getEmpty=true
		$param_array=array(
			'sports'=>$sportid,
			'count'=>'1000',
			'lng'=>'fa',
			'mode'=>'4',
			'country'=>'1'
		);
		$game=$this->HTTP_SGET($this->API.'Get1x2_VZip',$param_array);
		$sport=json_decode($game);
		if(!isset($sport->Value))
			return false;
		
		$jsport=[];
		for($i=0;$i<=count($sport->Value)-1;$i++){
			$jsport[$i]=[
				@"cn"=>$sport->Value[$i]->CN,
				@"sportid"=>$sportid,
				@"league"=>$sport->Value[$i]->L,
				@"league_id"=>$sport->Value[$i]->LI,
				@"game_id"=>@$sport->Value[$i]->CI,
				@"game_i_id"=>@$sport->Value[$i]->I,
				@"time"=>@$sport->Value[$i]->S,
				@"team1"=>@$sport->Value[$i]->O1,
				@"team2"=>@$sport->Value[$i]->O2,
				@"result"=>[
					@'team1'=>@$sport->Value[$i]->SC->FS->S1,
					@'team2'=>@$sport->Value[$i]->SC->FS->S2
				],
				@"bet"=>$sport->Value[$i]->E
			];
		}
		return $jsport;
	}
	public function search_game($text){
		$param_array=array(
			"text"=>urlencode($text),
			"limit"=>50,
			"lng"=>$this->LANGUAGE
		);

		$sports=$this->HTTP_SGET($this->API.'Web_SearchZip',$param_array);
		$sport=json_decode($sports);
		if(!isset($sport->Value))
			return false;
		
		$jsport=array();
		for($i=0;$i<=count($sport->Value)-1;$i++){
			$jsport[$i]=array(
				@"cn"=>@$sport->Value[$i]->CN,
				@"sportid"=>@$sport->Value[$i]->SI,
				@"league"=>@$sport->Value[$i]->L,
				@"league_id"=>@$sport->Value[$i]->LI,
				@"game_id"=>@$sport->Value[$i]->CI,
				@"game_i_id"=>@$sport->Value[$i]->I,
				@"time"=>@$sport->Value[$i]->S,
				@"team1"=>@$sport->Value[$i]->O1,
				@"team2"=>@$sport->Value[$i]->O2
			);
		}
		return $jsport;

	}

	public function cinema(){
		$newip=@file_get_contents($this->DAPI.'/cinema');
		if($newip!=''){
			return $newip;
		}else{
			return "";
		}
	}

	public function get_video($ip,$id){
		$vdata=file_get_contents("http://".$ip."/hls-live/xmlive/_definst_/".$id."/".$id.".m3u8");
		$ts=substr($vdata,31,strpos($vdata,"#EXT-X-ALLOW-CACHE:NO")-31);

		$vurl=str_replace(array("\r","\n","\t"),"",$ip."/hls-live/streams/xmlive/events/_definst_/".$id."/".$id."Num".$ts.".ts");

		return $vurl;
	}
	
	public function get_logo($id){
		$logo=$this->HTTP_SGET('https://v2l.ccdnss.com/genfiles/logo_teams/'.$id,[]);	
		return $logo;
	}
	
	//result
	public function get_result($gameid){
		$url='';
		if($this->line_or_live==false){
			$url=$this->DAPI.'/StatisticFeed/StatByConstId3?id='.$gameid.'&ln=fa&cfview=0&uid=0';
		}else{
			$url=$this->DAPI.'/StatisticFeed/StatByGameId2?id='.$gameid.'&ln=fa&cfview=0&uid=0';
		}
		$res=json_decode($this->HTTP_SGET($url,[]));
		$result=[];
		
		//info
		//total
		if(isset($res->Score1)){
			$result['game']['total']['1']=$res->Score1;
		}
		if(isset($res->Score2)){
			$result['game']['total']['2']=$res->Score2;
		}
		//start time
		if(isset($res->DateStart)){
			$result['game']['start']=$res->DateStart;
		}
		//league
		if(isset($res->LLChampId)){
			$result['game']['league_id']=$res->LLChampId;
		}
		if(isset($res->StageTitle)){
			$result['game']['league']=$res->StageTitle;
		}
		//winer
		if(isset($res->Winner)){
			$result['game']['winner']['id']=$res->Winner;
			$result['game']['winner']['key']='team'.$res->Winner;
		}
		
		//team1
		if(isset($res->Team1)){
			if(isset($res->Team1->Id)){
				$result['team1']['id']=$res->Team1->Id;
			}
			if(isset($res->Team1->Image)){
				$result['team1']['img']=$res->Team1->Image;
			}
			if(isset($res->Team1->Title)){
				$result['team1']['title']=$res->Team1->Title;
			}
			if(isset($res->Team1->CountryId)){
				$result['team1']['country_id']=$res->Team1->CountryId;
			}
			if(isset($res->Team1->TeamId)){
				$result['team1']['team_id']=$res->Team1->TeamId;
			}
		}
		//team2
		if(isset($res->Team2)){
			if(isset($res->Team2->Id)){
				$result['team2']['id']=$res->Team2->Id;
			}
			if(isset($res->Team1->Image)){
				$result['team2']['img']=$res->Team2->Image;
			}
			if(isset($res->Team2->Title)){
				$result['team2']['title']=$res->Team2->Title;
			}
			if(isset($res->Team2->CountryId)){
				$result['team2']['country_id']=$res->Team2->CountryId;
			}
			if(isset($res->Team2->TeamId)){
				$result['team2']['team_id']=$res->Team2->TeamId;
			}
		}
		//half
		//score
		if(isset($res->Periods)){
			$pr=[];
			for($i=0;$i<=count($res->Periods)-1;$i++){
				$pr[$i]['half_type']=@$res->Periods[$i]->Type;
				$pr[$i]['h1']=@$res->Periods[$i]->Score1;
				$pr[$i]['h2']=@$res->Periods[$i]->Score2;
				if(isset($res->Periods[$i]->DateStart)){
					$pr[$i]['start_time']=$res->Periods[$i]->DateStart;
				}
			}
			$result['half']['score']=$pr;
		}
		
		//match
		if(isset($res->Events)){
			$em=[];
			for($i=0;$i<=count($res->Events)-1;$i++){
				$em[$i]['minute']=@$res->Events[$i]->Minute;
				$em[$i]['type']=@$res->Events[$i]->Type;
				$em[$i]['player']=@$res->Events[$i]->Player;
				$em[$i]['player_id']=@$res->Events[$i]->PlayerId;
				$em[$i]['team_id']=@$res->Events[$i]->TeamId;
				$em[$i]['assistant']=@$res->Events[$i]->Assistant;
				$em[$i]['assistant_id']=@$res->Events[$i]->AssistantId;
				$em[$i]['half_type']=@$res->Events[$i]->PeriodType;
			}
			$result['half']['event']=$em;
		}
		//half score
		if(isset($res->PeriodScoreDital)){
			$pd=[];
			for($i=0;$i<=count($res->PeriodScoreDital)-1;$i++){
				$pd[$i]['type']=@$res->PeriodScoreDital[$i]->PeriodType;
				$pd[$i]['team']=@$res->PeriodScoreDital[$i]->Data->Score1;
				$pd[$i]['team2']=@$res->PeriodScoreDital[$i]->Data->Score2;
				$pd[$i]['is_lost_serve']=@$res->PeriodScoreDital[$i]->Data->IsLostServe;
			}
			$result['score_detail']=$pd;
		}
		
		
		return $result;
	}

	//http 
	//or change to CURL
	function HTTP_SGET($url,$param){
			$str_param='';
			foreach($param as $key=>$value){
				$str_param.=$key.'='.$param[$key]."&";
			}
			$data=file_get_contents($url.'?'.substr($str_param,0,-1));
			return $data;
	}	
}
?>
