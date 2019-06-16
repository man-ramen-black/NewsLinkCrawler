<?php
    error_reporting(false);
    ini_set('memory_limit','-1');
    require_once("./simple_html_dom/simple_html_dom.php");
    header("Content-Type: application/json; charset=EUC-KR");
    
    foreach($_REQUEST as $key => $value){
	${$key} = $value;
    }
    
    $rowsPerPage = 300;
    
    if($mode == "count"){
	$rowsPerPage = 50;
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
    
    $sdate = date("Ymd", strtotime($sdate));
    $edate = date("Ymd", strtotime($edate));
    
    
    $url = "http://srchdb1.chosun.com/pdf/i_service/pdf_SearchList.jsp";
    
    $data = array(
	//검색어
	"FV" => iconv("UTF-8", "EUC-KR", $query),
	//제목
	"TI" => "TI",
	//본문
	"TX" => "TX",
	//주제
	"KW" => "KW",
	//발행일 위 ture, 아래 false
	"PD_TYPE" => false,
	//발행일 부터, 당일 등
	"PD_OP" => 1,
	//시작일
	"PD_F1" => $sdate,
	//종료일
	"PD_F2" => $edate,
	//목록보기 제목만 false
	"LIST_TYPE" => false,
	//출력건수
	"sRowsperPage" => $rowsPerPage,
	"currentPage" => $page,
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_ENCODING, 'EUC-KR');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_POST, true);
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
		
		$naviTxt = $html -> find(".navi_txt", 1);
		if(!$naviTxt){
		    $result['count'] = 0;
		    break;
		}
		$countText = $naviTxt -> outertext;
		$temp = explode("총 ", $countText);
		$temp = explode(" 건", $temp[1]);
		$count = $temp[0];
		$result['count'] = ceil($count/300);

	    //링크 추출
	    }else{
		if(!$html){
		    break;
		}
		
		if(!($titles = $html -> find(".list_tit"))){
		    break;
		}
		
		foreach($titles as $title){
		    if(!($link = $title -> find("a", 0) -> href)){
			continue;
		    } 
		    $result[] = "http://srchdb1.chosun.com".$link;
		}
	    }

	    break;
    }
    
    
    
    echo json_encode($result);
?>