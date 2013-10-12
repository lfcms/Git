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
		echo '<div><form action="%appurl%" method="post">Repo: <select name="newgitpath" />';
		if(is_dir(ROOT.'../.git')) echo '<optgroup label="System"><option value="'.ROOT.'..">LittlefootCMS</option></optgroup>';
		
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
		echo '</optgroup>';
		
		echo '<optgroup label="Skins">';
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
		
		echo '</select><input type="submit" value="Change Repo" /></form></div>';
		
		
		
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
			? '<a href="%appurl%push">Push</a>' 
			: '<a href="%appurl%merge/'.$current.'">Merge</a>';
		
		echo '<h3>Current branch: '.$current.' ['.$update.']</h3>'; 
		echo nl2br($status);
		
		echo '<fieldset>';
		
		$branches = shell_exec('/usr/bin/git for-each-ref --sort=-committerdate refs/heads/');
	
		$branches = explode("\n", $branches, -1);
		echo '<form action="%appurl%create" method="post">Create a new branch: <input type="text" name="newbranch" placeholder="New branch name"/> <input type="submit" value="Create" /></form>';
		
		echo '<h4>Availabled Brances</h4>
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
		echo '</fieldset>';
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
		
		$this->main($vars);
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
}