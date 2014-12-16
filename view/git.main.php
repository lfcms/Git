<?php

// print any message set before last call, then unset
if(isset($_SESSION['git_msg']))
{
	echo '<span class="git_msg">';
	echo nl2br($_SESSION['git_msg']);
	echo '</span>';
	unset($_SESSION['git_msg']);
}

?>
<div id="git_tools">
	<h3>Tools</h3>
	<form action="%appurl%" method="post">
		<a title="Click to manage your repositories" href="%appurl%repo">Repo</a>: 
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
		<input type="submit" value="Change Repo" />
	</form>
	<form action="%appurl%gitop" method="post">
		<a href="%appurl%remotes">Remotes</a>: 
			<input type="submit" name="operation" value="fetch" />
			<input type="submit" name="operation" value="-p" />
			<select name="remote" id="">
				<?=$remotes;?>
			</select> 
			/ <input type="text" name="branch" placeholder="master" />
			<input type="submit" name="operation" value="pull" />
			<input type="submit" name="operation" value="--rebase" /> 
			<input type="submit" name="operation" value="push" />
			<input type="submit" name="operation" value="checkout" /> 
			<input type="submit" name="operation" value="-b" /> 
	</form>
	<form action="%appurl%tag" method="post">
		<a href="%appurl%tags">Tag</a>: <input type="text" name="tag" placeholder="Tag (STABLE, DEV)" />
	</form>
	<a href="%appurl%identity">Configure Identity</a>
</div>
<div id="git_branches">
	<h3>Local Branches</h3>
	<form action="%appurl%create" method="post">
		Create a new branch (/[A-Za-z0-9_]+/): <input type="text" name="newbranch" placeholder="New branch name"/> 
		<input type="submit" value="Create" />
	</form>
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
		
		ob_start(); // capture branch <li>
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
				<li class="git_current_branch">
					<form action="%appurl%commit" method="post">
						<strong><?=$parts[2];?></strong> 
						<input type="text" name="commit_text" placeholder="Commit text"/> 
						<input type="submit" value="Commit" /><!-- <?=$pull;?> -->
						<span style="float: right">(<a href="%appurl%history">view history</a>) <?=$branch;?></span>
					</form>
					<div class="git_current_tools">
						<!-- a<?=$update;?> -->
						<form action="%appurl%gitop/<?=$parts[2];?>" method="post">
							<select name="branch" id="">
								<option value="">-- Select Branch --</option>
								<?=$branch_options;?>
							</select>
							<input type="submit" name="operation" value="Merge" />
							<input type="submit" name="operation" value="Rebase" />
							<input type="submit" name="operation" value="--continue" />
						</form>
					</div>
					<div>
						Stash Ops: <form action="%appurl%gitop" method="post">
							<input type="submit" name="operation" value="Stash" />
							<input type="submit" name="operation" value="List" />
							<input type="submit" name="operation" value="Branch" />
							<input type="submit" name="operation" value="Apply" />
							<input type="submit" name="operation" value="--index" />
						</form>
					</div>
				</li>
				<?php
			}
			else
			{
				$delete = '';
				if($parts[2] != 'master') 
					$delete = ' [<a '.jsprompt('Are you sure you want to delete ['.$parts[2].']?').'  href="%appurl%rm/'.$parts[2].'">Delete</a>]';
				echo '<li><a href="%appurl%checkout/'.$parts[2].'">'.$parts[2].'</a> '.$delete.$pull.'<span style="float: right">'.$branch.'</span>';
				
			}
		}
		
		$branchesOutput = ob_get_clean();
		
	?>
	<ul id="local_branches">
		<?php if($current == NULL): ?>
		<li>
			<form action="%appurl%commit" method="post"><strong>Not currently on any branch</strong> <input type="text" name="commit_text" placeholder="Commit text"/> <input type="submit" value="Commit" /><?=$pull;?> <span><?=$branch;?><span></form></li>
		<?php endif;
		
		/*function recurseBranches($parent, $boutput, $resolve)
		{
			echo '<ul>';
			foreach($resolve[$parent] as $child)
			{
				echo $boutput[$child];
				if(isset($resolve[$child]))
				{				
					recurseBranches($child, $boutput, $resolve);
				}
			}
			echo '</li></ul>';
		}*/
		
		
		//echo $boutput['master'];
		//recurseBranches('master', $boutput, $resolve);
		
		//foreach($
		
		echo $branchesOutput;
		
		?>
		</li>
	</ul>
</div>

<?php 
/*
<h3>Status</h3>
echo nl2br($status);*/

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