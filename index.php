<!DOCTYPE html>
<html>
    <head>
	
        <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densitydpi=medium-dpi" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	
        <title>뉴스 링크 크롤러</title>
	
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/earlyaccess/notosanskr.css">
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" type="text/css" /> 
	
	<style>
	    * { -webkit-tap-highlight-color: rgba(0, 0, 0, 0);outline:0;margin:0; padding:0; }
	    html, body {margin: 0;}
	    body {font-family: 'Noto Sans KR', 'Apple SD Gothic Neo','맑은 고딕','Malgun Gothic','돋움',Dotum,'굴림',Gulim,sans-serif;}
	    a {color:blue}
	    
	    h1 {margin-bottom:10px;}
	    h2 {margin-bottom:5px;}
	    input {padding:4px 6px;}
	    select {padding:3px 4px 4px 4px;font-size:13px;}
	    ul, ol, li{list-style: none}
	    
	    button {background: linear-gradient(to top, #fafafa 0%, #fff 100%);color: #666!important;border: 1px solid #ebebeb;border-bottom-color: #c6c6c6;box-shadow: 0 2px 2px rgba(0, 0, 0, 0.04);cursor:pointer;padding:0.4em 0.8em;font-weight:600;border-radius:.25em}
	    button:hover {background: linear-gradient(to bottom, #fafafa 0%, #fff 100%);}
	    button:active {text-shadow: 0 1px 0px #fff;border-color: #ebebeb;border-top-color: #ddd;background: #f4f4f4;box-shadow: none}
	    
	    button.big {font-size:1.2em;}
	    button.full_height {height:100%;}
	    
	    table {border-spacing: 0; border-collapse: collapse;}
	    th, td {border:solid 1px #ccc;padding:3px 8px;}
	    th {background:#eee}
	    
	    #wrap {padding:20px 30px;}
	    
	    .main_view {margin-bottom:10px;}
	    
	    .progress_view {display:none;}
	    .progress_text {position:absolute;left:0;right:0;text-align:center;color:#333}
	    .progress_bar {border-radius:5px;background:#F5F5F5;border:solid 1px #ccc;height:25px}
	    .progress_bar li {height:100%;display:inline-block}
	    .progress_bar li:first-child{border-top-left-radius:5px;border-bottom-left-radius:5px;}
	    .progress_bar li:last-child{border-top-right-radius:5px;border-bottom-right-radius:5px;}
	    .progress_bar li.good {background:#5EB663}
	    .progress_bar li.bad {background:#D75553}
	    
	    .result_view {clear:both;display:none;}
	    
	    .links {width:100%;}
	    .links li {border:solid 1px gray;border-bottom:0;padding:2px 5px;}
	    .links li:last-child {border-bottom:solid 1px gray;}
	    
	    textarea {border:solid 1px gray;width:100%;height:400px;}
	    .invisible {position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;}
	</style>
	
	<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script> 
	<script>
	    $(function(){
		$(".date").datepicker({
		    dateFormat : 'yy-mm-dd',
		    changeYear: true,
		    changeMonth : true
		});
	    });
	    
	    var links = [];
	    var loadingCount = 0;
	    
	    function submitForm(){
		$("#submit_btn").hide();
		$("#loading").show();
		$("#result").val("");
		$("#result_container").html("");
		$(".progress_view").hide();
		$(".result_view").hide();
		
		var data = "mode=count&";
		data += $("#form").serialize();		
		executeAjax({
		    data : data,
		    success : function(result){
			if(!result.count){
			    $("#submit_btn").show();
			    $("#loading").hide();
			    alert("검색 결과가 없습니다.");
			    return;
			}
			getLinkSplit(result.count);
		    },
		    error : function(){
			$("#submit_btn").show();
			$("#loading").hide();
		    }
		});
	    }
	    
	    function getLinkSplit(count){
		$(".progress_view").show();
		loadingCount = count;
		links = new Array(count);
		$(".progress_bar").text("");
		$(".progress_text").text("0/"+count);
		for(var i = 0 ; i < count ; i++){
		    getLink(i+1);
		}
	    }
	    
	    function getLink(page){
		var data = "page="+page+"&";
		data += $("#form").serialize();		
		executeAjax({
		  data: data,
		  success : function(result){
			$(".progress_bar").append("<li style='width:"+(1/links.length*100)+"%' class='good'></li>");
			$(".progress_text").text(($(".progress_text").text().split("/")[0]*1+1)+"/"+links.length);
			loadingCount--;
			links[page-1] = result;
			if(loadingCount < 1){
			    setTimeout(onCompleteGetLink, 1000);
			}
		    },
		    error : function(){
			$(".progress_bar").append("<li style='width:"+(1/links.length*100)+"%' class='bad'></li>");
		    }
		});
	    }
	    
	    function onCompleteGetLink(){
		$(".progress_view").hide();
		$(".result_view").show();
		
		var linkText = "";
		var html = "";
		for(var i = 0 ; i < links.length; i++){
		    if(!links[i]){
			continue;
		    }
		    for(var j = 0 ; j < links[i].length; j++){
			linkText += links[i][j]+"\n";
			html += "<li><a target='_blank' href='"+links[i][j]+"'>"+links[i][j]+"</a></li>";
		    }
		}
		$("#result_container").append(html);
		$("#result").val(linkText);
		$("#submit_btn").show();
		$("#loading").hide();
		window.scrollTo(0,0);
		alert("조회가 완료되었습니다.\n\n※조회된 내용이 많은 경우 페이지 표시가 다소 늦어질 수 있습니다.");
	    }
	    
	    function executeAjax(data){
		executeAjax(data, 1);
	    }
	    
	    function executeAjax(data, tryCount){
		if(data.progress){
		    $(data.progress).show();
		}
		console.log(data);
		$.ajax({
		    url: $("#news").val()+".php",
		    type: "POST",
		    dataType: 'json',
		    data: data.data,
		    success: function(result){
			console.log(result);
			if(data.progress){
			    $(data.progress).hide();
			}
			
			if(!result){
			    if(tryCount < 5){    
			       executeAjax(data, ++tryCount);
			    }else{
				alert("정보를 불러오는데 실패했습니다.\n잠시 후에 다시 시도해주세요.");	
			    }
			    return;
			}
			if(data.success){
			    data.success(result);    
			}
		    },
		    error: function(request,status,error){
			if(data.progress){
			    $(data.progress).hide();
			}
			
			if(tryCount < 5){
			    executeAjax(data, callback, progress, ++tryCount);    
			}else{
			    alert("오류가 발생했습니다.\n\nError["+request.status+"] : " + error+"\n(Response : "+request.responseText+")");
			    if(data.error){
				data.error();	
			    }
			}
		    }
		});
	    }
	    
	    function copy(selector){
		try{
		    document.querySelector(selector).select();
		    document.execCommand('copy');
		    alert("복사되었습니다.");
		}catch(e){
		    alert($(selector).val());
		}
	    }
	    
	    function toggleResult(btn){
		if($(btn).text() === "직접 복사"){
		    $(btn).text("링크 보기");
		    $("#result_container").hide();
		    $("#result").removeClass("invisible");
		    
		}else{
		    $(btn).text("직접 복사");
		    $("#result_container").show();
		    $("#result").addClass("invisible");
		}
	    }
	    
	    function onEnter(func){
		if(event.keyCode == 13){
		    func();
		}
	    }
	    
	</script>
    </head>
    <body>
	<div id="wrap">
	    <div class="main_view">
		<h1>뉴스 링크 크롤러</h1>
		<form id="form">
		    <table>
			<tr>
			    <th>신문사</th>
			    <td>
				<select id="news">
				    <option value="chosun" data-url="http://srchdb1.chosun.com/pdf/i_service/pdf_SearchList.jsp">조선일보</option>
				    <option value="donga" data-url="http://news.donga.com/search">동아일보</option>
				    <option value="joongang" data-url="http://bitly.kr/jM3LKL">중앙일보</option>
				</select>
				<button type="button" onclick="window.open($('#news option:selected').attr('data-url'))">바로가기</button>
			    </td>
			    <td rowspan='99'>
				<img id="loading" src="./images/loading.gif?ver=<?php echo time();?>" style="display:none;width:82px">
				<button type="button" id="submit_btn" class="big full_height" onclick="submitForm()">조회</button>
			    </td>
			</tr>
			<tr>
			    <th>검색어</th>
			    <td><input type="text" name="query" placeholder="검색어를 입력해주세요." onkeydown="onEnter(submitForm)"></td>
			</tr>
			<tr>
			    <th>발행일</th>
			    <td>
				<input type="text" class="date" name="sdate" size="6" value="<?php echo date("Y-m-d", strtotime("-1 year"))?>" onkeydown="onEnter(submitForm)"> 
				~ <input type="text" class="date" name="edate" size="6" value="<?php echo date("Y-m-d")?>" onkeydown="onEnter(submitForm)">
			    </td>
			</tr>
		    </table>
		</form>
	    </div>
	    
	    <div class="progress_view">
		<h2>진행 상황</h2>
		<div class='progress_text'></div>
		<ol class="progress_bar" ></ol>
	    </div>
	    
	    <div class="result_view">
		<h2>결과</h2>
		<div style="margin-bottom:10px;">
		    <button type="button" onclick="copy('#result');">전체 복사</button>
		    <button type="button" onclick="toggleResult(this)">링크 보기</button>
		</div>
		<ul id="result_container" class="links" style="display:none"></ul>
		<textarea id="result" readonly></textarea>
	    </div>
	</div>
    </body>
</html>
