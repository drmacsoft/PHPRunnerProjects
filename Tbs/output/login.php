<?php
@ini_set("display_errors","1");
@ini_set("display_startup_errors","1");

include("include/dbcommon.php");
include('include/xtempl.php');
require_once(getabspath("classes/cipherer.php"));
include('classes/loginpage.php');
add_nocache_headers();

$cipherer = new RunnerCipherer( NOT_TABLE_BASED_TNAME );




$layout = new TLayout("login2", "RoundedWhite_label", "MobileWhite_label");
$layout->version = 2;
$layout->blocks["top"] = array();
$layout->containers["login"] = array();
$layout->containers["login"][] = array("name"=>"loginheader", 
	"block"=>"loginheader", "substyle"=>2  );

$layout->containers["login"][] = array("name"=>"message", 
	"block"=>"message_block", "substyle"=>1  );

$layout->containers["login"][] = array("name"=>"wrapper", 
	"block"=>"", "substyle"=>1 , "container"=>"fields" );
$layout->containers["fields"] = array();
$layout->containers["fields"][] = array("name"=>"loginfields", 
	"block"=>"", "substyle"=>1  );

$layout->containers["fields"][] = array("name"=>"mesguest", 
	"block"=>"guestlink_block", "substyle"=>3  );

$layout->containers["fields"][] = array("name"=>"mesreg", 
	"block"=>"", "substyle"=>3  );

$layout->containers["fields"][] = array("name"=>"mesforgot", 
	"block"=>"", "substyle"=>3  );

$layout->containers["fields"][] = array("name"=>"loginbuttons", 
	"block"=>"loginbuttons", "substyle"=>2  );

$layout->skins["fields"] = "fields";


$layout->skins["login"] = "1";

$layout->blocks["top"][] = "login";
$page_layouts["login"] = $layout;

$layout->skinsparams = array();
$layout->skinsparams["empty"] = array("button"=>"button2");
$layout->skinsparams["menu"] = array("button"=>"button1");
$layout->skinsparams["hmenu"] = array("button"=>"button1");
$layout->skinsparams["undermenu"] = array("button"=>"button1");
$layout->skinsparams["fields"] = array("button"=>"button1");
$layout->skinsparams["form"] = array("button"=>"button1");
$layout->skinsparams["1"] = array("button"=>"button1");
$layout->skinsparams["2"] = array("button"=>"button1");
$layout->skinsparams["3"] = array("button"=>"button1");



$xt = new Xtempl();

$id = postvalue("id") != "" ? postvalue("id") : 1;
$onFly = postvalue("onFly");
if($onFly == 2) 
    $id = 1;


//array of params for classes
$params = array("id" =>$id, "pageType" => PAGE_LOGIN);
$params['xt'] = &$xt;
$params["tName"]= NOT_TABLE_BASED_TNAME;
$params["templatefile"] = "login.htm";
$params['needSearchClauseObj'] = false;
 
	$pageObject = new LoginPage($params); 
$pageObject->init();



$adSubmit = false;


// begin proccess captcha
$pageObject->isCaptchaOk = 1;
$useCaptcha = false;

// end proccess captcha


//	Before Process event


if($globalEvents->exists("BeforeProcessLogin"))
	$globalEvents->BeforeProcessLogin($conn, $pageObject);

$myurl = @$_SESSION["MyURL"];
unset($_SESSION["MyURL"]);


if(postvalue("a")=="logout")
{
	$pageObject->LogoutAndRedirect(GetTableLink("login"));
}

$pageObject->fromFacebook = false;
$_SESSION["fromFacebook"] = false;

$message="";
if (!isset($pUsername)){
$pUsername = postvalue("username");
$pDisplayUsername = '';
$pPassword = postvalue("password");
}
$is508 = isEnableSection508();

$rememberbox_checked = "";
$rememberbox_attrs = ($is508==true ? "id=\"remember_password\" " : "")."name=\"remember_password\" value=\"1\"";
if(@$_COOKIE["username"] || @$_COOKIE["password"])
	$rememberbox_checked = " checked";

$logacc = true;
if($pageObject->auditObj)
{
	if($pageObject->auditObj->LoginAccess())
	{
		$logacc = false;
		$message = mysprintf("Access denied for %s minutes",array($pageObject->auditObj->LoginAccess()));
	}
}

if ((@$_POST["btnSubmit"] == "Login" || $adSubmit) && $logacc)
{

	if(@$_POST["remember_password"] == 1)
	{
		setcookie("username",$pUsername,time()+365*1440*60);
		setcookie("password",$pPassword,time()+365*1440*60);
		$rememberbox_checked=" checked";
	}
	else
	{
		setcookie("username","",time()-365*1440*60);
		setcookie("password","",time()-365*1440*60);
		$rememberbox_checked="";
	}
	
	if($pageObject->isCaptchaOk)
		$_SESSION["login_count_captcha"] = $_SESSION["login_count_captcha"]+1;
		
	$retval = true;
	$message = "";
	//run before login event
	if($globalEvents->exists("BeforeLogin"))
		$retval = $globalEvents->BeforeLogin($pUsername,$pPassword,$message, $pageObject);
		if ($retval)
		{
					$d = $pageObject->LogIn($pUsername,$pPassword);
		if ($d) {
			//login succeccful
			//run AfterSuccessfulLogin event
			// if login method is not AD then ASL event fires in SetAuthSessionData
						if ($onFly == 2) {
				if($myurl) {
					$myurl .= strpos($myurl, '?') !== FALSE ?  '&a=login' : '?a=login';
					$ajaxmessage = $myurl;
				} else {
					$ajaxmessage = GetTableLink("menu");
				}
			} else {
				if($myurl) {
					header("Location: ".$myurl);
				} else {
					HeaderRedirect("menu");
				}
				return;
			}
		}
		else
		{
			//invalide login
			if($globalEvents->exists("AfterUnsuccessfulLogin"))
				$globalEvents->AfterUnsuccessfulLogin($pUsername,$pPassword,$message, $pageObject);
			
			if ($pageObject->message!=""){
				$message = $pageObject->message;
			}
			
			if($message=="")
				$message = "Invalid Login";
		}
	}
	else {
		//invalide login
		if($globalEvents->exists("AfterUnsuccessfulLogin"))
			$globalEvents->AfterUnsuccessfulLogin($pUsername,$pPassword,$message, $pageObject);
		
		if ($pageObject->message!=""){
			$message = $pageObject->message;
		}
		
		if($message=="")
			$message = "Invalid Login";
	}
}


if ($onFly) {
	$xt->assign("loginlink_attrs","id=submitLogin");
} else {
	$xt->assign("loginlink_attrs","onclick=\"document.forms[0].submit();return false;\"");
}

$xt->assign("rememberbox_attrs",$rememberbox_attrs.$rememberbox_checked);


$xt->assign("guestlink_block", isGuestLoginAvailable());

	
$_SESSION["MyURL"] = $myurl;
if($myurl)
	$xt->assign("guestlink_attrs","href=\"".$myurl."\"");
else
	$xt->assign("guestlink_attrs","href=\"".GetTableLink("menu")."\"");
	
if(postvalue("username"))
	$xt->assign("username_attrs",($is508==true ? "id=\"username\" " : "")."value=\"".runner_htmlspecialchars($pUsername)."\"");
else
	$xt->assign("username_attrs",($is508==true ? "id=\"username\" " : "")."value=\"".runner_htmlspecialchars(refine(@$_COOKIE["username"]))."\"");

if ($onFly)
	$password_attrs="onkeydown=\"e=event; if(!e) e = window.event; if (e.keyCode != 13) return; $('a[id=submitLogin]').trigger('click'); return false;\"";
else
	$password_attrs="onkeydown=\"e=event; if(!e) e = window.event; if (e.keyCode != 13) return; document.forms[0].submit(); return false;\"";

if(postvalue("password"))
	$password_attrs.=($is508==true ? " id=\"password\"": "")." value=\"".runner_htmlspecialchars($pPassword)."\"";
else
	$password_attrs.=($is508==true ? " id=\"password\"": "")." value=\"".runner_htmlspecialchars(refine(@$_COOKIE["password"]))."\"";
$xt->assign("password_attrs",$password_attrs);

if(@$_GET["message"]=="expired")
	$message = "Your session has expired." . "Please login again.";

if(@$_GET["message"]=="invalidlogin")
	$message = "Invalid Login";
	
if($message)
{
	$xt->assign("message_block",true);
	$xt->assign("message","<div class='message rnr-error'>".$message."</div>");
}

$pageObject->body["begin"] .= GetBaseScriptsForPage(false);

$pageObject->body["begin"] .= "<form method=post action='".GetTableLink("login")."' id=form1 name=form1>
		<input type=hidden name=btnSubmit value=\"Login\">";
		
$pageObject->body["end"] .= "</form>
<script>
function elementVisible(jselement)
{ 
	do
	{
		if (jselement.style.display.toUpperCase() == 'NONE')
			return false;
		jselement=jselement.parentNode; 
	}
	while (jselement.tagName.toUpperCase() != 'BODY'); 
	return true;
}
if(elementVisible(document.forms[0].elements['username']))
	document.forms[0].elements['username'].focus();
</script>";

$pageObject->addCommonJs();

// button handlers file names
//fill jsSettings and ControlsHTMLMap
$pageObject->fillSetCntrlMaps();
$pageObject->body['end'] .= '<script>';
$pageObject->body['end'] .= "window.controlsMap = ".my_json_encode($pageObject->controlsHTMLMap).";";
$pageObject->body['end'] .= "window.viewControlsMap = ".my_json_encode($pageObject->viewControlsHTMLMap).";";
$pageObject->body['end'] .= "window.settings = ".my_json_encode($pageObject->jsSettings).";</script>";
$pageObject->body["end"] .= "<script type=\"text/javascript\" src=\"".GetRootPathForResources("include/runnerJS/RunnerAll.js")."\"></script>";
$pageObject->body["end"] .= '<script>'.$pageObject->PrepareJS()."</script>";
$pageObject->addButtonHandlers();

$xt->assignbyref("body",$pageObject->body);

$xt->assign("username_label",true);
$xt->assign("password_label",true);
$xt->assign("remember_password_label",true);
if(isEnableSection508())
{
	$xt->assign_section("username_label","<label for=\"username\">","</label>");
	$xt->assign_section("password_label","<label for=\"password\">","</label>");
	$xt->assign_section("remember_password_label","<label for=\"remember_password\">","</label>");
}

if( !$onFly )
	$pageObject->assignFormFooterAndHeaderBricks( true );	

if($globalEvents->exists("BeforeShowLogin"))
	$globalEvents->BeforeShowLogin($xt,$pageObject->templatefile, $pageObject);
	
// submit on popup login page
if ($onFly == 2)
{
	if (!$d) { // if not login
		$returnJSON['message'] = "Invalid Login";
	} else {
		$returnJSON['redirect'] = $ajaxmessage;
	}
	if (($pageObject->isCaptchaOk == 0) && ($useCaptcha)) {
		$returnJSON['message'] = "Invalid security code.";
	}
	echo printJSON($returnJSON);
	exit();
}

// load popup login page
if ($onFly == 1)
{
	if (postvalue("notRedirect")) 
	{
		$pageObject->body["begin"] .= "<input type=hidden id='notRedirect' value=1>";
		$xt->assign("continuebutton_attrs",'href="#" style="display:none" id="continueButton"');
		$xt->assign("continue_button",true);
	}
	$xt->assign("message_block",true);
	$xt->assign("message","<div id='login_message' class='message rnr-error'></div>");
	$xt->displayBrickHidden("message");
	$xt->assign("id",$id);
	$xt->assign("footer",false);
	$xt->assign("header",false);
	$xt->assign("body",$pageObject->body);	
	$xt->assign("style_block",true);
	$xt->assign("guestlink_block", false);
	$xt->assign("registerlink_attrs",'name="RegisterPage" data-table="'.$cLoginTable.'"');
	$xt->assign("forgotpasswordlink_attrs",'name="ForgotPasswordPage"');

	$pageObject->displayAJAX($pageObject->templatefile, $id+1);
	exit();
}
		
$pageObject->display($pageObject->templatefile);
?>