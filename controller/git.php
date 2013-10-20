<?php

class git extends app 
{
	private $path;
	
	protected function init($vars)
	{
		if(isset($_POST['newgitpath'])) $_SESSION['git_path'] = $_POST['newgitpath'];
		else if(!isset($_SESSION['git_path'])) $_SESSION['git_path'] = ROOT.'..';
		
		$this->path = $_SESSION['git_path'];
		
		chdir($this->path);
	}
	
	public function main($vars)
	{
		// Get list of remotes
		$lines = file($this->path.'/.git/config');
		$remotes = '';
		foreach($lines as $line)
			if(preg_match('/^\[remote "(.+)"/', $line, $match))
				$remotes .= '<option value="'.$match[1].'">'.$match[1].'</option>';
		
		$remotes = str_replace('value="origin"', 'value="origin" selected="selected"', $remotes);
		
		echo '<div>
			
		<form action="%appurl%" method="post">
			<h3>
			Repo: <select name="newgitpath" />';
			if(is_dir(ROOT.'../.git')) 
				echo '
					<optgroup label="System">
						<option value="'.ROOT.'..">LittlefootCMS</option>
					</optgroup>';
		
		echo '<optgroup label="Apps">';
		foreach(scandir(ROOT.'apps') as $app)
		{
			if($app[0] == '.') continue;
			if(is_dir(ROOT.'apps/'.$app.'/.git'))
			{
				if($_SESSION['git_path'] == ROOT.'apps/'.$app)
					echo '<option value="'.ROOT.'apps/'.$app.'" selected="selected">'.$app.'</option>';
				else
					echo '<option value="'.ROOT.'apps/'.$app.'">'.$app.'</option>';
			}
		}
		echo '</optgroup>
				<optgroup label="Skins">';
		foreach(scandir(ROOT.'skins') as $skin)
		{
			if($skin[0] == '.') continue;
			
			if(is_dir(ROOT.'skins/'.$skin.'/.git'))
				echo '<option value="'.ROOT.'skins/'.$skin.'">'.$skin.'</option>';
				
			
			if(is_dir(ROOT.'skins/'.$skin.'/.git'))
			{
				if($_SESSION['git_path'] == ROOT.'skins/'.$skin)
					echo '<option value="'.ROOT.'skins/'.$skin.'" selected="selected">'.$skin.'</option>';
				else
				echo '<option value="'.ROOT.'skins/'.$skin.'">'.$skin.'</option>';
			}
		}
		echo '</optgroup>';
		
		echo '</select><input type="submit" value="Change Repo" /></h3></form></div>';
		
		echo '
			<form action="%appurl%pushpull" method="post">
				<a href="%appurl%remotes">Remotes</a>: <select name="remote" id="">'.$remotes.'</select>
					/ <input type="text" name="branch" placeholder="master" />
					<input type="submit" name="direction" value="pull" />
					<input type="submit" name="direction" value="push" /> 
			</form> ';
		
		echo '<style type="text/css">
			#app-apps fieldset { margin-top: 10px; }
			#app-apps ul { list-style: none; margin: 0; margin-top: 10px; padding: 0; }
			#app-apps ul li { margin-top: 10px; }
			#app-apps h3 { margin-top: 20px; }
			#app-apps h4 { margin-top: 10px; }
			#app-apps .git_msg {   background: #AAAADD;
				border: medium solid #0000FF;
				color: #3333CC;
				display: block;
				font-weight: bold;
				margin: 10px 0;
				padding: 10px; }
		</style>';
		
		// Get current branch
		$status = shell_exec('/usr/bin/git status');
		
		preg_match("/# On branch ([^\n]+)/", $status, $match);
		$current = $match[1];
		
		$update = $current == 'master' 
			? ''//'<a href="%appurl%push">Push</a>' 
			: '(<a href="%appurl%merge/'.$current.'">Merge</a>) (<a href="%appurl%rebase/'.$current.'">Rebase</a>)';
		
		
		$branches = shell_exec('/usr/bin/git for-each-ref --sort=-committerdate refs/heads/');
	
		$branches = explode("\n", $branches, -1);
		
		
		echo '<h4>Available Branches</h4>';
		echo '<form action="%appurl%create" method="post">Create a new branch: <input type="text" name="newbranch" placeholder="New branch name"/> <input type="submit" value="Create" /></form>
			<ul>'; 
		
		foreach($branches as $branch)
		{
			$pull = ''; 
			$parts = explode('/', $branch);
			if($parts[2] != 'master') $pull = ' [<a href="%appurl%pullrequest/'.$parts[2].'">Submit Pull Request</a>] ';
			
			if($parts[2] == $current)
			{
				echo '<li><form action="%appurl%commit" method="post"><strong>'.$parts[2].'</strong> <input type="text" name="commit_text" placeholder="Commit text"/> <input type="submit" value="Commit" />'.$pull.' <span style="float: right">'.$branch.'<span></form></li>';
			}
			else
			{
				$delete = '';
				if($parts[2] != 'master')
					$delete = ' [<a href="%appurl%rm/'.$parts[2].'">Delete</a>]';
				echo '<li><a href="%appurl%checkout/'.$parts[2].'">'.$parts[2].'</a> '.$delete.$pull.'<span style="float: right">'.$branch.'</span></li>';
			}
		}
		echo '</ul>';
		
		echo '<h4>Current branch: '.$current.' '.$update.'</h4>'; 
		echo nl2br($status);
	}
	
	public function remotes($vars)
	{
		if($vars[1] == 'master') return 'nope';
		
		/*$ini = preg_replace('/\s"([^"]+)"/', '$1', file_get_contents($this->path.'/.git/config'));
		
		$ini_array = parse_ini_string($ini);
		
		echo nl2br(print_r($ini_array,true));*/
		
		echo '<span class="git_msg">';
		$config = "";
		$lines = file($this->path.'/.git/config');
		foreach($lines as $line)
			if(preg_match('/^\[remote|url =/', $line))
				echo $line.'<br />';
		echo '</span>';
		
		$this->main($vars);
	}
	
	public function rm($vars)
	{
		if($vars[1] == 'master') return 'nope';
		
		
		echo '<span class="git_msg">';
		echo substr(nl2br(shell_exec('/usr/bin/git branch -D '.$vars[1].' 2>&1')), 0, -1);
		echo '</span>';
		
		$this->main($vars);
	}
	
	public function push($vars)
	{
		
		echo '<span class="git_msg">';
		echo substr(nl2br(shell_exec('/usr/bin/git push -u origin master 2>&1')), 0, -1);
		echo '</span>';
		
		$this->main($vars);
	}
	
	public function merge($vars)
	{
		echo '<span class="git_msg">';
		echo substr(nl2br(shell_exec('/usr/bin/git checkout master 2>&1 && /usr/bin/git merge '.$vars[1].' 2>&1')), 0, -1);
		echo '</span>';
		
		$this->main($vars);
	}
	
	public function create($vars)
	{
		echo '<span class="git_msg">';
		echo substr(nl2br(shell_exec('/usr/bin/git checkout -b "'.$_POST['newbranch'].'" 2>&1')), 0, -1);
		echo '</span>';
		
		$this->main($vars);
	}
	 
	public function checkout($vars)
	{
		echo '<span class="git_msg">';
		echo substr(nl2br(shell_exec('/usr/bin/git checkout "'.$vars[1].'" 2>&1')), 0, -1);
		echo '</span>';
		
		redirect302($this->lf->appurl);
	}
	
	public function commit($vars)
	{
		$out = shell_exec('/usr/bin/git commit -am "'.$_POST['commit_text'].'" 2>&1');
		$out = substr($out, 0, -1);
		
		echo '<span class="git_msg">';
		echo nl2br($out);
		echo '</span>';
		
		$this->main($vars);
	}
	
	public function pullrequest($vars)
	{ 
		$out = shell_exec('/usr/bin/git diff --name-only master '.escapeshellarg($vars[1]).' 2>&1'); 
		$out = substr($out, 0, -1);
		
		$out2 = shell_exec('/usr/bin/git cherry -v master 2>&1'); 
		$out2 = substr($out2, 0, -1);
		
		echo '<span class="git_msg">
			Pull Request submitted<br /><br />
			Modified files (master -> '.$vars[1].'):<br />';
		echo nl2br(htmlentities($out));
		echo '</span>';
		
		$ticket = $vars[1];
		if(preg_match('/\d+/', $vars[1], $match))
			$ticket = $match[0];
		
		mail('qa@dev.eflipdomains.com', 'Ticket #'.intval($ticket).': Pull Request "'.$vars[1].'"', 'Pull request submitted by dev@'.$_SERVER['SERVER_NAME'].'

Branch: '.$vars[1].'
	
Modified files (master -> '.$vars[1].'):
'.$out.'

Commits:
'.$out2, 'From: dev@'.$_SERVER['SERVER_NAME']);


		$this->main($vars);
	}
	
	public function pushpull($vars)
	{
		if(!preg_match('/^(push|pull)$/', $_POST['direction'], $match)) return 'bad request';
		
		/*echo '<span class="git_msg">';
		
		echo '<br />';
		
		echo substr(nl2br(shell_exec('/usr/bin/git '.$match[1].' '.escapeshellarg($_POST['remote']).' '.escapeshellarg($_POST['branch']).' 2>&1')), 0, -1);
		echo '</span>';*/
		
		shell_exec('/usr/bin/git '.$match[1].' '.escapeshellarg($_POST['remote']).' '.escapeshellarg($_POST['branch']).' 2>&1');
		redirect302($this->lf->appurl);
	}
}