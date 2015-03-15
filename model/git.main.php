<?php # Git model for controller/git.php->main()

## Get list of remotes
$lines = file($this->path.'/.git/config');
$remotes = '';
foreach($lines as $line)
	if(preg_match('/^\[remote "(.+)"/', $line, $match))
		$remotes .= '<option value="'.$match[1].'">'.$match[1].'</option>';
$remotes = str_replace('value="origin"', 'value="origin" selected="selected"', $remotes);

## Generate app <option>s
$app_options = '';
foreach(scandir(ROOT.'apps') as $app)
{
	if(is_dir(ROOT.'apps/'.$app.'/.git'))
	{
		if($_SESSION['git_path'] == ROOT.'apps/'.$app)
			$app_options .= '<option value="'.ROOT.'apps/'.$app.'" selected="selected">'.$app.' (selected)</option>';
		else
			$app_options .= '<option value="'.ROOT.'apps/'.$app.'">'.$app.'</option>';
	}
}

## same thing but skins
$skin_options = '';
foreach(scandir(ROOT.'skins') as $skin)
{
	if(is_dir(ROOT.'skins/'.$skin.'/.git'))
	{
		if($_SESSION['git_path'] == ROOT.'skins/'.$skin)
			$skin_options .= '<option value="'.ROOT.'skins/'.$skin.'" selected="selected">'.$skin.'</option>';
		else
			$skin_options .= '<option value="'.ROOT.'skins/'.$skin.'">'.$skin.'</option>';
	}
}

## same thing but plugins
$plugin_options = '';
foreach(scandir(ROOT.'plugins') as $plugin)
{
	if(is_dir(ROOT.'plugins/'.$plugin.'/.git'))
	{
		if($_SESSION['git_path'] == ROOT.'plugins/'.$plugin)
			$plugin_options .= '<option value="'.ROOT.'plugins/'.$plugin.'" selected="selected">'.$plugin.'</option>';
		else
			$plugin_options .= '<option value="'.ROOT.'plugins/'.$plugin.'">'.$plugin.'</option>';
	}
}


## Get current branch, replace tools into output
$status = shell_exec('/usr/bin/git status');
$status = preg_replace(
	'/both modified:\s+([0-9A-Za-z.\/_]+)/', 
	'$0 <a '.jsprompt('Are you sure?').'href="%appurl%add?file=$1">mark resolved</a>',
$status);
//$status = preg_replace('/^modified: [^\n]+/', "$0 checkout", $status);

$status = preg_replace(
	'/modified:\s+([0-9A-Za-z.\/_]+)/', 
	'<a '.jsprompt('Are you sure?').'href="%appurl%cohead?file=$1">undo</a> $0',
$status);

$status = preg_replace(
	'/deleted:\s+([0-9A-Za-z.\/_]+)/', 
	'<a '.jsprompt('Are you sure?').'href="%appurl%cohead?file=$1">undo</a> $0',
$status);


preg_match("/^On branch ([^\n]+)/", $status, $match);
$current = $match[1];
$untracked = explode('Untracked files:', $status);



## Handle untracked files
if(isset($untracked[1]))
{
	$untracked = explode("\n\n", $untracked[1]);
	$status = str_replace(
		$untracked[1],
		preg_replace(
			'/([0-9A-Za-z.\/_]+)/', 
			'$0 <a href="%appurl%add?file=$1">add</a>', 
			$untracked[1]),
		$status
	); // <a '.jsprompt('Are you sure?').' href="%appurl%delete?file=$1">delete</a>
}

//test
$continue = '';
if(isset($_SESSION['rebase'])) $continue = ' --continue';

$update = $current == 'master' 
	? ''//'<a href="%appurl%push">Push</a>' 
	: '<a '.jsprompt('Are you sure you want to merge ['.$current.'] this into [master]?').' href="%appurl%merge/'.$current.'" title="This will merge the current branch with master.">Merge</a> | <a title="Rebase if you want updates from master to apply to your branch" href="%appurl%rebase/">Rebase'.$continue.'</a>
	
	';

$branches = shell_exec('/usr/bin/git for-each-ref --sort=-committerdate refs/heads/');
$branches = explode("\n", $branches, -1);

## Better Status
$status = shell_exec('/usr/bin/git status -b --porcelain');

if(preg_match_all('/(^|\n\s?)(A|A?M|UU|\?\?|D)\s+([^\n]+)/', $status, $match))
{
	for($i = 0; $i < count($match[0]); $i++)
	{
		$full = $match[0][$i];
		$sol = $match[1][$i]; // start of line
		$operation = $match[2][$i];
		$file = $match[3][$i];
		
		unset($replace);
		switch($operation)
		{
			case 'AM':
				$replace = 'AM (<a '.jsprompt('Are you sure?').' href="%appurl%reset?file='.$file.'">Reset</a>) '.$file.' (modified since staged)';
				break;
			// lol UUMAD??
			case 'UU':
				$replace = 'UU (<a '.jsprompt('Are you sure?').'href="%appurl%cohead?file='.$file.'">Undo</a>, <a '.jsprompt('Are you sure?').' href="%appurl%add?file='.$file.'">Mark Resolved</a>) '.$file.' CONFLICT!';
				break;
			case 'M':
				$replace = 'M <!-- , <a href="">stage</a> --> (<a '.jsprompt('Are you sure?').'href="%appurl%cohead?file='.$file.'">Undo</a>) '.$file.' (modified)';
				break;
			case 'A':
				$replace = 'A (<a '.jsprompt('Are you sure?').' href="%appurl%reset?file='.$file.'">Reset</a>) '.$file.' (staged to add)';
				break;
			case 'D':
				$replace = 'D (<a '.jsprompt('Are you sure?').'href="%appurl%cohead?file='.$file.'">Undo</a>) Deleted: '.$file;
				break;
			case '??':
				$replace = '?? (<a href="%appurl%add?file='.$file.'">Add</a>) Untracked: '.$file;
				break;
		}
		
		if(isset($replace))
			$status = str_replace($full, $sol.$replace, $status);
	}
}

## Get git tree
$tree = shell_exec('git log --all --graph --pretty=tformat:"%x1b%h%x09%x1b%d%x1b%x20%s%x20%x1b[%an]%x1b" | grep " ("');

$tree = nl2br($tree);

## Prettified Git Diff

chdir($this->path);
if($current == 'master')
	$against = 'origin/master';
else if($current == 'development')
	$against = 'master';
else
	$against = 'development';

$branchdiff = '<div class="modified_diff_header">Diff '.$against.'..'.$current.'</div>'.shell_exec('/usr/bin/git diff --name-status '.$against.'..'.$current.' 2>&1');

$diff = '<a href="#" class="modified_showdiff">Show/Hide Diff</a>
<div class="modified_diff">'.htmlentities(substr(shell_exec('/usr/bin/git diff 2>&1'), 0, -1)).
$branchdiff.'</div>';

/* Red/Green diff colors */
$diff = preg_replace('/(^|\n)(diff \-\-git [^\n]+)/', '$1<span class="modified_diff_header">$2</span>', $diff);
$diff = preg_replace('/(^|\n)(\+[^\n]*)/', '$1<span class="modified_diff_to">$2</span>', $diff);
$diff = preg_replace('/(^|\n)(-[^\n]*)/', '$1<span class="modified_diff_from">$2</span>', $diff);
$diff = preg_replace('/\n<span/', '<span', $diff);
$diff = preg_replace('/<\/span>\n/', '</span>', $diff);



$status = preg_replace('/^##\s([^\r\n]+)/', 'Current Branch: <strong>$1</strong>', $status);
$status = nl2br($status);
