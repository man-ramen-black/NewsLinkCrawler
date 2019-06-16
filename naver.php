<?php
    error_reporting(false);
    ini_set('memory_limit','-1');
    require_once("./simple_html_dom/simple_html_dom.php");
    header("Content-Type: application/json; charset=UTF-8");
    
    foreach($_REQUEST as $key => $value){
	${$key} = $value;
    }
    
    $NEWS_CODE = array(
	//미디어오늘
	"media" => 1006,
	//연합뉴스
	"yna" => 1001,
	//오마이뉴스
	"ohmy" => 1047,
	//프레시안
	"pressian" => 1002,
	//데일리안
	"dailian" => 1119,
	//뉴데일리
	"newdaily" => 2005,
    );
    
    if(!$news_code){
	$news_code = "media";
    }
    
    if(!$page){
	$page = 1;
    }
    
    if(!$sdate){
	$sdate = date("Ymd", strtotime("-1 year"));
    }
    
    if(!$edate){
	$edate = date("Ymd");
    }
    
    $page = 1 + ($page-1) * 10;
    $sdate = date("Y.m.d", strtotime($sdate));
    $edate = date("Y.m.d", strtotime($edate));
    
    $url = "https://search.naver.com/search.naver?";
	
    $data = array(
	//뉴스
	"where" => "news",
	//?
	"sm" => "tab_pge",
	//??
	"pd" => 3,
	//마이뉴스
	"mynews" => 1,
	//시작일
	"ds" => $sdate,
	//종료일
	"de" => $edate,
	//검색어
	"query" => $query,
	//페이지
	"start" => $page,
    );
    
    $url .= http_build_query($data);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: news_office_checked=".$NEWS_CODE[$news_code]));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.2 (KHTML, like Gecko) Chrome/22.0.1216.0 Safari/537.2" );
    $response = curl_exec($ch);
    curl_close($ch);
    $temp = explode("<div class=\"news mynews section _prs_nws", $response);
    $response = "<div class=\"news mynews section _prs_nws". $temp[1];
    $html = str_get_html($response);
    $result = array();
    
    switch($switch){
	default:
	    //페이지 수 구하기
	    if($mode == "count"){
		if(!$html){
		    $result['count'] = 0;
		    break;
		}
		
		$countTextDom = $html -> find(".title_desc span", 0);
		if(!$countTextDom){
		    $result['count'] = 0;
		    break;
		}
		$countText = $countTextDom -> innertext;
		$temp = explode(" / ", $countText);
		$temp = explode("건", $temp[1]);
		$count = str_replace(",","",$temp[0]);
		$result['count'] = ceil($count/10);

	    //링크 추출
	    }else{
		if(!$html){
		    break;
		}
		
		if(!($titles = $html -> find(".type01 > li"))){
		    break;
		}
		
		foreach($titles as $title){
		    
		    //네이버 뉴스 링크로 링크 추출
		    if($naver_news){
			$temp = $title -> find("dd", 0);
			if(!$temp) continue;
			
		    }else{
			$temp = $title -> find("dt", 0);
			if(!$temp) continue;
		    }
		    
		    $temp = $temp -> find("a", 0);
		    if(!$temp) continue;
		    $link = $temp -> href;
		    
		    if(!$link || $link == "#"){
			continue;
		    } 
		    $result[] = $link;
		}
	    }

	    break;
    }
    
    echo json_encode($result);
?>