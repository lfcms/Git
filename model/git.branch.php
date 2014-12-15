<?php
/*
$treegraph = shell_exec('/usr/bin/git log --all --graph --pretty=tformat:"%d" | grep -B1000 "(master)"');
$treegraph = explode("\n", $treegraph, -1);

$currentbranch = NULL;
//$branches = array();
foreach($treegraph as $treepart)
{
	
	if(preg_match('/^([^\*]*)\*\s+\((?:HEAD, )?([^)]+)\)/', $treepart, $match))
	{
		// configure pointing and resolve children
		$branch = $match[2];
		$depth = strlen($match[1]);
		
		
		// Resolve children
		if(isset($children[$depth]))
		{
			if(strpos($branch, ', '))
			{
				$mybranches = explode(', ', $branch);
				
				if($pos = array_search('master', $mybranches))
				{
					$branch = 'master';
					unset($mybranches[$pos]);
					
					$children[$depth] = array_merge(
						$children[$depth],
						$mybranches
					);
				}
				else
				{
					$branch = $mybranches[0];
					$children[$depth] = array_merge(
						$children[$depth],
						array_slice($mybranches, 1)
					);
				}
			}
			
			
			$resolve[$branch] = $children[$depth];
			unset($children[$depth]);
		}
		
		// Configure pointing based on depth rail
		if(strpos($branch, ', '))
			$children[$depth] = explode(', ', $branch);
		else
			$children[$depth][] = $branch;
		
	} 
	else if(preg_match('/^.*?(?:\|\/).*?/', $treepart, $match))
	{
		$line = $match[0];
		
		for($i = 0; $i < strlen($line); $i++)
		{
			$char = $line[$i];
			
			if($char == '/') // converge
			{
				if(isset($children[$i-1]))
				{
					$children[$i-1] = array_merge($children[$i-1], $children[$i+1]);
				} else {
					$children[$i-1]	= $children[$i+1];
				}
				unset($children[$i+1]);
			}
			
			if($char == '\\') // diverge
			{
				if(isset($children[$i+1]))
				{
					$children[$i+1] = array_merge($children[$i-1], $children[$i+1]);
				} else
				{
					$children[$i+1] = $children[$i-1];
				}
				
				unset($children[$i-1]);
			}
		}
	}
		
}

if(isset($children))
	foreach($children[0] as $child)
	{
		if($child == 'master') continue;
		
		$resolve['master'][] = $child;
	}
*/
?>