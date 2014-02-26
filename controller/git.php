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
		//if(!count($_POST))
			echo '<style type="text/css">
				#app-dashboard fieldset { margin-top: 10px; }
				#app-dashboard ul { list-style: none; margin: 0; margin-top: 10px; padding: 0; }
				#app-dashboard ul li { margin-top: 10px; }
				#app-dashboard h3 { margin-top: 20px; }
				#app-dashboard h4 { margin-top: 10px; }
				#app-dashboard .git_msg {   background: #AAAADD;
					border: medium solid #0000FF;
					color: #3333CC;
					display: block;
					font-weight: bold;
					margin: 10px 0;
					padding: 10px; }
			</style>';
			
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
		
		// Get current branch
		$status = shell_exec('/usr/bin/git status');
		
		preg_match("/# On branch ([^\n]+)/", $status, $match);
		$current = $match[1];
		
		$update = $current == 'master' 
			? ''//'<a href="%appurl%push">Push</a>' 
			: '(<a '.jsprompt('Are you sure you want to merge ['.$current.'] this into [master]?').' href="%appurl%merge/'.$current.'" title="This will merge the current branch with master.">Merge</a>) (<a title="Rebase if you want updates from master to apply to your branch" href="%appurl%rebase/'.$current.'">Rebase</a>)
			
			
			<form style="display: inline" method="post" action="%appurl%pullrequest/'.$current.'">
					<input type="text" name="ticketid" placeholder="Ticket ID" />
					<input type="submit" value="Submit pull request" />
				</form>
			
			';
		
		
		$branches = shell_exec('/usr/bin/git for-each-ref --sort=-committerdate refs/heads/');
	
		$branches = explode("\n", $branches, -1);
		
		
		echo '<h4>Available Branches</h4>';
		echo '<form action="%appurl%create" method="post">Create a new branch: <input type="text" name="newbranch" placeholder="New branch name"/> <input type="submit" value="Create" /></form>
			<ul>'; 
		
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
				echo '<li><form action="%appurl%commit" method="post"><strong>'.$parts[2].'</strong> <input type="text" name="commit_text" placeholder="Commit text"/> <input type="submit" value="Commit" />'.$pull.' <span style="float: right">'.$branch.'<span></form></li>';
			}
			else
			{
				$delete = '';
				if($parts[2] != 'master') 
					$delete = ' [<a '.jsprompt('Are you sure you want to delete ['.$parts[2].']?').'  href="%appurl%rm/'.$parts[2].'">Delete</a>]';
				echo '<li><a href="%appurl%checkout/'.$parts[2].'">'.$parts[2].'</a> '.$delete.$pull.'<span style="float: right">'.$branch.'</span></li>';
			}
		}
		echo '</ul>';
		
		echo '<h4>Current branch: '.$current.' '.$update.' <form action="%appurl%tag" method="post"><a href="%appurl%tags">Tag</a>: <input type="text" name="tag" placeholder="Tag (STABLE, DEV)" /></form></h4>'; 
		echo nl2br($status);
	}
	
	public function tag($vars)
	{
		$rev = shell_exec('git rev-list HEAD | wc -l');
		$rev = trim($rev);
		$version = 'v1.'.date('y.m').'-r'.$rev.'-'.$_POST['tag'];
		
		echo '<span class="git_msg">';
		$out = shell_exec('/usr/bin/git tag -a "'.$version.'" -m "'.$version.'" 2>&1');
		if(!$out)
			echo 'Tagged: '.$version;
		else
			echo $out;
		echo '<br />';
		echo 'Tags: <br />'.nl2br(trim(shell_exec('/usr/bin/git tag 2>&1')));
		echo '</span>';
		
		$this->main($vars);
	}
	
	public function tags($vars)
	{
		echo '<span class="git_msg">';
		echo 'Tags: <br />'.nl2br(trim(shell_exec('/usr/bin/git tag 2>&1')));
		echo '</span>';
		
		$this->main($vars);
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
		echo substr(nl2br(shell_exec('/usr/bin/git checkout master 2>&1 && /usr/bin/git merge '.escapeshellarg($vars[1]).' 2>&1')), 0, -1);
		echo '</span>';
		
		$this->main($vars);
	}
	
	public function rebase($vars)
	{
		if($vars[1] != 'master')
		{
			echo '<span class="git_msg">';
			echo substr(nl2br(shell_exec('/usr/bin/git rebase master  2>&1')), 0, -1);
			echo '</span>';
		}
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
		
		
		if(strpos($out, "*** Please tell me who you are.") !== false)
		{			
			$conf = '
[user]
        name = dev
        email = dev@'.$_SERVER['SERVER_NAME'].'
';
		
			file_put_contents('.git/config', $conf, FILE_APPEND);
			
			/*shell_exec('/usr/bin/git config user.email "dev@'.$_SERVER['SERVER_NAME'].'" 2>&1');
			shell_exec('/usr/bin/git config user.name "dev@'.$_SERVER['SERVER_NAME'].'" 2>&1');
			*/
			
			$out = shell_exec('/usr/bin/git commit -am "'.$_POST['commit_text'].'" 2>&1');
			$out = substr($out, 0, -1);
		}
		
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
		
		/*$ticket = $vars[1];
		if(preg_match('/\d+/', $vars[1], $match))
			$ticket = $match[0];*/
		
		
		$ticket = intval($_POST['ticketid']);
		$email = 'dev@'.$_SERVER['HTTP_HOST'];

		$qamail = 'qa@eflipdomains.com';		
	
		mail($qamail, 'Ticket #'.intval($ticket).': Pull Request "'.$vars[1].'"', 'Pull request submitted by '.$email.'

Branch: '.$vars[1].'
	
Modified files (master -> '.$vars[1].'):
'.$out.'

Commits:
'.$out2, 'From: '.$email); // parse at ticket system

		$this->main($vars);
	}
	
	public function pushpull($vars)
	{
		if(!preg_match('/^(push|pull)$/', $_POST['direction'], $match)) return 'bad request';
		
		echo '<span class="git_msg">';
		echo '/usr/bin/git '.$match[1].' '.escapeshellarg($_POST['remote']).' '.escapeshellarg($_POST['branch']).'<br />';
		if($_POST['branch'] != 'master')
			echo nl2br(shell_exec('/usr/bin/git checkout -b '.escapeshellarg($_POST['branch']).' 2>&1'));
		
		$tags = '';
		if($match[1] == 'push') $tags = ' --tags';
		
		echo substr(nl2br(shell_exec('/usr/bin/git '.$match[1].' '.escapeshellarg($_POST['remote']).' '.escapeshellarg($_POST['branch']).''.$tags.' 2>&1')), 0, -1);
		echo '</span>';
		
		$this->main($vars);
	}
}
