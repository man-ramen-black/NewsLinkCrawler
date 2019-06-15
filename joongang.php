<?php
    error_reporting(false);
    ini_set('memory_limit','-1');
    require_once("./simple_html_dom/simple_html_dom.php");
    header("Content-Type: application/json; charset=UTF-8");
    
    foreach($_REQUEST as $key => $value){
	${$key} = $value;
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
    
    $sdate = date("m/d/Y 00:00:00", strtotime($sdate));
    $edate = date("m/d/Y 00:00:00", strtotime($edate));
    
    $url = "https://search.joins.com/TotalNews?";
	
    $data = array(
	//뉴스 동아일보
	"SourceGroupType" => "Joongang",
	//날짜 직접입력
	"PeriodType" => "DirectInput",
	//검색범위 전체 뉴스
	"SearchCategoryType" => "TotalNews",
	//시작일
	"StartSearchDate" => $sdate,
	//종료일
	"EndSearchDate" => $edate,
	//검색어
	"Keyword" => $text,
	//페이지
	"page" => $page,
    );
    
    $url .= http_build_query($data);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.2 (KHTML, like Gecko) Chrome/22.0.1216.0 Safari/537.2" );
    $response = curl_exec($ch);
    curl_close($ch);
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
		
		$countTextDom = $html -> find(".total_number", 0);
		if(!$countTextDom){
		    $result['count'] = 0;
		    break;
		}
		$countText = $countTextDom -> outertext;
		$temp = explode("-", $countText);
		$temp = explode(" ", $temp[1]);
		$count = $temp[0];
		$result['count'] = $count;

	    //링크 추출
	    }else{
		if(!$html){
		    break;
		}
		
		if(!($titles = $html -> find(".headline"))){
		    break;
		}
		
		foreach($titles as $title){
		    if(!($link = $title -> find("a", 0) -> href)){
			continue;
		    } 
		    $result[] = $link;
		}
	    }

	    break;
    }
    
    echo json_encode($result);
?>