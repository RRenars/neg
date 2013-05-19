<?php
require 'smarty/libs/Smarty.class.php';
$smarty = new Smarty();
require('functions.php');
session_start() or die ('Neizdevās');

$itemLimit = 20;
if (isset($_GET['pagesFirst']))
{
	$startItem = $_GET['pagesFirst'];
}
else
{
	$startItem = 0;
}
$main = "";
if (is_authorized())
{
	$main_container = profile_box();
	$main = "";
	$show_private = "";
	$main = $main . "<div class='separator_line'></div>";	
}
else {
	header("Location: login.php");
}

$mysql = connect1();
$dbname = connect2();
mysql_select_db($dbname) or die( "Unable to select database");
$sql = "SELECT * FROM items WHERE status!='DELETED' ". $show_private ." AND type='pearl' ORDER BY `itemID` DESC LIMIT $startItem, $itemLimit ";
$results = Array(); 
$result = mysql_query($sql, $mysql); 
$num=mysql_numrows($result);
$i=0;

while ($i < $num) {
	//$mysql2 = mysql_connect('localhost','rrenars', '32426185') or die('Could not connect: '.mysql_error());
	$id = mysql_result($result,$i, "author");
	$sql2 = "SELECT * FROM users WHERE id=$id";
	$results2 = Array();
	$result2 = mysql_query($sql2, $mysql); 	
	
	
	$author=mysql_result($result2, 0, "username");//mysql_result($result,$i,"author");
	$itemID = mysql_result($result, $i, "itemID");
	$titleID = mysql_result($result,$i, "title");
	$sql3 = "SELECT * FROM users WHERE id=$titleID";
	$result3 = mysql_query($sql3, $mysql); 
	if ($titleID == 2)
		$title = "Cits...";
	else
		$title = mysql_result($result3, 0, "username"); // iegūstam pērles autora username
	$content=mysql_result($result,$i,"content");
	$type = mysql_result($result,$i,"type");
	//$description = mysql_result($result, $i, "description");
	
	$commentSql = "SELECT Count(*) as Skaits FROM comments WHERE `itemID` = $itemID";
	$commentResult = mysql_query($commentSql, $mysql); 
	$commentCount = mysql_result($commentResult, 0, "Skaits");
	
	$main = $main . "<p class='pearl_author'>Pievienoja: <i>$author</i><br>";
	$main = $main . "<h3 class='pearl_title'>Pērles radītājs: $title</h3>";
	$main = $main . "<div class='pearl_description'><p>$content</p></div>";
	if ($author == $_SESSION['username'])
	{
		$main = $main . "<form class='edit_button' METHOD=POST action='edit_item.php'><input type='hidden' name='itemID' value='$itemID'><input type='submit' value='Rediģēt' /></form>";
		$main = $main . "<form class='pearl_button' METHOD=POST action='delete_item.php'><input type='hidden' name='itemID' value='$itemID'><input type='submit' value='Dzēst' /></form>";
	}
	$main = $main . "<a href='open.php?id=$itemID' class='comment'>Komentēt ($commentCount) </a>";
	$main = $main . "<div class='separator_line'></div>";
	$i++;
	

};

$G = $startItem - $itemLimit;
if (($G>0)&&($startItem!=0))
{
	$main = $main . "<a href='index.php?pagesFirst=$G' >Atpakaļ</a>";
}
else 
{
	if (($G<0)&&($startItem!=0))
	{
			$G=0;
	}        
}

$G = $startItem + $itemLimit;

	
$sql = "SELECT * FROM users";
//$results2 = Array();
$userList = mysql_query($sql, $mysql); 		
//$author=mysql_result($userList, 0, "username");//mysql_result($result,$i,"author");
$num=mysql_numrows($userList);
$i = 0;
$main = $main . 
"<form action='item_added.php' id='iteminput' method='post'>
<input type='hidden' name='type' value='pearl' />
	<table>
	<tr>
		<td>Pērles radītājs: </td><td>
			<select id='title' name='title'>";
			while ($i < $num)
			{
				$userID=mysql_result($userList, $i, "ID");
				$author=mysql_result($userList, $i, "username");
				if ($userID != 2)
					$main = $main . "<option value='$userID'>$author</option>";
				else
					$main = $main . "<option value='$userID' selected='selected'>Cits...</option>";
				$i++;
			}		
	$main = $main .	"</select>
		</td>
	</tr>
	<tr>
		<td>Pērle: </td><td><textarea id='content' name='content' rows=10 cols=72></textarea></td>
	</tr>
	<tr>
		<td><input type='submit' name='submit' value='Apstiptināt' /></td>
	</tr>
	</table>
</form>";

$main = $main . "<script type='text/javascript'>
	CKEDITOR.replace( 'content',
	{
		toolbar : 
		[
			{ name: 'basicstyles',	items : 
			[ 'Bold','Italic','Underline','Strike','Subscript','Superscript'] },
			{ name: 'clipboard',	items : [ 'Undo','Redo' ] },
			{ name: 'styles',		items : [ 'Font','FontSize' ] },
			{ name: 'paragraph',	items : [ 'Outdent','Indent','-','Blockquote','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
			{ name: 'links',		items : [ 'Link','Unlink' ] },
			{ name: 'insert',		items : [ 'Image','SpecialChar' ] }
		]
	} );
</script>";

$smarty->assign('main',$main);
$smarty->assign('main_container',$main_container);
$smarty->display('index.tpl');
?>