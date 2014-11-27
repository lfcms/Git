<link rel="stylesheet" href="%relbase%lf/apps/git/git.css" />
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
		//$_SESSION['rebase'] = '--continue';
		//unset($_SESSION['rebase']);
		
		//var_dump($_SESSION['rebase']);
	
		// Get list of remotes
		$lines = file($this->path.'/.git/config');
		$remotes = '';
		foreach($lines as $line)
			if(preg_match('/^\[remote "(.+)"/', $line, $match))
				$remotes .= '<option value="'.$match[1].'">'.$match[1].'</option>';
		$remotes = str_replace('value="origin"', 'value="origin" selected="selected"', $remotes);
		
		// Generate app <option>s
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
		
		// same thing but skins
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
		
		// same thing but plugins
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
		
		
		
		// Handle untracked files
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
			
			<form style="display: inline" method="post" action="%appurl%pullrequest/'.$current.'">
					<input type="text" name="ticketid" placeholder="Ticket ID" />
					<input type="submit" value="Submit pull request" />
				</form>
			
			';
		
		$branches = shell_exec('/usr/bin/git for-each-ref --sort=-committerdate refs/heads/');
		$branches = explode("\n", $branches, -1);
		
		/*include ROOT.'apps/git/model/git.branch.php';		
		echo '<pre>';
		print_r($resolve);
		echo '</pre>';*/
		
		//chdir(ROOT.'apps/git');
		ob_start();
		include ROOT.'apps/git/view/git.main.php';
		$main = ob_get_clean();
		
		
		
		
		
		
		chdir($this->path);
		// Git Diff		
		
		$diff = '<a href="#" class="modified_showdiff">Hide/Show Diff</a><div class="modified_diff">'.htmlentities(substr(shell_exec('/usr/bin/git diff 2>&1'), 0, -1)).'
		</div>';
		
		
		
		
		
		$diff = preg_replace('/(^|\n)(\+[^\n]+)\n/', '$1<div class="modified_diff_to">$2</div>', $diff);
		
		$diff = preg_replace('/(^|\n)(-[^\n]+)\n/', '$1<div class="modified_diff_from">$2</div>', $diff);
		
		
		
		//preg_match_all('/(^|\n)(diff --git a\/)(.*? b).*?(diff --git[^\n]+)?', $diff, $matches);
		
	//	preg_match_all('/(diff --git a\/(.*?)(\sb.*?)).*?\n(diff --git)?/', $diff, $matches);
			
		/*for($i = 0; $i < count($matches[0]); $i++)
		{
			$main = str_replace('', '', $main);
		}*/
		
		
		
		echo $main;
		
		
		
		echo $diff;
				
		
		// Better Status
		$status = shell_exec('/usr/bin/git status -b --porcelain');
		
		echo '<h3>Better Status</h3>';
		echo nl2br($status);
		
		
		
		
	}
	
	/*public function delete($args)
	{
		chdir($this->path);
		
		if(@unlink($_GET['file']))
			echo '<span class="git_msg">File "'.$_GET['file'].'" deleted</span>';
		else
			echo '<span class="git_msg">File "'.$_GET['file'].'" could not be deleted</span>';
		//$out = shell_exec('/usr/bin/git rm '.escapeshellcmd($_GET['file']).' 2>&1');;
		
		$this->main($vars);
	}*/
	
	public function history($args)
	{
		if(isset($args[1]))
		{
			
			echo '<span class="git_msg">';
			// checkout new branch
			echo substr(nl2br(shell_exec('/usr/bin/git checkout -b history'.escapeshellcmd($args[1]).' '.escapeshellcmd($args[1]).' 2>&1')), 0, -1);
			echo '</span>';
		}
	
		$out = substr(nl2br(shell_exec("/usr/bin/git log --graph --pretty=format:'%h %ad  %s%x09%ae' --date=short --abbrev-commit 2>&1")), 0, -1);
		
		$num_commits = substr_count($out, '*');
		
		$out = preg_replace("/(\*[^'0-9a-f]+)([0-9a-f]+)\s(\d{4}-\d{2}-\d{2})/", 
			'$1 $3 <a href="%appurl%history/$2">$2</a> ', 
			$out);
		
		$status = shell_exec('/usr/bin/git status');
		preg_match("/^On branch ([^\n]+)/", $status, $match);
		$current = $match[1];
		
		echo '
			<a href="%appurl%">Back</a>
			<h3>'.$_SESSION['git_path'].' on branch "'.$current.'"</h3>
			<h4>History ('.$num_commits.' commits)</h4>
			<p>Click a hash to branch from it</p>
		<span class="history">';
		echo $out;
		echo '</span>';
	}
	
	public function repo($args)
	{
		chdir(ROOT.'apps/git');
		include 'view/git.repo.php';
	}
	
	public function gitclone($args)
	{
		//git clone ssh://bios@localhost/home/bios/www/littlefoot/lf/skins/fresh
		
		$type = array('apps', 'skins');
		
		//echo '/usr/bin/git clone "'.escapeshellcmd($_POST['url']).'" 2>&1';
		
		chdir(ROOT.$type[intval($_POST['type'])]);
		
		$_SESSION['git_msg'] = substr(nl2br(shell_exec('/usr/bin/git clone "'.escapeshellcmd($_POST['url']).'" 2>&1')), 0, -1);
		
		redirect302();
		
	/*	echo '<span class="git_msg">';
		echo substr(nl2br(shell_exec('/usr/bin/git clone "'.escapeshellcmd($_POST['url']).'" 2>&1')), 0, -1);
		echo '</span>';
		
		$this->main($vars);*/
	}
	
	public function addremotes($args)
	{
		//git clone ssh://bios@localhost/home/bios/www/littlefoot/lf/skins/fresh
		
		//$type = array('apps', 'skins');
		
		//echo '/usr/bin/git clone "'.escapeshellcmd($_POST['url']).'" 2>&1';
		
		//chdir(ROOT.$type[intval($_POST['type'])]);
		
		$_SESSION['git_msg'] = substr(nl2br(shell_exec('/usr/bin/git remote add "'.escapeshellcmd($_POST['title']).'" "'.escapeshellcmd($_POST['url']).'" 2>&1')), 0, -1);
		redirect302();
		
	/*	echo '<span class="git_msg">';
		echo substr(nl2br(shell_exec('/usr/bin/git clone "'.escapeshellcmd($_POST['url']).'" 2>&1')), 0, -1);
		echo '</span>';
		
		$this->main($vars);*/
	}
	
	public function add($args)
	{
		chdir($this->path);
		shell_exec('/usr/bin/git add '.escapeshellcmd($_GET['file']).' 2>&1');
		$_SESSION['git_msg'] = $_GET['file'].' added to git';
		redirect302();
	}
	
	public function tag($vars)
	{
		$rev = shell_exec('git rev-list HEAD | wc -l');
		$rev = trim($rev);
		$version = 'v1.'.date('y.m').'-r'.$rev.'-'.$_POST['tag'];
		
		ob_start();
		$out = shell_exec('/usr/bin/git tag -a "'.$version.'" -m "'.$version.'" 2>&1');
		if(!$out)
			echo 'Tagged: '.$version;
		else
			echo $out;
		echo '<br />';
		echo 'Tags: <br />'.nl2br(trim(shell_exec('/usr/bin/git tag 2>&1')));
		
		
		$_SESSION['git_msg'] = ob_get_clean();
		
		redirect302();
	}
	
	public function tags($vars)
	{
		$_SESSION['git_msg'] = 'Tags: <br />'.nl2br(trim(shell_exec('/usr/bin/git tag 2>&1')));
		redirect302();
	}
	
	public function remotes($vars)
	{
		if(isset($vars[1]) && $vars[1] == 'master') return 'nope';
		
		/*$ini = preg_replace('/\s"([^"]+)"/', '$1', file_get_contents($this->path.'/.git/config'));
		
		$ini_array = parse_ini_string($ini);
		
		echo nl2br(print_r($ini_array,true));*/
		
		echo '<form action="%appurl%rmremote" method="post">
			<a href="%appurl%">Back</a>
			<h4>Remote Management</h4>';
		
		echo '<select name="remote" id="">';
		$lines = file($this->path.'/.git/config');
		foreach($lines as $line)
			if(preg_match('/^\[remote "([^"]+)"|url =/', $line, $match))
			{
				if($match[0] != 'url =') echo '<option value="'.$match[1].'">['.$match[1].'] ';
				if($match[0] == 'url =') echo $line.'</option>';
				
				//'</option>';
				//echo '<option>'.$line.'</option>';
			}
		echo '</select>
			<input type="submit" value="Delete remote" /> 
			</h3>
		</form>';
		
		echo '<h3>Add remote</h3>';
		echo '<form action="%appurl%addremotes" id="git_add_repo_form" method="post">
			
				Remote title: <input type="text" name="title" placeholder="origin" />
				Remote URL: <input size="40" type="text" name="url" placeholder="ssh://user@localhost/..." />
				
				<input type="submit" value="Add remote" />
		</form>';
		
		
		/*echo '<span class="git_msg">';
		$config = "";
		$lines = file($this->path.'/.git/config');
		foreach($lines as $line)
			if(preg_match('/^\[remote|url =/', $line))
				echo $line.'<br />';
		echo '</span>';
		
		$this->main($vars);*/
	}
	
	public function rmremote($args)
	{
		print_r($_POST);
		
		$_SESSION['git_msg'] = substr(nl2br(shell_exec('/usr/bin/git remote rm '.escapeshellcmd($_POST['remote']).' 2>&1')), 0, -1);
		
		//redirect302();
	}
	
	public function rm($vars)
	{
		if($vars[1] == 'master') return 'nope';
		$_SESSION['git_msg'] = substr(nl2br(shell_exec('/usr/bin/git branch -D '.$vars[1].' 2>&1')), 0, -1);
		redirect302();
	}
	
	public function rmfile($vars)
	{
		if($vars[1] == 'master') return 'nope';
		
		
		echo '<span class="git_msg">';
		echo substr(nl2br(shell_exec('/usr/bin/git rm '.escapeshellcmd($_GET['file']).' 2>&1')), 0, -1);
		echo '</span>';
		
		$this->main($vars);
	}
	/*
	public function push($vars)
	{
		echo '<span class="git_msg">';
		echo substr(nl2br(shell_exec('/usr/bin/git push -u origin master 2>&1')), 0, -1);
		echo '</span>';
		
		$this->main($vars);
	}*/
	
	public function merge($vars)
	{
		$_SESSION['git_msg'] = substr(nl2br(shell_exec('/usr/bin/git checkout master 2>&1 && /usr/bin/git merge '.escapeshellarg($vars[1]).' 2>&1')), 0, -1);
		redirect302();
	}
	
	public function rebase($vars)
	{
		$rebase = "master ";
		if(isset($_SESSION['rebase'])) $rebase = '--continue ';
		
		$out = substr(nl2br(shell_exec('/usr/bin/git rebase '.$rebase.'2>&1')), 0, -1);
		
		if(preg_match('/git rebase \(?--continue/', $out))
			$_SESSION['rebase'] = '--continue ';
		else
			unset($_SESSION['rebase']);
		
		// hopefully this never loses tones of data
		if(preg_match('/No changes/', $out))
		{
			unset($_SESSION['rebase']);
			$out = substr(nl2br(shell_exec('/usr/bin/git rebase --skip 2>&1')), 0, -1);
		}
		 
		
		echo '<span class="git_msg">';
		echo $out;
		echo '</span>';
			
			
		$this->main($vars);
	}
	
	public function create($vars)
	{
		$_SESSION['git_msg'] = substr(nl2br(shell_exec('/usr/bin/git checkout -b "'.$_POST['newbranch'].'" 2>&1')), 0, -1);
		redirect302();
	}
	
	public function cohead($vars)
	{
		echo '<span class="git_msg">';
		echo substr(nl2br(shell_exec('/usr/bin/git checkout HEAD -- "'.$_GET['file'].'" 2>&1')), 0, -1);
		echo '</span>';
		
		$this->main($vars);
	}
	 
	public function checkout($vars)
	{
		$_SESSION['git_msg'] = substr(nl2br(shell_exec('/usr/bin/git checkout '.escapeshellarg($vars[1]).' 2>&1')), 0, -1);
		redirect302(); 
	}
	
	public function commit($vars)
	{
		$out = shell_exec('/usr/bin/git commit -am "'.escapeshellarg($_POST['commit_text']).'" 2>&1');
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
			
			$out = shell_exec('/usr/bin/git commit -am "'.escapeshellarg($_POST['commit_text']).'" 2>&1');
			$out = substr($out, 0, -1);
		}
		
		$_SESSION['git_msg'] = $out;
		
		redirect302();
		/*
		echo '<span class="git_msg">';
		echo nl2br($out);
		echo '</span>';
		
		$this->main($vars);*/
	}
	
	public function pullrequest($vars)
	{
		$out = shell_exec('/usr/bin/git diff --name-only master '.escapeshellarg($vars[1]).' 2>&1'); 
		$out = substr($out, 0, -1);
		
		$out2 = shell_exec('/usr/bin/git cherry -v master 2>&1'); 
		$out2 = substr($out2, 0, -1);
		
		$email = 'dev@'.$_SERVER['HTTP_HOST'];
		$msg = 'Pull request submitted by '.$email.'
		
'.$_SESSION['git_path'].'

Branch: '.$vars[1].'
	
Modified files (master -> '.$vars[1].'):
'.$out.'

Commits:
'.$out2;
		
		/*echo '<span class="git_msg">
			Pull Request submitted<br /><br />
			Modified files (master -> '.$vars[1].'):<br />';
		echo nl2br(htmlentities($out));
		echo '</span>';*/
		
		$ticket = $vars[1];
		if(preg_match('/\d+/', $vars[1], $match))
			$ticket = $match[0];
		
		$ticket = intval($_POST['ticketid']);

		$qamail = 'qa@eflipdomains.com';		
	
		// parse at ticket system
		mail($qamail, 'Ticket #'.intval($ticket).": Pull Request '".$vars[1]."'", $msg, 'From: '.$email); 

		$this->main($vars);
		
		$_SESSION['git_msg'] = $msg;
		
		redirect302();
	}
	
	public function pushpull($vars)
	{
		if(!preg_match('/^(push|pull)$/', $_POST['direction'], $match)) return 'bad request';
		
		ob_start();
		if($match[1] == 'push')
		{
			$tags = ' --tags';
			echo substr(nl2br(shell_exec('/usr/bin/git push '.escapeshellarg($_POST['remote']).' '.escapeshellarg($_POST['branch']).''.$tags.' 2>&1')), 0, -1);
		}
		else if($match[1] == 'pull')
		{
			echo nl2br(shell_exec('/usr/bin/git stash 2>&1'));
			
			echo '/usr/bin/git '.$match[1].' '.escapeshellarg($_POST['remote']).' '.escapeshellarg($_POST['branch']).'<br />';
			if($_POST['branch'] != 'master')
				echo nl2br(shell_exec('/usr/bin/git checkout -b '.escapeshellarg($_POST['branch']).' 2>&1'));
			else
				echo nl2br(shell_exec('/usr/bin/git checkout '.escapeshellarg($_POST['branch']).' 2>&1'));
			$tags = '';
			
			if($match[1] == 'push') $tags = ' --tags'; // move this to a checkbox
			
			echo substr(nl2br(shell_exec('/usr/bin/git '.$match[1].' '.escapeshellarg($_POST['remote']).' '.escapeshellarg($_POST['branch']).''.$tags.' 2>&1')), 0, -1);
		
			echo nl2br(shell_exec('/usr/bin/git checkout - 2>&1'));
					
			echo nl2br(shell_exec('/usr/bin/git stash pop 2>&1'));
		}
		$_SESSION['git_msg'] = ob_get_clean();
		
		redirect302();
		
		//$this->main($vars);
	}
	
	public function quickstatus()
	{
		if(preg_match('/(apps|skins|plugins)\/([^\/]+)/', $_SESSION['git_path'], $match))
			$repo = $match[1].'/'.$match[2];
		else 
			$repo = 'Littlefoot';
		
		$status = shell_exec('/usr/bin/git status -b --porcelain');
		preg_match('/##\s(.*)/', $status, $branch);
		
		$modified = preg_match('/(^|\n) M /',$status,$match) ? ' *uncommited changes*' : '';
		
		return $repo.' :: '.$branch[1].$modified;
	}
}
