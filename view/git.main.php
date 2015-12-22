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
		<h3 class="no_martop">Local Branches</h3>
		<form action="%appurl%create" method="post">
			<div class="row">
				<div class="col-8">
					<input type="text" name="newbranch" placeholder="New branch name"/>
				</div>
				<div class="col-4">
					<input class="blue" type="submit" value="Create" />
				</div>
			</div>
		</form>


		
		<?php if($current == NULL): ?>
			<tile class="white">
				<form action="%appurl%commit" method="post">
					<strong>Not currently on any branch</strong> 
					<input type="text" class="green" name="commit_text" placeholder="Commit text"/> 
					<input type="submit" value="Commit" />
					<?=$pull;?> 
					<span><?=$branch;?><span>
				</form>
			</tile>
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
					
					<!-- <li class="active"> -->
					<div class="tile white marbot">
						<div class="tile-header">
							<div class="row">
								<div class="col-12">
									<i class="fa fa-check green_fg" title="Current Branch"></i>
									<span class=""><?=$parts[2];?></span>
									<span class="pull-right">
										<a href="%appurl%history" title="Commit History"><i class="fa fa-history"></i></a>
										<?=$branch;?>
									</span>
								</div>
							</div>
						</div>
						<div class="tile-content">
							<form action="%appurl%commit" method="post">
								<div class="row">
									<div class="col-10"><input type="text" name="commit_text" placeholder="Commit text"/></div>
									<div class="col-2"><input type="submit" class="green" value="Commit" /></div>
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
									<div class="col-2"><input class="blue" type="submit" name="operation" value="Merge" /></div>
									<div class="col-2"><input class="dark_blue" type="submit" name="operation" value="Rebase" /></div>
									<div class="col-2"><input class="dark_blue" type="submit" class="green" name="operation" value="--continue" /></div>
								</div>
							</form>
							
							<form action="%appurl%gitop" method="post">
								<div class="row">
									<div class="col-2"><span>Stash Ops:</span></div>
									<div class="col-2"><input type="submit" name="operation" value="Stash" /></div>
									<div class="col-2"><input type="submit" name="operation" value="List" /></div>
									<div class="col-2"><input type="submit" name="operation" value="Branch" /></div>
									<div class="col-2"><input class="dark_gray" type="submit" name="operation" value="Apply" /></div>
									<div class="col-2"><input class="dark_gray" type="submit" name="operation" value="--index" /></div>
								</div>
							</form>
						</div>
					</div>
					
					
					</li>
					<?php
				}
				else
				{
					$delete = '';
					if($parts[2] != 'master') 
						$delete = ' <a '.jsprompt('Are you sure you want to delete ['.$parts[2].']?').'  href="%appurl%rm/'.$parts[2].'" class="x" title="Delete Branch"><i class="fa fa-trash-o"></i></a>';
					
					?>
					
					 <div class="tile white marbot">
						<div class="tile-content">
							<div class="row">
								<div class="col-12">
									<i class="fa fa-check"></i>
									<a href="%appurl%checkout/<?=$parts[2];?>"><?=$parts[2];?></a>
									<span style="float: right">
										<?=$delete.$pull;?>
										<?=$branch;?>
									</span>
								</div>
							</div>
						</div>
					</div>
					<?php
					//echo '<li><a href="%appurl%checkout/'.$parts[2].'">'.$parts[2].'</a> '.$delete.$pull.'<span style="float: right">'.$branch.'</span>';
					
				}
			}
			
		?>
		
		<div class="tile white marbot">
			<div class="tile-header">
				<h4>Branch Tree</h4>
			</div>
			<div class="tile-content">
				<div class="row">
					<div class="col-12">
						<?=$tree;?>
					</div>
				</div>
			</div>
		</div>
		
		<h4>Git Diff</h4>
		<?=$diff;?>
		
	</div>
	<div class="col-4">
		<div class="tile white marbot">
			<div class="tile-header">
				<h4>Repository</h4>
			</div>
			<div class="tile-content">
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
							<button class="blue" type="submit">Change Repo</button>
						</div>
						<div class="col-6">
							<a class="button" title="Click to manage your repositories" href="%appurl%repo">View Repos</a>
						</div>
					</div>
				</form>
			</div>
		</div>
		
		<div class="tile white marbot">
			<div class="tile-header">
				<h4>Status</h4>
			</div>
			<div class="tile-content">
				<div class="row">
					<div class="col-12">
						<?=$status;?>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<a <?=jsprompt();?> href="%appurl%deleteErrorLogs" class="red button">Delete error_log files</a>
					</div>
				</div>
			</div>
		</div>
		
		<div class="tile white marbot">
			<div class="tile-header">
				<h4>Remotes</h4>
			</div>
			<div class="tile-content">
				<form action="%appurl%gitop" method="post">
					<div class="row">
						<div class="col-6">
							<select name="remote" id="">
								<?=$remotes;?>
							</select>
						</div>
						<div class="col-6">
							<a class="button" href="%appurl%remotes">View Remotes</a>
						</div>
					</div>
					<div class="row">
						<div class="col-12">
							<input type="text" name="branch" placeholder="master" value="<?=$current;?>" />
						</div>
					</div>
					<div class="row">
						<div class="col-3">
							<input class="dark_gray"  type="submit" name="operation" value="fetch" />
						</div>
						<div class="col-2">
							<input class="dark_gray"  type="submit" name="operation" value="-p" />
						</div>
						<div class="col-3">
							<input class="blue" type="submit" name="operation" value="pull" />
						</div>
						<div class="col-4">
							<input class="blue" type="submit" name="operation" value="--rebase" /> 
						</div>
					</div>
					<div class="row">
						<div class="col-7">
							<input type="submit" name="operation" value="checkout" /> 
						</div>
						<div class="col-2">
							<input type="submit" name="operation" value="-b" /> 
						</div>
						<div class="col-3">
							<input class="green" type="submit" name="operation" value="push" />
						</div>
					</div>
				</form>
			</div>
		</div>
		
		
		
		<div class="tile white">
			<div class="tile-header">
				<h4>Tags</h4>
			</div>
			<div class="tile-content">
				<div class="row">
					<div class="col-12">
						<form action="%appurl%tag" method="post">
							<input type="text" name="tag" placeholder="Tag (STABLE, DEV)" />
						</form>
					</div>
				</div>
				<div class="row">
					<div class="col-6">
						<button class="green">Tag Commit</button>
					</div>
					<div class="col-6">
						<a class="button" href="%appurl%tags">View Tags</a>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<a href="%appurl%identity">Configure Identity</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
		
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