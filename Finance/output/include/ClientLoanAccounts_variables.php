<?php
$strTableName="ClientLoanAccounts";
$_SESSION["OwnerID"] = $_SESSION["_".$strTableName."_OwnerID"];

$strOriginalTableName="accounts";

$gstrOrderBy="ORDER BY id DESC";
if(strlen($gstrOrderBy) && strtolower(substr($gstrOrderBy,0,8))!="order by")
	$gstrOrderBy="order by ".$gstrOrderBy;

// alias for 'SQLQuery' object
$gSettings = new ProjectSettings("ClientLoanAccounts");
$gQuery = $gSettings->getSQLQuery();
$eventObj = &$tableEvents["ClientLoanAccounts"];

$reportCaseSensitiveGroupFields = false;

$gstrSQL = $gQuery->gSQLWhere("");

?>