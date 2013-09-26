<?php

class git extends app 
{
	public function main($vars)
	{
		$cwd = getcwd();
		chdir(ROOT.'..');
		
		
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
			$parts = explode('/', $branch);
			if($parts[2] == $current)
			{
				echo '<li><form action="%appurl%commit" method="post">Active '.$branch.' <input type="text" name="commit_text" placeholder="Optional commit text"/> <input type="submit" value="Commit" /></form> </li>';
			}
			else
			{
				$delete = '';
				if($parts[2] != 'master')
					$delete = ' [<a href="%appurl%rm/'.$parts[2].'">Delete</a>]';
				echo '<li><a href="%appurl%checkout/'.$parts[2].'">Checkout</a> '.$branch.$delete.'</li>';
			}
		}
		echo '</ul>';
		
		//echo nl2br(print_r($match, true));
	
		//echo nl2br($current);
		
		chdir($cwd);
	}
	
	public function rm($vars)
	{
		if($vars[1] == 'master') return 'nope';
		
		$cwd = getcwd();
		chdir(ROOT.'..');
		
		echo nl2br(shell_exec('/usr/bin/git branch -D '.$vars[1].' 2>&1'));
		chdir($cwd);
		
		$this->main($vars);
	}
	
	public function push($vars)
	{
		$cwd = getcwd();
		chdir(ROOT.'..');
		
		echo nl2br(shell_exec('/usr/bin/git push -u lf master 2>&1'));
		chdir($cwd);
		
		$this->main($vars);
	}
	
	public function merge($vars)
	{
		
		$cwd = getcwd();
		chdir(ROOT.'..');
		
		echo nl2br(shell_exec('/usr/bin/git checkout master 2>&1 && /usr/bin/git merge '.$vars[1].' 2>&1'));
		chdir($cwd);
		
		
		$this->main($vars);
	}
	
	public function create($vars)
	{
		$cwd = getcwd();
		chdir(ROOT.'..');
		
		shell_exec('/usr/bin/git checkout -b "'.$_POST['newbranch'].'"');
		
		chdir($cwd);
		
		$this->main($vars);
	}
	
	public function checkout($vars)
	{
		$cwd = getcwd();
		chdir(ROOT.'..');
		
		echo nl2br(shell_exec('/usr/bin/git checkout "'.$vars[1].'" 2>&1'));
		
		chdir($cwd);
		
		$this->main($vars);
	}
	public function commit($vars)
	{
		$cwd = getcwd();
		chdir(ROOT.'..');
		
		$out = shell_exec('/usr/bin/git commit -a -m "'.$_POST['commit_text'].'"');
		echo nl2br($out);
		
		chdir($cwd);
		
		
		$this->main($vars);
	}
}