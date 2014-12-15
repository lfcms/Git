<?php 
echo '<form action="#" method="post">
	<a href="%appurl%">Back</a>
	<h4>Repo Management</h4>
	<select name="newgitpath" />';
	if(is_dir(ROOT.'../.git')) 
		echo '
			<optgroup label="System">
				<option value="'.ROOT.'..">LittlefootCMS</option>
			</optgroup>';

echo '<optgroup label="Apps">';
foreach(scandir(ROOT.'apps') as $app)
{
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
	if(is_dir(ROOT.'skins/'.$skin.'/.git'))
	{
		if($_SESSION['git_path'] == ROOT.'skins/'.$skin)
			echo '<option value="'.ROOT.'skins/'.$skin.'" selected="selected">'.$skin.'</option>';
		else
			echo '<option value="'.ROOT.'skins/'.$skin.'">'.$skin.'</option>';
	}
}
echo '</optgroup>';

echo '</select> 
	<input type="submit" disabled="disabled" value="Delete (not implemented)" /> 
	</h3>
</form>';

echo '<h3>Add Repo</h3>';
echo '<form action="%appurl%gitclone" id="git_add_repo_form" method="post">
	Type: <select name="type" id="">
			<option value="0">App</option>
			<option value="1">Skin</option>
		</select>
		
		Clone URL: <input size="40" type="text" name="url" placeholder="ssh://user@localhost/..." />
		
		Rename: <input type="text" name="rename" placeholder="blog" />
		
		<input type="submit" value="Clone" />
</form>'; ?>