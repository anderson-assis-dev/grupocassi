<<!DOCTYPE html>
<html>
<body>

<form action="" method="post">
<select name="cars[]" multiple>
  <option value="volvo">Volvo</option>
  <option value="saab">Saab</option>
  <option value="opel">Opel</option>
  <option value="audi">Audi</option>
</select>
<input type="submit">
</form>

<p>Hold down the Ctrl (windows) / Command (Mac) button to select multiple options.</p>

</body>
</html>
<?php
  var_dump($_POST['cars']);
  $dados = $_POST['cars'];
  echo(count($dados));
  echo($dados[0]);
?>
