<?php

// print any message set before last call, then unset
if(isset($_SESSION['git_msg']))
{
	echo '<pre class="rounded notice">';
	echo $_SESSION['git_msg'];
	echo '</pre>';
	unset($_SESSION['git_msg']);
}

?>

<div class="row">
	<div class="col-8">
		<h3>Local Branches</h3>
		<form action="%appurl%create" method="post">
			<div class="row">
				<div class="col-8">
					<input type="text" name="newbranch" placeholder="New branch name"/>
				</div>
				<div class="col-4">
					<input type="submit" value="Create" />
				</div>
			</div>
		</form>






		<ul class="efvlist">
			<?php if($current == NULL): ?>
				<li>
					<form action="%appurl%commit" method="post">
						<strong>Not currently on any branch</strong> 
						<input type="text" name="commit_text" placeholder="Commit text"/> 
						<input type="submit" value="Commit" />
						<?=$pull;?> 
						<span><?=$branch;?><span>
					</form>
				</li>
			<?php endif; ?>
			
			
			<?php
			
				include ROOT.'apps/git/model/git.branch.php';
				
				$branch_options = '';
				foreach($branches as $branch)
				{
					$parts = explode('/', $branch); 
					$branch = $parts[2];
					$hash = substr($branch, 0, 7);
					
					$branch_options .= '<option value="'.$branch.'">'.$branch.'</option>';
				}
				
				foreach($branches as $branch)
				{
					$pull = ''; 
					$parts = explode('/', $branch); 
					$branch = substr($branch, 0, 7);
					/*if($parts[2] != 'master') $pull = '
						<form action="%appurl%pullrequest/'.$parts[2].'">
							<input type="text" name="ticketid" placeholder="Ticket ID" />
							<input type="submit" value="Submit pull request" />
						</form>';*/
					
					if($parts[2] == $current) 
					{
						?>
						<li class="active">
							<form action="%appurl%commit" method="post">
								<div class="row">
									<div class="col-2"><strong class="label dark_b"><?=$parts[2];?></strong></div>
									<div class="col-5"><input type="text" name="commit_text" placeholder="Commit text"/></div>
									<div class="col-2"><input type="submit" value="Commit" /></div>
									<div class="col-3"><a class="label" href="%appurl%history">view history (<?=$branch;?>)</a></div>
								</div>
							</form>
						
							<form action="%appurl%gitop/<?=$parts[2];?>" method="post">
								<div class="row">
									<div class="col-2"><span>Merge Ops:</span></div>
									<div class="col-4">	
										<select name="branch" id="">
											<option value="">-- Select Branch --</option>
											<?=$branch_options;?>
										</select>
									</div>
									<div class="col-2"><input type="submit" name="operation" value="Merge" /></div>
									<div class="col-2"><input type="submit" name="operation" value="Rebase" /></div>
									<div class="col-2"><input type="submit" name="operation" value="--continue" /></div>
								</div>
							</form>
							
							<form action="%appurl%gitop" method="post">
								<div class="row">
									<div class="col-2"><span>Stash Ops:</span></div>
									<div class="col-2"><input type="submit" name="operation" value="Stash" /></div>
									<div class="col-2"><input type="submit" name="operation" value="List" /></div>
									<div class="col-2"><input type="submit" name="operation" value="Branch" /></div>
									<div class="col-2"><input type="submit" name="operation" value="Apply" /></div>
									<div class="col-2"><input type="submit" name="operation" value="--index" /></div>
								</div>
							</form>
						</li>
						<?php
					}
					else
					{
						$delete = '';
						if($parts[2] != 'master') 
							$delete = ' [<a '.jsprompt('Are you sure you want to delete ['.$parts[2].']?').'  href="%appurl%rm/'.$parts[2].'">Delete</a>]';
						
						?>
						
						 <li>
							<a href="%appurl%checkout/<?=$parts[2];?>"><?=$parts[2];?></a> 
							<?=$delete.$pull;?>
							<span style="float: right">
							<?=$branch;?>
							</span>
						</li>
						<?php
						//echo '<li><a href="%appurl%checkout/'.$parts[2].'">'.$parts[2].'</a> '.$delete.$pull.'<span style="float: right">'.$branch.'</span>';
						
					}
				}
				
			?>
		</ul>
	</div>
	<div class="col-4">
	
		<h3>Repositories</h3>
		
		<form action="%appurl%" method="post">
			<div class="row">
				<div class="col-12">
					<select name="newgitpath" />
						<?php if(is_dir(ROOT.'../.git')) : ?>
						<optgroup label="System">
							<option value="<?=ROOT;?>..">LittlefootCMS</option>
						</optgroup>
						<?php endif; ?>
						<optgroup label="Apps"><?=$app_options;?></optgroup>
						<optgroup label="Skins"><?=$skin_options;?></optgroup>
						<optgroup label="Plugins"><?=$plugin_options;?></optgroup>
					</select> 
				</div>
			</div>
			<div class="row">
				<div class="col-6">
					<a class="button blue" title="Click to manage your repositories" href="%appurl%repo">View Repos</a>
				</div>
				<div class="col-6">
					<button class="green" type="submit">Change Repo</button>
				</div>
			</div>
		</form>
		
		<h3>Remotes</h3>
		
		<form action="%appurl%gitop" method="post">
			<div class="row">
				<div class="col-6">
					<a class="blue button" href="%appurl%remotes">View Remotes</a>
				</div>
				<div class="col-6">
					<select name="remote" id="">
						<?=$remotes;?>
					</select>
				</div>
				
			</div>
			<div class="row">
				<div class="col-12">
					<input type="text" name="branch" placeholder="master" />
				</div>
			</div>
			<div class="row">
				<div class="col-3">
					<input type="submit" name="operation" value="push" />
				</div>
				<div class="col-3">
					<input type="submit" name="operation" value="pull" />
				</div>

				<div class="col-3">
					<input type="submit" name="operation" value="-p" />
				</div>
				<div class="col-3">
					<input type="submit" name="operation" value="-b" /> 
				</div>
			</div>
			<div class="row">
				<div class="col-4">
					<input class="green" type="submit" name="operation" value="fetch" />
				</div>
				<div class="col-4">
					<input type="submit" name="operation" value="checkout" /> 
				</div>
				<div class="col-4">
					<input type="submit" name="operation" value="--rebase" /> 
				</div>
			</div>
		</form>
		
		<h3>Tags</h3>

		<div class="row">
			<div class="col-12">
				<form action="%appurl%tag" method="post">
					<input type="text" name="tag" placeholder="Tag (STABLE, DEV)" />
				</form>
			</div>
		</div>
		<div class="row">
			<div class="col-6">
				<a class="button" href="%appurl%tags">View Tags</a>
			</div>

			<div class="col-6">
				<button class="green">Tag Commit</button>
			</div>
		</div>
			
		<a href="%appurl%identity">Configure Identity</a>

	</div>
</div>



<?php

chdir($this->path);
// Git
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

/*
// Get current branch, replace tools into output
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

echo '<pre>';
echo $status;
echo '</pre>';*/



// Better Status
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

$tree = shell_exec('git log --all --graph --pretty=tformat:"%x1b%h%x09%x1b%d%x1b%x20%s%x20%x1b[%an]%x1b" | grep " ("');

echo '<h3>Status</h3>';
$status = preg_replace('/^##\s([^\r\n]+)/', 'Current Branch: <strong>$1</strong>', $status);
echo nl2br($status);

echo '<h3>Branch Tree</h3>';
echo nl2br($tree);

echo '<h3>Diff</h3>';
echo $diff.'<br />';

?>
		
		
		
<script type="text/javascript">
	$(document).ready(function(){
		$('.modified_showdiff').click(
			function()
			{
				$(this).next().toggle();
				return false;
			}
		);
		//$('.modified_showdiff').next().toggle();
	});
</script>