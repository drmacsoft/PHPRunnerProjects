<?php
@ini_set("display_errors","1");
@ini_set("display_startup_errors","1");


require_once("include/dbcommon.php");


Security::processLogoutRequest();
if(!isLogged())
{
	HeaderRedirect("login");
	return;
}


if (($_SESSION["MyURL"] == "") || (!isLoggedAsGuest())) {
	Security::saveRedirectURL();
}




$layout = new TLayout("menu2", "RoundedOffice", "MobileOffice");
$layout->version = 2;
$layout->blocks["top"] = array();
$layout->containers["menu"] = array();
$layout->container_properties["menu"] = array(  );
$layout->containers["menu"][] = array("name"=>"login_menu",
	"block"=>"loggedas_block", "substyle"=>2  );

$layout->containers["menu"][] = array("name"=>"vmenu",
	"block"=>"menu_block", "substyle"=>1  );

$layout->skins["menu"] = "1";

$layout->blocks["top"][] = "menu";
$page_layouts["menu"] = $layout;

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



require_once('include/xtempl.php');
require_once(getabspath("classes/cipherer.php"));

include_once(getabspath("include/loanaccounts_events.php"));
$tableEvents["loanaccounts"] = new eventclass_loanaccounts;
include_once(getabspath("include/CashDeposit_events.php"));
$tableEvents["CashDeposit"] = new eventclass_CashDeposit;
include_once(getabspath("include/CashWithdrawl_events.php"));
$tableEvents["CashWithdrawl"] = new eventclass_CashWithdrawl;
include_once(getabspath("include/LoanCredit_events.php"));
$tableEvents["LoanCredit"] = new eventclass_LoanCredit;
include_once(getabspath("include/LoanPayment_events.php"));
$tableEvents["LoanPayment"] = new eventclass_LoanPayment;
include_once(getabspath("include/NEFTTransfer_events.php"));
$tableEvents["NEFTTransfer"] = new eventclass_NEFTTransfer;
include_once(getabspath("include/ClientCashWithdrawl_events.php"));
$tableEvents["ClientCashWithdrawl"] = new eventclass_ClientCashWithdrawl;
include_once(getabspath("include/ClientLoanCredit_events.php"));
$tableEvents["ClientLoanCredit"] = new eventclass_ClientLoanCredit;
include_once(getabspath("include/SavingsAccounts_events.php"));
$tableEvents["SavingsAccounts"] = new eventclass_SavingsAccounts;
include_once(getabspath("include/ClientAccounts_events.php"));
$tableEvents["ClientAccounts"] = new eventclass_ClientAccounts;

$xt = new Xtempl();

$id = postvalue("id")!=="" ? postvalue("id") : 1;

//array of params for classes
$params = array();
$params["id"] = $id; 
$params["xt"] = &$xt;
$params["tName"] = NOT_TABLE_BASED_TNAME;
$params["pageType"] = PAGE_MENU;
$params["templatefile"] = "menu.htm";
$params["isGroupSecurity"] = $isGroupSecurity;
$params["needSearchClauseObj"] = false;
$pageObject = new RunnerPage($params);
$pageObject->init();
$pageObject->commonAssign();
// button handlers file names

//	Before Process event
if($globalEvents->exists("BeforeProcessMenu"))
	$globalEvents->BeforeProcessMenu( $pageObject );

$pageObject->body["begin"] .= GetBaseScriptsForPage(false);

$pageObject->addCommonJs();

//fill jsSettings and ControlsHTMLMap
$pageObject->fillSetCntrlMaps();
$pageObject->setLangParams();
$pageObject->body['end'] .= '<script>';
$pageObject->body['end'] .= "window.controlsMap = ".my_json_encode($pageObject->controlsHTMLMap).";";
$pageObject->body['end'] .= "window.viewControlsMap = ".my_json_encode($pageObject->viewControlsHTMLMap).";";
$pageObject->body['end'] .= "window.settings = ".my_json_encode($pageObject->jsSettings).";</script>";
$pageObject->body["end"] .= "<script type=\"text/javascript\" src=\"".GetRootPathForResources("include/runnerJS/RunnerAll.js")."\"></script>";
$pageObject->body["end"] .= '<script>'.$pageObject->PrepareJS()."</script>";
$xt->assignbyref("body",$pageObject->body);
$xt->assign("id", $id);
// The user might rewrite $_SESSION["UserName"] value with HTML code in an event, so no encoding will be performed while printing this value.
$xt->assign("username", $_SESSION["UserName"]);
$xt->assign("changepwd_link",$_SESSION["AccessLevel"] != ACCESS_LEVEL_GUEST && $_SESSION["fromFacebook"] == false);
$xt->assign("changepwdlink_attrs","onclick=\"window.location.href='".GetTableLink("changepwd")."';return false;\"");

$xt->assign("logoutlink_attrs", 'id="logoutButton'.$id.'"');
$xt->assign("guestloginlink_attrs", 'id="loginButton'.$id.'"');

$xt->assign("loggedas_block", !isLoggedAsGuest());
$xt->assign("loggedas_message", !isLoggedAsGuest());

$xt->assign("logout_link", true);
$xt->assign("guestloginbutton", isLoggedAsGuest());
$xt->assign("logoutbutton", isSingleSign() && !isLoggedAsGuest());

if( IsAdmin() )
	$xt->assign("adminarea_link", true);

// get redirect location for menu page
$redirect = $pageObject->getRedirectForMenuPage();
if($redirect)
{
	header("Location: ".$redirect); 
	exit();
}

$xt->assign("menu_block",true);
if($globalEvents->exists("BeforeShowMenu"))
	$globalEvents->BeforeShowMenu($xt, $pageObject->templatefile, $pageObject);

$pageObject->display($pageObject->templatefile);
?>