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
    
    $page = 1 + ($page-1) * 15;
    $sdate = date("Ymd", strtotime($sdate));
    $edate = date("Ymd", strtotime($edate));
    
    $url = "http://www.donga.com/news/search?";
	
    $data = array(
	//뉴스 동아일보
	"check_news" => 1,
	//날짜 직접입력
	"search_date" => 5,
	//더보기
	"more" => 1,
	//시작일
	"v1" => $sdate,
	//종료일
	"v2" => $edate,
	//검색어
	"query" => $query,
	//페이지
	"p" => $page,
    );
    
    $url .= http_build_query($data);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36" );
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
		
		$countTextDom = $html -> find(".searchCont h2 span", 0);
		if(!$countTextDom){
		    $result['count'] = 0;
		    break;
		}
		$countText = $countTextDom -> outertext;
		$temp = explode("총 ", $countText);
		$temp = explode(" 건", $temp[1]);
		$count = $temp[0];
		$result['count'] = ceil($count/15);

	    //링크 추출
	    }else{
		if(!$html){
		    break;
		}
		
		if(!($titles = $html -> find(".searchCont .tit"))){
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