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
		echo '<div>Path: <form action="%appurl%" method="post"><select name="newgitpath" />';
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
		
		echo '</select><input type="submit" /></form></div>';
		
		
		
		
		
		
		
		
		// Get current branch
		$current = shell_exec('/usr/bin/git status');
		
		if($vars[0] == '')
			echo nl2br($current);
		
		preg_match("/# On branch ([^\n]+)/", $current, $match);
		$current = $match[1];
		
		$update = $current == 'master' 
			? '<a href="%appurl%push">Push</a>' 
			: '<a href="%appurl%merge/'.$current.'">Merge</a>';
		
		echo '<h3>Current branch: '.$current.' ['.$update.']</h3>'; 
		
		$branches = shell_exec('/usr/bin/git for-each-ref --sort=-committerdate refs/heads/');
	
		$branches = explode("\n", $branches, -1);
		echo '<ul>
			<li>
				<form action="%appurl%create" method="post"><input type="text" name="newbranch" placeholder="Create new branch"/></form>
			</li>
		';
		
		foreach($branches as $branch)
		{
			$pull = '';
			$parts = explode('/', $branch);
			if($parts[2] != 'master') $pull = ' [<a href="%appurl%pullrequest/'.$parts[2].'">Submit Pull Request</a>] ';
			
			if($parts[2] == $current)
			{
				echo '<li><form action="%appurl%commit" method="post"><strong>'.$parts[2].'</strong> <input type="text" name="commit_text" placeholder="Optional commit text"/> <input type="submit" value="Commit" />'.$pull.' <span style="float: right">'.$branch.'<span></form></li>';
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
	}
	
	public function rm($vars)
	{
		if($vars[1] == 'master') return 'nope';
		
		
		echo nl2br(shell_exec('/usr/bin/git branch -D '.$vars[1].' 2>&1'));
		
		$this->main($vars);
	}
	
	public function push($vars)
	{
		
		echo nl2br(shell_exec('/usr/bin/git push -u origin master 2>&1'));
		
		$this->main($vars);
	}
	
	public function merge($vars)
	{
		
		echo nl2br(shell_exec('/usr/bin/git checkout master 2>&1 && /usr/bin/git merge '.$vars[1].' 2>&1'));
		
		
		$this->main($vars);
	}
	
	public function create($vars)
	{
		
		shell_exec('/usr/bin/git checkout -b "'.$_POST['newbranch'].'"');
		
		
		$this->main($vars);
	}
	
	public function checkout($vars)
	{
		echo nl2br(shell_exec('/usr/bin/git checkout "'.$vars[1].'" 2>&1'));
		
		$this->main($vars);
	}
	public function commit($vars)
	{
		
		
		$out = shell_exec('/usr/bin/git commit -am "'.$_POST['commit_text'].'"');
		echo nl2br($out);
		
		
		$this->main($vars);
	}
}